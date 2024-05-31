<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        <div>
            <div>
                <div>
                    <div class="mb-4 text-lg text-blue-900 dark:text-blue-300">
                        {{ __('Gallop to your email and verify your rider account.') }}
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-4 font-medium text-sm text-green-700 dark:text-green-300">
                            {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
                        </div>
                    @endif

                    <div>
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf

                            <div class="mr-4">
                                <x-button type="submit" class="text-white bg-blue-500 hover:bg-blue-700">
                                    {{ __('Resend Verification Email') }}
                                </x-button>
                            </div>
                        </form>

                        <div class="mt-4 flex items-center justify-between">
                            <a
                                href="{{ route('profile.show') }}"
                                class="underline text-sm text-gray-900 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                            >
                                {{ __('Edit Profile') }}</a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf

                                <button type="submit" class="underline text-sm text-gray-900 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 ms-2">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>



                    </div>
                    
                </div>

            </div>

        </div>

    </x-authentication-card>
</x-guest-layout>