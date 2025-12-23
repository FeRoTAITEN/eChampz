<div>
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Total Users</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalUsers) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-4 text-sm">
                <span class="text-dark-400">Gamers: <span class="text-white font-medium">{{ number_format($totalGamers) }}</span></span>
                <span class="text-dark-400">Recruiters: <span class="text-white font-medium">{{ number_format($totalRecruiters) }}</span></span>
            </div>
        </div>

        <!-- Verified Users -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Verified Users</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($verifiedUsers) }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-dark-400">Verification rate</span>
                    <span class="text-primary-400 font-medium">
                        {{ $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100) : 0 }}%
                    </span>
                </div>
                <div class="w-full h-2 bg-dark-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-400 rounded-full transition-all duration-500"
                         style="width: {{ $totalUsers > 0 ? ($verifiedUsers / $totalUsers) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Unverified Users -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Pending Verification</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($unverifiedUsers) }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-sm text-dark-400">
                Users waiting to verify their email address
            </p>
        </div>

        <!-- Total Admins -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Admin Accounts</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalAdmins) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-sm text-dark-400">
                Platform administrators
            </p>
        </div>

        <!-- Gamers -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Gamers</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalGamers) }}</p>
                </div>
                <div class="w-12 h-12 bg-cyan-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-sm text-dark-400">
                Players showcasing achievements
            </p>
        </div>

        <!-- Recruiters -->
        <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm font-medium">Recruiters</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalRecruiters) }}</p>
                </div>
                <div class="w-12 h-12 bg-rose-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-4 text-sm text-dark-400">
                Talent scouts and team recruiters
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-dark-900/60 backdrop-blur-sm border border-dark-700 rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if(Auth::guard('admin')->user()->hasPermission('admins'))
                <a href="{{ route('admin.admins') }}" class="flex items-center gap-3 p-4 bg-dark-800/50 hover:bg-dark-800 border border-dark-700 hover:border-dark-600 rounded-xl transition-all duration-200 group">
                    <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center group-hover:bg-purple-500/20 transition-colors">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <span class="text-dark-200 group-hover:text-white font-medium transition-colors">Add Admin</span>
                </a>
            @endif

            @if(Auth::guard('admin')->user()->hasPermission('users'))
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 p-4 bg-dark-800/50 hover:bg-dark-800 border border-dark-700 hover:border-dark-600 rounded-xl transition-all duration-200 group">
                    <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition-colors">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-dark-200 group-hover:text-white font-medium transition-colors">Manage Users</span>
                </a>
            @endif

            @if(Auth::guard('admin')->user()->hasPermission('permissions'))
                <a href="{{ route('admin.permissions') }}" class="flex items-center gap-3 p-4 bg-dark-800/50 hover:bg-dark-800 border border-dark-700 hover:border-dark-600 rounded-xl transition-all duration-200 group">
                    <div class="w-10 h-10 bg-primary-500/10 rounded-lg flex items-center justify-center group-hover:bg-primary-500/20 transition-colors">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-dark-200 group-hover:text-white font-medium transition-colors">Permissions</span>
                </a>
            @endif

            <button wire:click="loadStats" class="flex items-center gap-3 p-4 bg-dark-800/50 hover:bg-dark-800 border border-dark-700 hover:border-dark-600 rounded-xl transition-all duration-200 group">
                <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="text-dark-200 group-hover:text-white font-medium transition-colors">Refresh Stats</span>
            </button>
        </div>
    </div>
</div>








