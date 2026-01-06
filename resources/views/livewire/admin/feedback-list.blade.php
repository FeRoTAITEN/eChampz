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
                    placeholder="Search feedback..."
                    class="w-full sm:w-64 pl-12 pr-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                >
            </div>

            <!-- Type Filter -->
            <select
                wire:model.live="typeFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Types</option>
                <option value="general">General</option>
                <option value="bug">Bug</option>
                <option value="feature">Feature</option>
                <option value="other">Other</option>
            </select>

            <!-- Status Filter -->
            <select
                wire:model.live="statusFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Status</option>
                <option value="new">New</option>
                <option value="reviewed">Reviewed</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">User</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Subject</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Type</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Status</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Date</th>
                        <th class="text-right px-6 py-4 text-sm font-semibold text-dark-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($feedback as $item)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                @if ($item->user)
                                    <span class="text-white">{{ $item->user->name }}</span>
                                @else
                                    <span class="text-dark-400">Anonymous</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ Str::limit($item->subject, 50) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 bg-blue-500/10 text-blue-400 text-sm font-medium rounded-full border border-blue-500/20">
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->status === 'new')
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-500/10 text-amber-400 text-sm font-medium rounded-full border border-amber-500/20">
                                        New
                                    </span>
                                @elseif ($item->status === 'reviewed')
                                    <span class="inline-flex items-center px-3 py-1 bg-blue-500/10 text-blue-400 text-sm font-medium rounded-full border border-blue-500/20">
                                        Reviewed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-primary-500/10 text-primary-400 text-sm font-medium rounded-full border border-primary-500/20">
                                        Resolved
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-dark-300 text-sm">{{ $item->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="viewFeedback({{ $item->id }})"
                                        class="p-2 text-dark-400 hover:text-blue-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="View Details"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="Are you sure you want to delete this feedback?"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                                <p>No feedback found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($feedback->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $feedback->links() }}
            </div>
        @endif
    </div>

    <!-- View/Edit Modal -->
    @if ($showModal && $viewingFeedback)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-5 border-b border-dark-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Feedback Details</h3>
                        <button wire:click="closeModal" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="updateFeedback">
                        <div class="px-6 py-6 space-y-6">
                            <!-- User Info -->
                            <div class="flex items-center gap-4 pb-4 border-b border-dark-700">
                                <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">
                                        @if ($viewingFeedback->user)
                                            {{ strtoupper(substr($viewingFeedback->user->name, 0, 2)) }}
                                        @else
                                            AN
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-white font-medium">
                                        {{ $viewingFeedback->user ? $viewingFeedback->user->name : 'Anonymous' }}
                                    </h4>
                                    <p class="text-dark-400 text-sm">{{ $viewingFeedback->created_at->format('M d, Y \a\t H:i') }}</p>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Subject</label>
                                <div class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white">
                                    {{ $viewingFeedback->subject }}
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Message</label>
                                <div class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white whitespace-pre-wrap">
                                    {{ $viewingFeedback->message }}
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Status</label>
                                <select
                                    wire:model="status"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                >
                                    <option value="new">New</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                                @error('status') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Admin Notes -->
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Admin Notes</label>
                                <textarea
                                    wire:model="admin_notes"
                                    rows="4"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    placeholder="Add internal notes..."
                                ></textarea>
                                @error('admin_notes') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-dark-800/50 border-t border-dark-700 flex justify-between">
                            <button
                                type="button"
                                wire:click="delete({{ $viewingFeedback->id }})"
                                wire:confirm="Are you sure you want to delete this feedback?"
                                class="px-5 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-xl border border-red-500/20 transition-colors"
                            >
                                Delete
                            </button>
                            <div class="flex gap-3">
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
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

