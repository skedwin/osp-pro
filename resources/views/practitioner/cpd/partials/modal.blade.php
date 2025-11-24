<!-- Claim Token Modal -->
<div
    x-show="claimTokenModalOpen"
    x-cloak
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
    @keydown.escape.window="closeModal()"
>
    <div
        x-show="claimTokenModalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="closeModal()"
        class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl lg:p-8"
    >
        <!-- Close Button -->
        <button
            type="button"
            @click="closeModal()"
            class="absolute top-4 right-4 inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700"
            aria-label="Close modal"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Modal Header -->
        <div class="mb-6 pr-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="rounded-xl bg-blue-100 p-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800">Claim CPD Token</h3>
            </div>
            <p class="text-sm text-slate-500">
                Enter your event token to claim your CPD points
            </p>
        </div>

        <!-- Response Message -->
        <div x-show="responseMessage" x-cloak class="mb-4">
            <div 
                x-bind:class="{
                    'bg-green-50 border-green-200 text-green-800': responseType === 'success',
                    'bg-red-50 border-red-200 text-red-800': responseType === 'error'
                }"
                class="flex items-start gap-3 rounded-xl border p-4"
            >
                <svg 
                    x-show="responseType === 'success'"
                    class="w-5 h-5 flex-shrink-0 mt-0.5" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg 
                    x-show="responseType === 'error'"
                    class="w-5 h-5 flex-shrink-0 mt-0.5" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium flex-1" x-text="responseMessage"></p>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="claimToken()" class="space-y-4">
            <div>
                <label for="event_token" class="block text-sm font-semibold text-slate-700 mb-2">
                    Event Token <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="event_token"
                    x-model="eventToken"
                    required
                    :disabled="isSubmitting"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-100 disabled:cursor-not-allowed"
                    placeholder="Enter your event token"
                    autofocus
                >
                <p class="mt-1.5 text-xs text-slate-500">
                    Enter the event token provided by your CPD activity organizer
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    @click="closeModal()"
                    :disabled="isSubmitting"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-200 rounded-xl border-2 border-slate-300 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isSubmitting || !eventToken.trim()"
                    class="flex-1 flex items-center justify-center p-3 font-medium text-white rounded-lg bg-brand-500 text-theme-sm hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!isSubmitting">Claim Token</span>
                    <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
