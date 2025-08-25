<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Course;
use App\Models\Coupon;
use App\Models\CourseStudent;

class PaymentController extends Controller
{
    public function intent(Request $request){
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $student = auth()->user()->student;
        $courseId = $request->input('course_id');
        $couponCode = $request->input('coupon_code');

        $course = Course::find($courseId);

        $price = $course->price;
        if($price==0) {
            CourseStudent::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'course_id' => $courseId,
                ],
                ['status' => 'enrolled']
            );
            return response()->json(['message' => 'Payment successful.']);
        }

        $price *= (100 - $course->discount) / 100;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();
            if (!$coupon) {
                return response()->json(['error' => 'Invalid or expired coupon'], 422);
            }

            if ($course->instructor_id !== $coupon->instructor_id) {
                return response()->json(['error' => 'Invalid or expired coupon'], 422);
            }
            $price *= (100 - $coupon->value) / 100;
        }
        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round($price * 100),
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ]
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request, FcmService $fcm)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid payment ID'], 400);
        }

        if ($paymentIntent->status !== 'succeeded') {
            return response()->json(['error' => 'Payment not successful'], 402);
        }

        $metadataCourseId = $paymentIntent->metadata->course_id;
        $metadataStudentId = $paymentIntent->metadata->student_id;
        $student = auth()->user()->student;

        if ((int) $metadataStudentId !== $student->id) {
            return response()->json(['error' => 'User mismatch'], 405);
        }

        CourseStudent::updateOrCreate(
            [
                'student_id' => $student->id,
                'course_id' => $metadataCourseId
            ],
            ['status' => 'enrolled']
        );

        $course = Course::findOrFail($metadataCourseId);
        $instructor = $course->instructor;

        $amount=null;
        if ($instructor) {
            $amount = $paymentIntent->amount_received / 100; // Stripe returns cents
            $instructor->increment('current_balance', $amount);
            $instructor->increment('total_balance', $amount);

            // ✅ Notify the instructor about the enrollment/payment
            $fcm->sendToUser(
                user: $instructor->user,
                title: 'New Student Enrolled 🎓',
                body: $student->user->name . ' has enrolled in your course "' . $course->title . '".',
                data: [
                    'type' => 'course_enrollment',
                    'course_id' => (string) $course->id,
                    'student_id' => (string) $student->id,
                    'payment_id' => (string) $paymentIntent->id
                ]
            );
        }

        // ✅ Notify the student about successful enrollment
        $fcm->sendToUser(
            user: $student->user,
            title: 'Enrollment Successful ✅',
            body: 'You have successfully enrolled in "' . $course->title . '".',
            data: [
                'type' => 'course_enrolled',
                'course_id' => (string) $course->id,
                'payment_id' => (string) $paymentIntent->id
            ]
        );

        Payment::create([
            'payment_id' => $paymentIntent->id,
            'student_id' => $student->id,
            'course_id' => $metadataCourseId,
            'amount'    => $amount,
            'status'    => $paymentIntent->status,
        ]);

        return response()->json(['message' => 'Payment successful.']);
    }

//    public function confirm(Request $request)
//    {
//        $request->validate([
//            'payment_id' => 'required|string',
//        ]);
//
//        Stripe::setApiKey(config('services.stripe.secret'));
//
//        try {
//            $paymentIntent = PaymentIntent::retrieve($request->payment_id);
//        } catch (\Exception $e) {
//            return response()->json(['error' => 'Invalid payment ID'], 400);
//        }
//
//        if ($paymentIntent->status !== 'succeeded') {
//            return response()->json(['error' => 'Payment not successful'], 402);
//        }
//
//        $metadataCourseId = $paymentIntent->metadata->course_id;
//        $metadataStudentId = $paymentIntent->metadata->student_id;
//        $student = auth()->user()->student;
//
//        if ((int) $metadataStudentId !== $student->id) {
//            return response()->json(['error' => 'User mismatch'], 405);
//        }
//
//        CourseStudent::updateOrCreate(
//            [
//                'student_id' => $student->id,
//                'course_id' => $metadataCourseId
//            ],
//            ['status' => 'enrolled']
//        );
//
//        $course = Course::findOrFail($metadataCourseId);
//        $instructor = $course->instructor;
//
//        if ($instructor) {
//            $amount = $paymentIntent->amount_received / 100; // Stripe returns cents
//            $instructor->increment('current_balance', $amount);
//            $instructor->increment('total_balance', $amount);
//        }
//
//        Payment::create([
//            'payment_id' => $paymentIntent->id,
//            'student_id' => $student->id,
//            'course_id' => $metadataCourseId,
//            'amount'    => $amount,
//            'status'    => $paymentIntent->status,
//        ]);
//
//        return response()->json(['message' => 'Payment successful.']);
//    }

}
