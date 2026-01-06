<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends BaseController
{
    /**
     * Submit feedback (requires authentication).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['sometimes', 'string', 'in:general,bug,feature,other'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'message' => $request->message,
            'type' => $request->get('type', 'general'),
            'status' => 'new',
        ]);

        return $this->createdResponse($feedback, 'Feedback submitted successfully');
    }

    /**
     * Get user's own feedback (requires authentication).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');
        $type = $request->get('type');

        $query = Feedback::where('user_id', $user->id);

        if ($status) {
            $query->status($status);
        }

        if ($type) {
            $query->type($type);
        }

        $feedback = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($feedback, 'Feedback retrieved successfully');
    }
}

