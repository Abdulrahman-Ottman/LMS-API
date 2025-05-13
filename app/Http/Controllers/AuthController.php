<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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
        ]);

        $code = rand(100000, 999999);

        Cache::put('register_' . $request->email, [
            'data' => $request->except('password_confirmation'),
            'code' => $code,
        ], now()->addMinutes(15));


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

        return response()->json([
            'message' => 'Registration completed.',
            'user' => $user,
            'token' => $user->createToken('mobile')->plainTextToken,
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

        return response()->json(['token' => $token, 'user' => $user], 200);
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



}



