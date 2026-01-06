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
                    placeholder="Search tickets..."
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
                <option value="bug">Bug</option>
                <option value="feature">Feature</option>
                <option value="support">Support</option>
                <option value="account">Account</option>
                <option value="other">Other</option>
            </select>

            <!-- Priority Filter -->
            <select
                wire:model.live="priorityFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>

            <!-- Status Filter -->
            <select
                wire:model.live="statusFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Ticket #</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">User</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Subject</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Category</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Priority</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Status</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Assigned</th>
                        <th class="text-right px-6 py-4 text-sm font-semibold text-dark-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-white font-mono text-sm">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ $ticket->user->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white">{{ Str::limit($ticket->subject, 40) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 bg-blue-500/10 text-blue-400 text-sm font-medium rounded-full border border-blue-500/20">
                                    {{ ucfirst($ticket->category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($ticket->priority === 'urgent')
                                    <span class="inline-flex items-center px-3 py-1 bg-red-500/10 text-red-400 text-sm font-medium rounded-full border border-red-500/20">
                                        Urgent
                                    </span>
                                @elseif ($ticket->priority === 'high')
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-500/10 text-orange-400 text-sm font-medium rounded-full border border-orange-500/20">
                                        High
                                    </span>
                                @elseif ($ticket->priority === 'medium')
                                    <span class="inline-flex items-center px-3 py-1 bg-yellow-500/10 text-yellow-400 text-sm font-medium rounded-full border border-yellow-500/20">
                                        Medium
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-500/10 text-gray-400 text-sm font-medium rounded-full border border-gray-500/20">
                                        Low
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($ticket->status === 'open')
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-500/10 text-amber-400 text-sm font-medium rounded-full border border-amber-500/20">
                                        Open
                                    </span>
                                @elseif ($ticket->status === 'in_progress')
                                    <span class="inline-flex items-center px-3 py-1 bg-blue-500/10 text-blue-400 text-sm font-medium rounded-full border border-blue-500/20">
                                        In Progress
                                    </span>
                                @elseif ($ticket->status === 'resolved')
                                    <span class="inline-flex items-center px-3 py-1 bg-primary-500/10 text-primary-400 text-sm font-medium rounded-full border border-primary-500/20">
                                        Resolved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-dark-600/50 text-dark-400 text-sm font-medium rounded-full border border-dark-600">
                                        Closed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($ticket->assignedAdmin)
                                    <span class="text-white text-sm">{{ $ticket->assignedAdmin->name }}</span>
                                @else
                                    <span class="text-dark-400 text-sm">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="viewTicket({{ $ticket->id }})"
                                    class="p-2 text-dark-400 hover:text-blue-400 hover:bg-dark-700 rounded-lg transition-colors"
                                    title="View Details"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-dark-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p>No tickets found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($tickets->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- View Ticket Modal -->
    @if ($showModal && $viewingTicket)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="px-6 py-5 border-b border-dark-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $viewingTicket->ticket_number }}</h3>
                            <p class="text-sm text-dark-400">{{ $viewingTicket->subject }}</p>
                        </div>
                        <button wire:click="closeModal" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                        <!-- Ticket Info -->
                        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b border-dark-700">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Assigned To</label>
                                <select
                                    wire:model="assigned_to"
                                    wire:change="updateTicket"
                                    class="w-full px-4 py-2 bg-dark-800 border border-dark-600 rounded-xl text-white text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                >
                                    <option value="">Unassigned</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Status</label>
                                <select
                                    wire:model="status"
                                    wire:change="updateTicket"
                                    class="w-full px-4 py-2 bg-dark-800 border border-dark-600 rounded-xl text-white text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                >
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Priority</label>
                                <select
                                    wire:model="priority"
                                    wire:change="updateTicket"
                                    class="w-full px-4 py-2 bg-dark-800 border border-dark-600 rounded-xl text-white text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Category</label>
                                <div class="px-4 py-2 bg-dark-800 border border-dark-600 rounded-xl text-white text-sm">
                                    {{ ucfirst($viewingTicket->category) }}
                                </div>
                            </div>
                        </div>

                        <!-- Initial Ticket Description -->
                        <div class="mb-6 pb-6 border-b border-dark-700">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">
                                        {{ strtoupper(substr($viewingTicket->user->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-white font-medium">{{ $viewingTicket->user->name }}</h4>
                                    <p class="text-dark-400 text-xs">{{ $viewingTicket->created_at->format('M d, Y \a\t H:i') }}</p>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white whitespace-pre-wrap">
                                {{ $viewingTicket->description }}
                            </div>
                            @if ($viewingTicket->attachments->where('response_id', null)->count() > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($viewingTicket->attachments->where('response_id', null) as $attachment)
                                        <a href="{{ $attachment->url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-dark-800 border border-dark-600 rounded-lg text-white text-sm hover:bg-dark-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a2 2 0 00-2.828-2.828L8.586 9.172a4 4 0 105.656 5.656l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                            </svg>
                                            {{ $attachment->file_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Responses -->
                        @foreach ($viewingTicket->responses as $response)
                            <div class="mb-6 pb-6 border-b border-dark-700">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-gradient-to-br {{ $response->isFromAdmin() ? 'from-blue-500 to-blue-600' : 'from-primary-500 to-primary-600' }} rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-white">
                                            @if ($response->isFromAdmin())
                                                {{ strtoupper(substr($response->admin->name, 0, 2)) }}
                                            @else
                                                {{ strtoupper(substr($response->user->name, 0, 2)) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="text-white font-medium">
                                            {{ $response->isFromAdmin() ? $response->admin->name : $response->user->name }}
                                            @if ($response->isFromAdmin())
                                                <span class="ml-2 text-xs text-blue-400">(Admin)</span>
                                            @endif
                                        </h4>
                                        <p class="text-dark-400 text-xs">{{ $response->created_at->format('M d, Y \a\t H:i') }}</p>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white whitespace-pre-wrap">
                                    {{ $response->message }}
                                </div>
                                @if ($response->attachments->count() > 0)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($response->attachments as $attachment)
                                            <a href="{{ $attachment->url }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-dark-800 border border-dark-600 rounded-lg text-white text-sm hover:bg-dark-700 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a2 2 0 00-2.828-2.828L8.586 9.172a4 4 0 105.656 5.656l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                {{ $attachment->file_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Add Response Button -->
                        <button
                            wire:click="openResponseModal"
                            class="w-full px-4 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors"
                        >
                            Add Response
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Response Modal -->
    @if ($showResponseModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeResponseModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-5 border-b border-dark-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Add Response</h3>
                        <button wire:click="closeResponseModal" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="addResponse">
                        <div class="px-6 py-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Message</label>
                                <textarea
                                    wire:model="response_message"
                                    rows="6"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                    placeholder="Enter your response..."
                                ></textarea>
                                @error('response_message') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Attachments</label>
                                <input
                                    type="file"
                                    wire:model="attachments"
                                    multiple
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white text-sm
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500"
                                >
                                @error('attachments.*') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                                <p class="text-dark-400 text-xs mt-2">Max 10MB per file. Allowed: images, PDF, DOC, DOCX</p>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-dark-800/50 border-t border-dark-700 flex justify-end gap-3">
                            <button
                                type="button"
                                wire:click="closeResponseModal"
                                class="px-5 py-2.5 bg-dark-700 hover:bg-dark-600 text-dark-200 font-medium rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors"
                            >
                                Send Response
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

