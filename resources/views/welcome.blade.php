<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Caballos para disfrutar</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="font-sans antialiased dark:bg-black dark:text-white/50">
    <div class="relative bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <img id="background" class="absolute top-0 left-0 w-full h-full object-cover z-index-0"
            src="https://cdn.pixabay.com/photo/2021/02/27/09/05/girl-6054032_1280.jpg" alt="Paseo a caballo">
        <div class="relative z-index-10">
            <header class="py-4 bg-blue-500">
                <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
                <h1 class="flex items-center text-3xl font-bold text-center text-white dark:text-white">
                        <img class="h-8 w-auto mr-2" src="{{ asset('icons/horse.png') }}" alt="Logo"> {{ __('Caballos para disfrutar') }}
                    </h1>
                    @if (Route::has('login'))
                        <nav class="flex ">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="rounded-md px-3 py-2 text-white ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                    {{ __('Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="rounded-md px-3 py-2 text-white hover:bg-white hover:text-black focus:outline-none focus:underline focus:text-black dark:text-white dark:hover:text-white/80 dark:focus:text-white/80">
                                    {{ __('Login') }}
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="rounded-md px-3 py-2 text-white hover:bg-white hover:text-black focus:outline-none focus:underline focus:text-black dark:text-white dark:hover:text-white/80 dark:focus:text-white/80">
                                        {{ __('Register') }}
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <div class="max-w-md mx-auto mt-12 mb-12">
                <form method="POST" action="{{ route('login') }}" class="bg-gray-100 bg-opacity-75 shadow-md rounded px-8 pt-6 pb-8">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                        <input id="email" class="block mt-1 w-full bg-gray-200 rounded-md px-3 py-2" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                        <input id="password" class="block mt-1 w-full bg-gray-200 rounded-md px-3 py-2" type="password" name="password" required autocomplete="current-password" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 flex items-center justify-between text-gray-700">
                        <label for="remember_me" class="flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded bg-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 focus:ring-offset-gray-200" name="remember">
                            <span class="ml-2">{{ __('Remember me') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="underline" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-center">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('Log in') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="py-8 text-center text-sm text-black dark:text-white/70">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </footer>
    </div>
</body>

</html>