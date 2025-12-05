<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\EmailVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmailVerificationController extends BaseController
{
    /**
     * Send verification code to user's email
     */
    public function sendCode(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email is already verified.', 400);
        }

        // Delete any existing codes
        EmailVerificationCode::where('user_id', $user->id)->delete();

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store the code
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        // In production, send email with the code
        // For now, we'll return it in development mode only
        $responseData = [
            'message' => 'Verification code has been sent to your email.',
        ];

        // Only include code in development/testing
        if (app()->environment(['local', 'testing'])) {
            $responseData['code'] = $code;
        }

        return $this->successResponse($responseData, 'Verification code sent successfully');
    }

    /**
     * Verify email with code
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email is already verified.', 400);
        }

        // Find the verification code
        $verification = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->first();

        if (!$verification) {
            return $this->errorResponse('Invalid verification code.', 400);
        }

        // Check if expired
        if (Carbon::parse($verification->expires_at)->isPast()) {
            $verification->delete();
            return $this->errorResponse('Verification code has expired. Please request a new one.', 400);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Delete the verification code
        $verification->delete();

        return $this->successResponse([
            'user' => $user->fresh(),
        ], 'Email verified successfully');
    }

    /**
     * Check verification status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'verified' => $user->hasVerifiedEmail(),
            'email' => $user->email,
            'verified_at' => $user->email_verified_at,
        ]);
    }
}

