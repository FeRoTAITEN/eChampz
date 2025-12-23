<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - eChampz</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        dark: {
                            100: '#f1f5f9',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        .glow-green {
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.2);
        }
        .grid-pattern {
            background-image:
                linear-gradient(rgba(34, 197, 94, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 197, 94, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
    </style>
</head>
<body class="h-full font-sans antialiased gradient-bg grid-pattern">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-400 to-primary-600 rounded-2xl glow-green mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">eChampz Admin</h1>
                <p class="text-dark-400">Sign in to access the admin panel</p>
            </div>

            <!-- Login form -->
            <div class="bg-dark-900/80 backdrop-blur-sm border border-dark-700 rounded-2xl p-8">
                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark-300 mb-2">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                                   focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500
                                   transition-all duration-200"
                            placeholder="admin@echampz.com"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-dark-300 mb-2">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            class="w-full px-4 py-3 bg-dark-800 border border-dark-600 rounded-xl text-white placeholder-dark-400
                                   focus:outline-none focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500
                                   transition-all duration-200"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            class="w-4 h-4 bg-dark-800 border-dark-600 rounded text-primary-500 focus:ring-primary-500/50 focus:ring-offset-dark-900"
                        >
                        <label for="remember" class="ml-2 text-sm text-dark-300">Remember me</label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500
                               text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25
                               transition-all duration-200 transform hover:scale-[1.02]"
                    >
                        Sign In
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-dark-500 text-sm mt-6">
                &copy; {{ date('Y') }} eChampz. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>








