<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Tickets')]
class TicketList extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $categoryFilter = '';
    public string $priorityFilter = '';
    public string $statusFilter = '';
    public string $assignedFilter = '';
    public bool $showModal = false;
    public bool $showResponseModal = false;
    public ?int $viewingId = null;

    // Form fields
    public string $response_message = '';
    public ?int $assigned_to = null;
    public string $status = 'open';
    public string $priority = 'medium';
    public $attachments = [];

    protected $queryString = ['search', 'categoryFilter', 'priorityFilter', 'statusFilter', 'assignedFilter'];

    protected function rules(): array
    {
        return [
            'response_message' => 'required_without:assigned_to|nullable|string',
            'assigned_to' => 'nullable|exists:admins,id',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAssignedFilter(): void
    {
        $this->resetPage();
    }

    public function viewTicket(int $id): void
    {
        $ticket = Ticket::with(['user', 'assignedAdmin', 'responses.user', 'responses.admin', 'responses.attachments', 'attachments'])
            ->findOrFail($id);
        $this->viewingId = $ticket->id;
        $this->assigned_to = $ticket->assigned_to;
        $this->status = $ticket->status;
        $this->priority = $ticket->priority;
        $this->showModal = true;
    }

    public function openResponseModal(): void
    {
        $this->reset(['response_message', 'attachments']);
        $this->showResponseModal = true;
    }

    public function addResponse(): void
    {
        $this->validate([
            'response_message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $ticket = Ticket::findOrFail($this->viewingId);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'admin_id' => Auth::guard('admin')->id(),
            'message' => $this->response_message,
        ]);

        // Handle attachments
        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('tickets/' . $ticket->id, $fileName, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'response_id' => $response->id,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Update ticket status if it was resolved
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open', 'resolved_at' => null]);
        }

        session()->flash('message', 'Response added successfully.');
        $this->closeResponseModal();
        $this->viewTicket($ticket->id); // Refresh view
    }

    public function updateTicket(): void
    {
        $this->validate([
            'assigned_to' => 'nullable|exists:admins,id',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::findOrFail($this->viewingId);
        
        $updateData = [
            'assigned_to' => $this->assigned_to,
            'status' => $this->status,
            'priority' => $this->priority,
        ];

        if ($this->status === 'resolved' && $ticket->status !== 'resolved') {
            $updateData['resolved_at'] = now();
        } elseif ($this->status !== 'resolved') {
            $updateData['resolved_at'] = null;
        }

        $ticket->update($updateData);

        session()->flash('message', 'Ticket updated successfully.');
        $this->viewTicket($ticket->id); // Refresh view
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = TicketAttachment::findOrFail($attachmentId);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $ticketId = $attachment->ticket_id;
        $attachment->delete();

        session()->flash('message', 'Attachment deleted successfully.');
        $this->viewTicket($ticketId); // Refresh view
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['viewingId', 'assigned_to', 'status', 'priority', 'response_message', 'attachments']);
        $this->resetErrorBag();
    }

    public function closeResponseModal(): void
    {
        $this->showResponseModal = false;
        $this->reset(['response_message', 'attachments']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $tickets = Ticket::query()
            ->with(['user', 'assignedAdmin'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->category($this->categoryFilter);
            })
            ->when($this->priorityFilter, function ($query) {
                $query->priority($this->priorityFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->status($this->statusFilter);
            })
            ->when($this->assignedFilter, function ($query) {
                $query->assignedTo($this->assignedFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $viewingTicket = $this->viewingId 
            ? Ticket::with(['user', 'assignedAdmin', 'responses.user', 'responses.admin', 'responses.attachments', 'attachments'])
                ->find($this->viewingId)
            : null;

        $admins = Admin::all();

        return view('livewire.admin.ticket-list', [
            'tickets' => $tickets,
            'viewingTicket' => $viewingTicket,
            'admins' => $admins,
        ]);
    }
}

