<?php

namespace App\Livewire\Admin;

use App\Models\Faq;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('FAQs')]
class FaqList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $category = '';
    public string $question = '';
    public string $answer = '';
    public int $order = 0;
    public bool $is_active = true;

    protected $queryString = ['search', 'categoryFilter', 'statusFilter'];

    protected function rules(): array
    {
        return [
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['category', 'question', 'answer', 'order', 'is_active', 'editingId']);
        $this->order = Faq::max('order') + 1 ?? 0;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $this->editingId = $faq->id;
        $this->category = $faq->category;
        $this->question = $faq->question;
        $this->answer = $faq->answer;
        $this->order = $faq->order;
        $this->is_active = $faq->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'category' => $this->category,
            'question' => $this->question,
            'answer' => $this->answer,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $faq = Faq::findOrFail($this->editingId);
            $faq->update($data);
            session()->flash('message', 'FAQ updated successfully.');
        } else {
            Faq::create($data);
            session()->flash('message', 'FAQ created successfully.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();
        session()->flash('message', 'FAQ deleted successfully.');
    }

    public function moveUp(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $previous = Faq::where('order', '<', $faq->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previous) {
            $temp = $faq->order;
            $faq->order = $previous->order;
            $previous->order = $temp;
            $faq->save();
            $previous->save();
            session()->flash('message', 'FAQ order updated.');
        }
    }

    public function moveDown(int $id): void
    {
        $faq = Faq::findOrFail($id);
        $next = Faq::where('order', '>', $faq->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($next) {
            $temp = $faq->order;
            $faq->order = $next->order;
            $next->order = $temp;
            $faq->save();
            $next->save();
            session()->flash('message', 'FAQ order updated.');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['category', 'question', 'answer', 'order', 'is_active', 'editingId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $faqs = Faq::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->category($this->categoryFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = Faq::distinct()->pluck('category')->sort()->values();

        return view('livewire.admin.faq-list', [
            'faqs' => $faqs,
            'categories' => $categories,
        ]);
    }
}

