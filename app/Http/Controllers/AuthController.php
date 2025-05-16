<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController
{

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:' . implode(',', [User::ROLE_STUDENT, User::ROLE_INSTRUCTOR]),
        ]);

        $code = rand(100000, 999999);

        Cache::put('register_' . $request->email, [
            'data' => $request->only(['first_name', 'last_name', 'user_name', 'email', 'password', 'role']),
            'data' => $request->except('password_confirmation'),
            'code' => $code,
        ], now()->addMinutes(15));

        // $cached = Cache::get('register_' . $request->email);


        // $data = $cached['data'];
        // $data['password'] = bcrypt($data['password']);
        // $user = User::create($data);

        // Cache::forget('register_' . $request->email);

        // if ($user->isStudent()) {
        //     $user->student()->create([
        //         'full_name' => $user->first_name . ' ' . $user->last_name,
        //     ]);
        // } elseif ($user->isInstructor()) {
        //     $user->instructor()->create([
        //         'full_name' => $user->first_name . ' ' . $user->last_name,
        //         'views'     => 0,
        //     ]);
        // }
        // $user->load('student', 'instructor');

        // $token = $user->createToken('mobile')->plainTextToken;

        // return response()->json([
        //     'message' => 'Registration completed.',
        //     'user'    => $user,
        //     'token'   => $token,
        //     'profile' => $user->student ?? $user->instructor,
        // ]);

        Mail::to($request->email)->send(new VerificationCodeMail($code));

        return response()->json(['message' => 'Verification code sent to your email.']);

    }

    public function verifyRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $cached = Cache::get('register_' . $request->email);

        if (!$cached || $cached['code'] != $request->code) {
            return response()->json(['error' => 'Invalid or expired code.'], 422);
        }

        $data = $cached['data'];
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        Cache::forget('register_' . $request->email);

        if ($user->isStudent()) {
            $user->student()->create([
                'full_name' => $user->first_name . ' ' . $user->last_name,
            ]);
        } elseif ($user->isInstructor()) {
            $user->instructor()->create([
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'views'     => 0,
            ]);
        }
        $user->load('student', 'instructor');

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Registration completed.',
            'user'    => $user,
            'token'   => $token,
            'profile' => $user->student ?? $user->instructor,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token'   => $token,
            'user'    => $user,
            'profile' => $user->student ?? $user->instructor,
        ], 200);
    }

    // GOOGLE AUTH
    public function googleSignIn(Request $request)
    {
        $request->validate([
            'id_token' => 'required',
        ]);
        $idToken = $request->input('id_token');

        $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]); // client ID from Google Console
        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            return response()->json(['error' => 'Invalid ID token'], 401);
        }

        $email = $payload['email'];
        $user_name = $payload['name'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $user_name,
                'email' => $email,
                'password' => bcrypt($request->password),
            ]);
        }
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
        ] , 200);
    }


    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!password_verify($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = rand(100000, 999999);

        Cache::put('reset_' . $request->email, [
            'code' => $code,
        ], now()->addMinutes(15));

        Mail::to($request->email)->send(new \App\Mail\PasswordResetCodeMail($code));

        return response()->json(['message' => 'Reset code sent to your email.']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cached = Cache::get('reset_' . $request->email);

        if (!$cached || $cached['code'] != $request->code) {
            return response()->json(['message' => 'Invalid or expired reset code'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        Cache::forget('reset_' . $request->email);

        return response()->json(['message' => 'Password reset successfully']);
    }

}
