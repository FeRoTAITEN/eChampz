<?php

namespace App\Livewire\Admin;

use App\Models\Feedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Feedback')]
class FeedbackList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public ?int $viewingId = null;

    // Form fields for admin notes
    public string $admin_notes = '';
    public string $status = 'new';

    protected $queryString = ['search', 'typeFilter', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewFeedback(int $id): void
    {
        $feedback = Feedback::findOrFail($id);
        $this->viewingId = $feedback->id;
        $this->admin_notes = $feedback->admin_notes ?? '';
        $this->status = $feedback->status;
        $this->showModal = true;
    }

    public function updateFeedback(): void
    {
        $this->validate([
            'status' => 'required|in:new,reviewed,resolved',
            'admin_notes' => 'nullable|string',
        ]);

        $feedback = Feedback::findOrFail($this->viewingId);
        $feedback->update([
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
        ]);

        session()->flash('message', 'Feedback updated successfully.');
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();
        session()->flash('message', 'Feedback deleted successfully.');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['viewingId', 'admin_notes', 'status']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $feedback = Feedback::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->typeFilter, function ($query) {
                $query->type($this->typeFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->status($this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $viewingFeedback = $this->viewingId ? Feedback::with('user')->find($this->viewingId) : null;

        return view('livewire.admin.feedback-list', [
            'feedback' => $feedback,
            'viewingFeedback' => $viewingFeedback,
        ]);
    }
}

