<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use App\Models\Permission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Permissions')]
class PermissionList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $slug = '';
    public string $description = '';

    protected $queryString = ['search'];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'description' => 'nullable|string|max:500',
        ];

        if ($this->editingId) {
            $rules['slug'] = 'required|string|max:255|unique:permissions,slug,' . $this->editingId;
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['name', 'slug', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $this->editingId = $permission->id;
        $this->name = $permission->name;
        $this->slug = $permission->slug;
        $this->description = $permission->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];

        if ($this->editingId) {
            $permission = Permission::findOrFail($this->editingId);
            $permission->update($data);
            session()->flash('message', 'Permission updated successfully.');
        } else {
            Permission::create($data);
            session()->flash('message', 'Permission created successfully.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $permission = Permission::findOrFail($id);

        // Check if any admins have this permission
        if ($permission->admins()->count() > 0) {
            session()->flash('error', 'Cannot delete permission that is assigned to admins.');
            return;
        }

        $permission->delete();
        session()->flash('message', 'Permission deleted successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'slug', 'description', 'editingId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $permissions = Permission::query()
            ->withCount('admins')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.permission-list', [
            'permissions' => $permissions,
        ]);
    }
}

