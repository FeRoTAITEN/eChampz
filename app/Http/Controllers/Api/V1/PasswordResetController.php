<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends BaseController
{
    /**
     * Send password reset code
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // If the user does not exist, return a generic success to avoid account enumeration
        $userExists = User::where('email', $request->email)->exists();
        if (!$userExists) {
            return $this->successResponse([
                'message' => 'If an account exists for this email, a reset code has been sent.',
            ], 'Password reset code sent successfully');
        }

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store the hashed token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($code),
            'created_at' => Carbon::now(),
        ]);

        // In production, send email with the code
        // For now, we'll return it in development mode only
        $responseData = [
            'message' => 'Password reset code has been sent to your email.',
        ];

        // Only include code in development/testing
        if (app()->environment(['local', 'testing'])) {
            $responseData['code'] = $code;
        }

        return $this->successResponse($responseData, 'Password reset code sent successfully');
    }

    /**
     * Verify reset code
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return $this->errorResponse('Invalid or expired reset code.', 400);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Reset code has expired. Please request a new one.', 400);
        }

        // Verify the code
        if (!Hash::check($request->code, $record->token)) {
            return $this->errorResponse('Invalid reset code.', 400);
        }

        // Generate a temporary token for the reset
        $resetToken = Str::random(64);

        // Update the record with the new token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update([
                'token' => Hash::make($resetToken),
                'created_at' => Carbon::now(),
            ]);

        return $this->successResponse([
            'reset_token' => $resetToken,
        ], 'Code verified successfully');
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return $this->errorResponse('Invalid or expired reset token.', 400);
        }

        // Check if token is expired (15 minutes for reset token)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Reset token has expired. Please start over.', 400);
        }

        // Verify the reset token
        if (!Hash::check($request->reset_token, $record->token)) {
            return $this->errorResponse('Invalid reset token.', 400);
        }

        // Update the user's password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all existing tokens (logout from all devices)
        $user->tokens()->delete();

        return $this->successResponse(null, 'Password has been reset successfully. Please login with your new password.');
    }
}

