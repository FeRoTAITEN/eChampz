<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Admins')]
class AdminList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showPermissionModal = false;
    public ?int $editingId = null;
    public ?int $permissionAdminId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public bool $is_super_admin = false;

    // Permission management
    public array $selectedPermissions = [];

    protected $queryString = ['search'];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'is_super_admin' => 'boolean',
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
            $rules['email'] = 'required|email|max:255|unique:admins,email,' . $this->editingId;
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['name', 'email', 'password', 'is_super_admin', 'editingId']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $admin = Admin::findOrFail($id);
        $this->editingId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->is_super_admin = $admin->is_super_admin;
        $this->password = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'is_super_admin' => $this->is_super_admin,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $admin = Admin::findOrFail($this->editingId);
            $admin->update($data);
            session()->flash('message', 'Admin updated successfully.');
        } else {
            Admin::create($data);
            session()->flash('message', 'Admin created successfully.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $admin = Admin::findOrFail($id);

        // Prevent deleting yourself
        if ($admin->id === Auth::guard('admin')->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        $admin->delete();
        session()->flash('message', 'Admin deleted successfully.');
    }

    public function managePermissions(int $id): void
    {
        $this->permissionAdminId = $id;
        $admin = Admin::findOrFail($id);
        $this->selectedPermissions = $admin->permissions->pluck('id')->toArray();
        $this->showPermissionModal = true;
    }

    public function savePermissions(): void
    {
        $admin = Admin::findOrFail($this->permissionAdminId);
        $admin->syncPermissions($this->selectedPermissions);
        session()->flash('message', 'Permissions updated successfully.');
        $this->closePermissionModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'is_super_admin', 'editingId']);
        $this->resetErrorBag();
    }

    public function closePermissionModal(): void
    {
        $this->showPermissionModal = false;
        $this->reset(['permissionAdminId', 'selectedPermissions']);
    }

    public function render()
    {
        $admins = Admin::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $permissions = Permission::all();

        return view('livewire.admin.admin-list', [
            'admins' => $admins,
            'permissions' => $permissions,
        ]);
    }
}

