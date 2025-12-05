<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Users')]
class UserList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $verifiedFilter = '';
    public bool $showModal = false;
    public ?int $viewingId = null;

    protected $queryString = ['search', 'roleFilter', 'verifiedFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingVerifiedFilter(): void
    {
        $this->resetPage();
    }

    public function viewUser(int $id): void
    {
        $this->viewingId = $id;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewingId = null;
    }

    public function toggleVerification(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            session()->flash('message', 'User email verification removed.');
        } else {
            $user->update(['email_verified_at' => now()]);
            session()->flash('message', 'User email verified successfully.');
        }
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'User deleted successfully.');
        $this->closeModal();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->verifiedFilter !== '', function ($query) {
                if ($this->verifiedFilter === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($this->verifiedFilter === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $viewingUser = $this->viewingId ? User::find($this->viewingId) : null;

        return view('livewire.admin.user-list', [
            'users' => $users,
            'viewingUser' => $viewingUser,
            'roles' => UserRole::cases(),
        ]);
    }
}

