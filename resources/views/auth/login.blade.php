<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />
      

        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <img id="background" class="absolute top-0 left-0 w-full h-full object-cover z-index-0" src="https://cdn.pixabay.com/photo/2018/01/22/14/13/animal-3099035_1280.jpg" alt="Paseo a caballo">
            <div class="max-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white bg-cover bg-center">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <!-- Modal de usuario verificado -->
                   
                    <form method="POST" action="{{ route('login') }}" class="bg-gray-100 bg-opacity-75 shadow-md rounded px-8 pt-6 pb-8 mb-4 max-w-md mx-auto">
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

                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                {{ __('Log in') }}
                            </button>
                        </div>
                    </form>

                    <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </footer>
                </div>
            </div>
        </div>
    </x-authentication-card>

  
</x-guest-layout>