<x-app-layout>
    <div class="max-w-3xl mx-auto mt-10 px-5 py-10 bg-white rounded-lg shadow-lg flex flex-col items-center justify-center">
        <div class="mb-4 text-lg text-gray-600 text-center">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-lg text-green-600 text-center">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="mt-4 flex flex-col gap-4 items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-primary-button>
                    {{ __('Log Out') }}
                </x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
