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
                    placeholder="Search users..."
                    class="w-full sm:w-64 pl-12 pr-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                >
            </div>

            <!-- Role Filter -->
            <select
                wire:model.live="roleFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </select>

            <!-- Verification Filter -->
            <select
                wire:model.live="verifiedFilter"
                class="px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
                <option value="">All Status</option>
                <option value="verified">Verified</option>
                <option value="unverified">Unverified</option>
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
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Email</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Role</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Status</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Registered</th>
                        <th class="text-right px-6 py-4 text-sm font-semibold text-dark-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($users as $user)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br {{ $user->role->value === 'gamer' ? 'from-cyan-600 to-cyan-700' : 'from-rose-600 to-rose-700' }} rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-white">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-white">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-dark-300">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if ($user->role->value === 'gamer')
                                    <span class="inline-flex items-center px-3 py-1 bg-cyan-500/10 text-cyan-400 text-sm font-medium rounded-full border border-cyan-500/20">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                        </svg>
                                        Gamer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-rose-500/10 text-rose-400 text-sm font-medium rounded-full border border-rose-500/20">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Recruiter
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->email_verified_at)
                                    <span class="inline-flex items-center px-3 py-1 bg-primary-500/10 text-primary-400 text-sm font-medium rounded-full border border-primary-500/20">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-500/10 text-amber-400 text-sm font-medium rounded-full border border-amber-500/20">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-dark-400 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="viewUser({{ $user->id }})"
                                        class="p-2 text-dark-400 hover:text-blue-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="View Details"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="toggleVerification({{ $user->id }})"
                                        class="p-2 text-dark-400 hover:text-primary-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="{{ $user->email_verified_at ? 'Remove Verification' : 'Verify Email' }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="delete({{ $user->id }})"
                                        wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p>No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- View User Modal -->
    @if ($showModal && $viewingUser)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-5 border-b border-dark-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">User Details</h3>
                        <button wire:click="closeModal" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <!-- User Avatar -->
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br {{ $viewingUser->role->value === 'gamer' ? 'from-cyan-600 to-cyan-700' : 'from-rose-600 to-rose-700' }} rounded-full flex items-center justify-center">
                                <span class="text-xl font-bold text-white">
                                    {{ strtoupper(substr($viewingUser->name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-xl font-semibold text-white">{{ $viewingUser->name }}</h4>
                                <p class="text-dark-400">{{ $viewingUser->role->label() }}</p>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-dark-700">
                                <span class="text-dark-400">Email</span>
                                <span class="text-white">{{ $viewingUser->email }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-dark-700">
                                <span class="text-dark-400">Email Status</span>
                                @if ($viewingUser->email_verified_at)
                                    <span class="inline-flex items-center text-primary-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Verified on {{ $viewingUser->email_verified_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-amber-400">Not verified</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-dark-700">
                                <span class="text-dark-400">Registered</span>
                                <span class="text-white">{{ $viewingUser->created_at->format('M d, Y \a\t H:i') }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-dark-400">Last Updated</span>
                                <span class="text-white">{{ $viewingUser->updated_at->format('M d, Y \a\t H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-dark-800/50 border-t border-dark-700 flex justify-between">
                        <button
                            wire:click="delete({{ $viewingUser->id }})"
                            wire:confirm="Are you sure you want to delete this user?"
                            class="px-5 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-xl border border-red-500/20 transition-colors"
                        >
                            Delete User
                        </button>
                        <button
                            wire:click="closeModal"
                            class="px-5 py-2.5 bg-dark-700 hover:bg-dark-600 text-dark-200 font-medium rounded-xl transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>




