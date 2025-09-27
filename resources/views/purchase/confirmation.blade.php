<x-checkout-layout :title="'Purchase Confirmed'">
    <div class="max-w-2xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <!-- Success Icon -->
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Purchase Confirmed!</h1>
            <p class="text-gray-600 mb-8">{{ session('message') ?? $message ?? ($allocatedMessage ?? 'Your class pass has been successfully purchased and added to your account!') }}</p>

            <!-- Package Details -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Package Details</h2>
                
                <div class="space-y-4 text-left">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Package</div>
                            <div class="font-medium text-gray-900">{{ $package['name'] ?? 'Class Pass' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Type</div>
                            <div class="font-medium text-gray-900">
                                @if($type === 'package_5')
                                    5 Credits
                                @elseif($type === 'package_10')
                                    10 Credits
                                @elseif($type === 'unlimited')
                                    Unlimited Pass
                                @elseif($type === 'membership')
                                    Monthly Membership
                                @else
                                    {{ Str::title(str_replace('_', ' ', $type)) }}
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Validity</div>
                            <div class="font-medium text-gray-900">
                                @if($type === 'membership')
                                    Monthly Recurring
                                @else
                                    Valid for 1 Month
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Amount Paid</div>
                            <div class="font-medium text-gray-900">£{{ number_format($package['price'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    
                    @if(isset($user))
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Account</div>
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Email</div>
                                    <div class="font-medium text-gray-900">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <h3 class="text-sm font-medium text-blue-800">What's Next?</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                @if($type === 'membership')
                                    <li>Your monthly membership is now active</li>
                                    <li>You'll receive monthly credits automatically</li>
                                    <li>Membership will renew monthly until cancelled</li>
                                @elseif($type === 'unlimited')
                                    <li>Your unlimited pass is now active for 1 month</li>
                                    <li>Book as many classes as you want</li>
                                @else
                                    <li>Your credits are now available in your account</li>
                                    <li>Use them to book any available classes</li>
                                    <li>Credits expire in 1 month from purchase date</li>
                                @endif
                                <li>A confirmation email has been sent to your address</li>
                                @if(!auth()->check() && isset($user) && $user->wasRecentlyCreated)
                                    <li>We've created an account for you with email: <strong>{{ $user->email }}</strong></li>
                                    <li>Check your email for password setup instructions</li>
                                    <li>If you don't receive an email within 5 minutes, check your spam folder</li>
                                    <li>You can also <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">request a password reset</a> using your email</li>
                                @elseif(!auth()->check())
                                    <li>You can <a href="{{ route('login') }}" class="text-blue-600 hover:underline">sign in to your account</a> to manage your credits</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('welcome') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition-colors duration-200">
                    <span>Book a Class</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                @if(auth()->check())
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-colors duration-200">
                        <span>View Dashboard</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('purchase.index') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-colors duration-200">
                        <span>Buy More Credits</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
    </div>
</x-checkout-layout>
