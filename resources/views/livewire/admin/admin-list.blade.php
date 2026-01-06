<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search admins..."
                class="w-full sm:w-80 pl-12 pr-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                       focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
            >
        </div>

        <button
            wire:click="create"
            class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-primary-500 to-primary-600
                   hover:from-primary-400 hover:to-primary-500 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25
                   transition-all duration-200"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Admin
        </button>
    </div>

    <!-- Table -->
    <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Name</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Email</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Role</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Permissions</th>
                        <th class="text-left px-6 py-4 text-sm font-semibold text-dark-300">Created</th>
                        <th class="text-right px-6 py-4 text-sm font-semibold text-dark-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($admins as $admin)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-dark-600 to-dark-700 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-primary-400">
                                            {{ strtoupper(substr($admin->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-white">{{ $admin->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-dark-300">{{ $admin->email }}</td>
                            <td class="px-6 py-4">
                                @if ($admin->is_super_admin)
                                    <span class="inline-flex items-center px-3 py-1 bg-purple-500/10 text-purple-400 text-sm font-medium rounded-full border border-purple-500/20">
                                        Super Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-dark-700 text-dark-300 text-sm font-medium rounded-full">
                                        Admin
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($admin->is_super_admin)
                                    <span class="text-dark-400 text-sm">All permissions</span>
                                @else
                                    <span class="text-dark-300 text-sm">{{ $admin->permissions->count() }} assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-dark-400 text-sm">{{ $admin->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if (!$admin->is_super_admin)
                                        <button
                                            wire:click="managePermissions({{ $admin->id }})"
                                            class="p-2 text-dark-400 hover:text-primary-400 hover:bg-dark-700 rounded-lg transition-colors"
                                            title="Manage Permissions"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <button
                                        wire:click="edit({{ $admin->id }})"
                                        class="p-2 text-dark-400 hover:text-blue-400 hover:bg-dark-700 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @if ($admin->id !== Auth::guard('admin')->id())
                                        <button
                                            wire:click="delete({{ $admin->id }})"
                                            wire:confirm="Are you sure you want to delete this admin?"
                                            class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors"
                                            title="Delete"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-dark-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p>No admins found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($admins->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $admins->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="px-6 py-5 border-b border-dark-700">
                            <h3 class="text-lg font-semibold text-white">
                                {{ $editingId ? 'Edit Admin' : 'Create Admin' }}
                            </h3>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-dark-300 mb-2">Name</label>
                                <input
                                    wire:model="name"
                                    type="text"
                                    id="name"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                                    placeholder="John Doe"
                                >
                                @error('name') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-dark-300 mb-2">Email</label>
                                <input
                                    wire:model="email"
                                    type="email"
                                    id="email"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                                    placeholder="admin@echampz.com"
                                >
                                @error('email') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-dark-300 mb-2">
                                    Password {{ $editingId ? '(leave blank to keep current)' : '' }}
                                </label>
                                <input
                                    wire:model="password"
                                    type="password"
                                    id="password"
                                    class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                                           focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 transition-all duration-200"
                                    placeholder="••••••••"
                                >
                                @error('password') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Super Admin -->
                            <div class="flex items-center gap-3">
                                <input
                                    wire:model="is_super_admin"
                                    type="checkbox"
                                    id="is_super_admin"
                                    class="w-5 h-5 bg-dark-800 border-dark-600 rounded text-primary-500 focus:ring-primary-500/50 focus:ring-offset-dark-900"
                                >
                                <label for="is_super_admin" class="text-dark-300">
                                    Super Admin <span class="text-dark-500 text-sm">(has all permissions)</span>
                                </label>
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
                                class="px-5 py-2.5 bg-primary-500 hover:bg-primary-400 text-white font-medium rounded-xl transition-colors"
                            >
                                {{ $editingId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Permission Modal -->
    @if ($showPermissionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closePermissionModal" class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-dark-900 border border-dark-700 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="savePermissions">
                        <div class="px-6 py-5 border-b border-dark-700">
                            <h3 class="text-lg font-semibold text-white">Manage Permissions</h3>
                        </div>

                        <div class="px-6 py-6 space-y-3 max-h-96 overflow-y-auto">
                            @foreach ($permissions as $permission)
                                <label class="flex items-start gap-3 p-3 bg-dark-800/50 hover:bg-dark-800 border border-dark-700 rounded-xl cursor-pointer transition-colors">
                                    <input
                                        wire:model="selectedPermissions"
                                        type="checkbox"
                                        value="{{ $permission->id }}"
                                        class="mt-1 w-5 h-5 bg-dark-800 border-dark-600 rounded text-primary-500 focus:ring-primary-500/50 focus:ring-offset-dark-900"
                                    >
                                    <div>
                                        <p class="font-medium text-white">{{ $permission->name }}</p>
                                        @if ($permission->description)
                                            <p class="text-sm text-dark-400">{{ $permission->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="px-6 py-4 bg-dark-800/50 border-t border-dark-700 flex justify-end gap-3">
                            <button
                                type="button"
                                wire:click="closePermissionModal"
                                class="px-5 py-2.5 bg-dark-700 hover:bg-dark-600 text-dark-200 font-medium rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-primary-500 hover:bg-primary-400 text-white font-medium rounded-xl transition-colors"
                            >
                                Save Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>









