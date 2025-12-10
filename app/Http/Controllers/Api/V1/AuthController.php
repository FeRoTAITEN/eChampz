<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class AuthController extends BaseController
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Auto-send verification OTP
        $otpData = $this->generateAndSendOTP($user);

        $responseData = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'verification_required' => true,
        ];

        // Include OTP code in development mode
        if (app()->environment(['local', 'testing'])) {
            $responseData['code'] = $otpData['code'];
        }

        return $this->createdResponse($responseData, 'User registered. Please verify your email.');
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorizedResponse('Invalid credentials');
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $responseData = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];

        // If user is not verified, auto-send OTP
        if (!$user->hasVerifiedEmail()) {
            $otpData = $this->generateAndSendOTP($user);
            $responseData['verification_required'] = true;

            // Include OTP code in development mode
            if (app()->environment(['local', 'testing'])) {
                $responseData['code'] = $otpData['code'];
            }

            return $this->successResponse($responseData, 'Please verify your email to continue.');
        }

        return $this->successResponse($responseData, 'Login successful');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse($request->user());
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out from all devices successfully');
    }

    /**
     * Get available roles
     */
    public function roles(): JsonResponse
    {
        $roles = collect(UserRole::cases())->map(function ($role) {
            return [
                'value' => $role->value,
                'label' => $role->label(),
            ];
        });

        return $this->successResponse($roles);
    }

    /**
     * Generate and send OTP for email verification
     */
    private function generateAndSendOTP(User $user): array
    {
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

        // TODO: In production, send email with the code
        // Mail::to($user->email)->send(new VerificationCodeMail($code));

        return ['code' => $code];
    }
}
