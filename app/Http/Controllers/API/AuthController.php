<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use PresentsUser;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::with('relation.department', 'relation.designation', 'relation.shiftTiming')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'The email or password you entered is incorrect.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'This account has been deactivated. Please contact your administrator.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'token'  => $user->createToken($request->device_name ?: 'mobile')->plainTextToken,
            'user'   => $this->presentUser($user),
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('relation.department', 'relation.designation', 'relation.shiftTiming');

        return response()->json(['status' => true, 'user' => $this->presentUser($user)]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => true, 'message' => 'Logged out']);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->json(['status' => true, 'message' => 'Password changed successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Always answer the same way: a differing response told an attacker
        // which addresses are registered.
        $generic = [
            'status'  => true,
            'message' => 'If that email is registered, an OTP has been sent to it.',
        ];

        if (!$user || !$user->is_active) {
            return response()->json($generic);
        }

        // One OTP per minute.
        if ($user->otp_expires_at && now()->lt(Carbon::parse($user->otp_expires_at)->subMinutes(9))) {
            return response()->json([
                'status'  => false,
                'message' => 'Please wait a minute before requesting another OTP.',
            ], 429);
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp'            => $code,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        try {
            Mail::send('emails.otp', ['otp' => $code, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)->subject('Your OTP Code');
            });
        } catch (\Throwable $e) {
            // The OTP is saved either way; surfacing the mail error (or worse,
            // the code itself, as this endpoint used to) helps nobody.
            Log::error('OTP mail failed: ' . $e->getMessage());
        }

        return response()->json($generic);
    }

    public function verifyAndReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'otp'      => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->otp || !hash_equals($user->otp, $request->otp) || now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'otp'            => null,
            'otp_expires_at' => null,
        ])->save();

        // A password reset should not leave old sessions alive.
        $user->tokens()->delete();

        return response()->json(['status' => true, 'message' => 'Password reset successfully.']);
    }
}
