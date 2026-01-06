<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TicketController extends BaseController
{
    /**
     * Create a new ticket (requires authentication).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['sometimes', 'string', 'in:bug,feature,support,account,other'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high,urgent'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => $request->subject,
            'description' => $request->description,
            'category' => $request->get('category', 'support'),
            'priority' => $request->get('priority', 'medium'),
            'status' => 'open',
        ]);

        return $this->createdResponse($ticket, 'Ticket created successfully');
    }

    /**
     * List user's tickets (requires authentication).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 20);
        $status = $request->get('status');
        $category = $request->get('category');
        $priority = $request->get('priority');

        $query = Ticket::where('user_id', $user->id);

        if ($status) {
            $query->status($status);
        }

        if ($category) {
            $query->category($category);
        }

        if ($priority) {
            $query->priority($priority);
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse($tickets, 'Tickets retrieved successfully');
    }

    /**
     * Get ticket details with responses (requires authentication, owner only).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = Ticket::where('user_id', $user->id)
            ->with(['responses.user', 'responses.admin', 'responses.attachments', 'attachments'])
            ->find($id);

        if (!$ticket) {
            return $this->notFoundResponse('Ticket not found');
        }

        return $this->successResponse($ticket, 'Ticket retrieved successfully');
    }

    /**
     * Add response to ticket (requires authentication, owner only).
     */
    public function addResponse(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        $ticket = Ticket::where('user_id', $user->id)->find($id);

        if (!$ticket) {
            return $this->notFoundResponse('Ticket not found');
        }

        // Don't allow responses to closed tickets
        if ($ticket->status === 'closed') {
            return $this->errorResponse('Cannot add response to a closed ticket', 400);
        }

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        // Update ticket status to open if it was resolved
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open', 'resolved_at' => null]);
        }

        $response->load(['user', 'attachments']);

        return $this->createdResponse($response, 'Response added successfully');
    }

    /**
     * Upload attachment to ticket (requires authentication, owner only).
     */
    public function uploadAttachment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
            'response_id' => ['sometimes', 'integer', 'exists:ticket_responses,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        $ticket = Ticket::where('user_id', $user->id)->find($id);

        if (!$ticket) {
            return $this->notFoundResponse('Ticket not found');
        }

        // If response_id is provided, verify it belongs to the ticket
        $responseId = $request->get('response_id');
        if ($responseId) {
            $response = TicketResponse::where('ticket_id', $ticket->id)
                ->where('id', $responseId)
                ->where('user_id', $user->id)
                ->first();

            if (!$response) {
                return $this->notFoundResponse('Response not found or does not belong to this ticket');
            }
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('tickets/' . $ticket->id, $fileName, 'public');

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'response_id' => $responseId,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return $this->createdResponse($attachment, 'Attachment uploaded successfully');
    }
}

