<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends BaseController
{
    /**
     * XP mapping per step and completion bonus.
     */
    private array $xpMap = [
        'name' => 10,
        'birthday' => 10,
        'represent' => 15,
        'complete' => 20,
    ];

    /**
     * Get onboarding status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->checkCompletion($user);

        [$completed, $pendingSteps, $steps] = $this->buildProgress($user->fresh());

        $xpTotal = DB::table('xp_transactions')
            ->where('user_id', $user->id)
            ->sum('amount');

        if ($xpTotal !== (int) $user->xp_total) {
            $user->update(['xp_total' => $xpTotal]);
        }

        return $this->successResponse([
            'completed' => $completed,
            'pending_steps' => $pendingSteps,
            'steps' => $steps,
            'role' => $this->roleValue($user),
            'xp_total' => $xpTotal,
        ]);
    }

    /**
     * Save name step.
     */
    public function name(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        /** @var User $user */
        $user = $request->user();
        $user->update(['name' => $request->name]);

        $this->awardStep($user, 'name');
        $this->checkCompletion($user);

        return $this->successResponse([
            'user' => $user->fresh(),
        ], 'Name saved.');
    }

    /**
     * Save birthday step.
     */
    public function birthday(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . ((int) date('Y') + 1)],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $dateOfBirth = Carbon::createFromDate(
                (int) $request->year,
                (int) $request->month,
                (int) $request->day
            )->startOfDay();
        } catch (\Throwable $e) {
            return $this->validationErrorResponse(['date' => ['Invalid date supplied.']]);
        }

        /** @var User $user */
        $user = $request->user();
        $user->update(['date_of_birth' => $dateOfBirth]);

        $this->awardStep($user, 'birthday');
        $this->checkCompletion($user);

        return $this->successResponse([
            'user' => $user->fresh(),
        ], 'Birthday saved.');
    }

    /**
     * Save represent step (recruiter only).
     */
    public function represent(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$this->isRecruiter($user)) {
            return $this->errorResponse('Only recruiters need to complete this step.', 403);
        }

        $validator = validator($request->all(), [
            'type' => ['required', Rule::in(['organization', 'freelancer'])],
            'organization_name' => [
                Rule::requiredIf(fn () => $request->type === 'organization'),
                'string',
                'max:255',
                'nullable',
            ],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $data = [
            'represent_type' => $request->type,
            'organization_name' => $request->type === 'organization' ? $request->organization_name : null,
            'position' => $request->type === 'organization' ? $request->position : null,
        ];

        $user->update($data);

        $this->awardStep($user, 'represent');
        $this->checkCompletion($user);

        return $this->successResponse([
            'user' => $user->fresh(),
        ], 'Representation saved.');
    }

    /**
     * Determine progress and pending steps.
     */
    private function buildProgress(User $user): array
    {
        $steps = [
            'name' => !empty($user->name),
            'birthday' => !empty($user->date_of_birth),
        ];

        if ($this->isRecruiter($user)) {
            $steps['represent'] = !empty($user->represent_type);
        }

        $pending = collect($steps)
            ->filter(fn ($done) => !$done)
            ->keys()
            ->values()
            ->toArray();

        $completed = empty($pending);

        $stepsDetail = collect($steps)->map(function ($done, $key) {
            return [
                'step' => $key,
                'done' => $done,
                'awarded_xp' => $this->xpMap[$key] ?? 0,
            ];
        })->values()->toArray();

        return [$completed, $pending, $stepsDetail];
    }

    /**
     * Award XP for a step once.
     */
    private function awardStep(User $user, string $step): void
    {
        $amount = $this->xpMap[$step] ?? 0;
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $step, $amount) {
            $exists = DB::table('xp_transactions')
                ->where('user_id', $user->id)
                ->where('source', 'onboarding')
                ->where('source_id', $step)
                ->exists();

            if ($exists) {
                return;
            }

            DB::table('xp_transactions')->insert([
                'user_id' => $user->id,
                'source' => 'onboarding',
                'source_id' => $step,
                'amount' => $amount,
                'meta' => json_encode(['step' => $step]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->increment('xp_total', $amount);
        });
    }

    /**
     * Mark onboarding complete when all required steps are done and award completion XP.
     */
    private function checkCompletion(User $user): void
    {
        [$completed] = $this->buildProgress($user->fresh());

        if (!$completed) {
            return;
        }

        DB::transaction(function () use ($user) {
            $fresh = $user->fresh();

            if (!$fresh->onboarding_completed_at) {
                $fresh->update(['onboarding_completed_at' => now()]);
            }

            $this->awardCompletion($fresh);
        });
    }

    /**
     * Award completion bonus once.
     */
    private function awardCompletion(User $user): void
    {
        $amount = $this->xpMap['complete'] ?? 0;
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $amount) {
            $exists = DB::table('xp_transactions')
                ->where('user_id', $user->id)
                ->where('source', 'onboarding')
                ->where('source_id', 'complete')
                ->exists();

            if ($exists) {
                return;
            }

            DB::table('xp_transactions')->insert([
                'user_id' => $user->id,
                'source' => 'onboarding',
                'source_id' => 'complete',
                'amount' => $amount,
                'meta' => json_encode(['step' => 'complete']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->increment('xp_total', $amount);
        });
    }

    private function isRecruiter(User $user): bool
    {
        return $this->roleValue($user) === UserRole::RECRUITER->value;
    }

    private function roleValue(User $user): string
    {
        $role = $user->role;

        return $role instanceof UserRole ? $role->value : (string) $role;
    }
}
