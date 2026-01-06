<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search FAQs..."
                    class="w-full sm:w-64 pl-12 pr-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                >
            </div>

            <!-- Category Filter -->
            <select
                wire:model.live="categoryFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select
                wire:model.live="statusFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button
            wire:click="create"
            class="px-5 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors"
        >
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New FAQ
            </span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Order</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Category</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Question</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Status</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Views</th>
                        <th class="text-right px-6 py-4 text-sm font-semibold text-dark-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($faqs as $faq)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="moveUp({{ $faq->id }})"
                                        class="p-1 text-dark-400 hover:text-primary-400 transition-colors"
                                        title="Move Up"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    <span class="text-white font-medium">{{ $faq->order }}</span>
                                    <button
                                        wire:click="moveDown({{ $faq->id }})"
                                        class="p-1 text-dark-400 hover:text-primary-400 transition-colors"
                                        title="Move Down"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ $faq->category }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ Str::limit($faq->question, 60) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($faq->is_active)
                                    <span class="inline-flex items-center px-3 py-1 bg-primary-500/10 text-primary-400 text-sm font-medium rounded-full border border-primary-500/20">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-dark-600/50 text-dark-400 text-sm font-medium rounded-full border border-dark-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-dark-300">{{ $faq->views }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="edit({{ $faq->id }})"
                                        class="p-2 text-dark-400 hover:text-blue-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="delete({{ $faq->id }})"
                                        wire:confirm="Are you sure you want to delete this FAQ?"
                                        class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-dark-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>No FAQs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($faqs->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $faqs->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-5 border-b border-dark-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">{{ $editingId ? 'Edit FAQ' : 'Create FAQ' }}</h3>
                        <button wire:click="closeModal" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Category</label>
                                <input
                                    type="text"
                                    wire:model="category"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    placeholder="e.g., General, Account, Payments"
                                >
                                @error('category') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Question</label>
                                <textarea
                                    wire:model="question"
                                    rows="2"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    placeholder="Enter the question"
                                ></textarea>
                                @error('question') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Answer</label>
                                <textarea
                                    wire:model="answer"
                                    rows="4"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    placeholder="Enter the answer"
                                ></textarea>
                                @error('answer') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-dark-300 mb-2">Order</label>
                                    <input
                                        type="number"
                                        wire:model="order"
                                        min="0"
                                        class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                               focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    >
                                    @error('order') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-dark-300 mb-2">Status</label>
                                    <label class="flex items-center gap-3 mt-3">
                                        <input
                                            type="checkbox"
                                            wire:model="is_active"
                                            class="w-5 h-5 rounded border-dark-600 bg-dark-800 text-primary-500
                                                   focus:ring-2 focus:ring-primary-500/50"
                                        >
                                        <span class="text-white">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-dark-800/50 border-t border-dark-700 flex justify-end gap-3">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-5 py-2.5 bg-dark-700 hover:bg-dark-600 text-dark-200 font-medium rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors"
                            >
                                {{ $editingId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

