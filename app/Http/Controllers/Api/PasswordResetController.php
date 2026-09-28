<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            Log::info('Password reset requested for non-existent email', [
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'If an account exists with this email, a reset link has been sent.',
            ]);
        }

        // Delete any existing reset tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $resetUrl = $frontendUrl
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($request->email);

        try {
            Mail::raw(
                "Hello {$user->name},\n\n" .
                "You requested a password reset for your Klient account.\n\n" .
                "Click the link below to reset your password:\n\n" .
                "{$resetUrl}\n\n" .
                "This link will expire in 60 minutes.\n\n" .
                "If you did not request this, please ignore this email.\n\n" .
                "- Klient Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Reset Your Klient Password');
                }
            );

            Log::info('Password reset email sent', [
                'email' => $request->email,
                'reset_url' => $resetUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send email. Please try again later.',
            ], 500);
        }

        return response()->json([
            'message' => 'If an account exists with this email, a reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (! $record) {
            return response()->json([
                'errors' => ['email' => ['Invalid or expired reset link.']],
            ], 422);
        }

        if (! Hash::check($validated['token'], $record->token)) {
            return response()->json([
                'errors' => ['token' => ['Invalid or expired reset link.']],
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();

            return response()->json([
                'errors' => ['token' => ['Reset link has expired. Please request a new one.']],
            ], 422);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'errors' => ['email' => ['User not found.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        // Invalidate all existing Sanctum tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please log in with your new password.',
        ]);
    }
}