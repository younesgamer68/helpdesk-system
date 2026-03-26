<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full {{ $widget->theme_mode === 'dark' ? 'bg-zinc-950 dark' : 'bg-white' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $widget->form_title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Submit button glow effect */
        .btn-submit {
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.2s ease;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .btn-submit:hover::after {
            opacity: 1;
        }
    </style>
</head>

<body
    class="font-sans antialiased h-full overflow-hidden {{ $widget->theme_mode === 'dark' ? 'text-zinc-100 bg-zinc-950' : 'text-gray-900 bg-white' }}"
    x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)">
    <div
        class="h-full w-full overflow-y-auto {{ $widget->theme_mode === 'dark' ? 'bg-zinc-950' : 'bg-gray-50' }} flex flex-col justify-center py-12 sm:px-6 lg:px-8">


        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg" x-show="show"
            x-transition:enter="transition ease-out duration-700 delay-150"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div
                class="{{ $widget->theme_mode === 'dark' ? 'bg-zinc-900 border border-zinc-800' : 'bg-white shadow' }} py-8 px-4 sm:rounded-lg sm:px-10">
                <!-- Form Header -->
                <div>
                    <h2
                        class="text-3xl font-extrabold {{ $widget->theme_mode === 'dark' ? 'text-zinc-100' : 'text-gray-900' }}">
                        {{ $widget->form_title }}</h2>
                    @if ($widget->welcome_message)
                        <p
                            class="mt-2 text-sm {{ $widget->theme_mode === 'dark' ? 'text-zinc-400' : 'text-gray-600' }}">
                            {{ $widget->welcome_message }}</p>
                    @endif
                </div>

                <div class="mt-8">
                    <!-- Success Message (Hidden by default) -->
                    <div id="success-message" class="hidden mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <div
                                class="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Ticket Submitted!</h3>
                            <p class="text-sm text-gray-600 mb-2" id="success-text"></p>
                            <div
                                class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded px-3 py-1">
                                <span class="text-xs text-gray-500">Ticket #</span>
                                <span class="text-sm font-mono font-bold text-gray-900" id="ticket-number"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div id="error-alert" class="hidden mb-6">
                        <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg p-4">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700" id="error-message"></p>
                        </div>
                    </div>

                    <!-- Form -->
                    <form id="ticket-form" class="space-y-6">

                        {{-- Customer Name --}}
                        <div>
                            <label
                                class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }}">
                                Your Name <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" name="customer_name" required
                                    class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none"
                                    placeholder="John Doe">
                            </div>
                        </div>

                        {{-- Customer Email --}}
                        <div>
                            <label
                                class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }}">
                                Your Email <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="email" name="customer_email" required
                                    class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none"
                                    placeholder="john@example.com">
                            </div>
                        </div>

                        {{-- Customer Phone --}}
                        <div>
                            <label
                                class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }}">
                                Phone Number
                                @if ($widget->require_phone)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span
                                        class="{{ $widget->theme_mode === 'dark' ? 'text-zinc-500' : 'text-gray-400' }} text-xs font-normal">(Optional)</span>
                                @endif
                            </label>
                            <div class="mt-1">
                                <input type="tel" name="customer_phone"
                                    {{ $widget->require_phone ? 'required' : '' }}
                                    class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none"
                                    placeholder="+1 (555) 123-4567">
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label
                                class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }}">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" name="subject" required
                                    class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none"
                                    placeholder="Brief description of your issue">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label
                                class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }}">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <textarea name="description" rows="4" required
                                    class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm resize-none {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none"
                                    placeholder="Please provide details about your issue..."></textarea>
                            </div>
                        </div>

                        {{-- Category (Conditional) --}}
                        @if ($widget->show_category && $widget->company->categories->count() > 0)
                            <div>
                                <label
                                    class="block text-sm font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-700' }} mb-1">
                                    Category <span
                                        class="{{ $widget->theme_mode === 'dark' ? 'text-zinc-500' : 'text-gray-400' }} text-xs font-normal">(Optional)</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="category_id"
                                        class="appearance-none block w-full px-3 py-3 border rounded-md shadow-sm sm:text-sm cursor-pointer transition ease-in-out duration-150 {{ $widget->theme_mode === 'dark' ? 'bg-zinc-800 border-zinc-700 text-zinc-100 focus:ring-emerald-500 focus:border-emerald-500' : 'border-gray-300 bg-white placeholder-gray-400 focus:ring-green-500 focus:border-green-500' }} focus:outline-none">
                                        <option value="">Select a category</option>
                                        @foreach ($widget->company->categories as $parentCategory)
                                            @if ($parentCategory->children->isNotEmpty())
                                                <optgroup label="{{ $parentCategory->name }}">
                                                    @foreach ($parentCategory->children as $child)
                                                        <option value="{{ $child->id }}">{{ $child->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 {{ $widget->theme_mode === 'dark' ? 'text-zinc-400' : 'text-gray-500' }}">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Submit Button --}}
                        <div>
                            <button type="submit" id="submit-btn"
                                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white {{ $widget->theme_mode === 'dark' ? 'bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-500' : 'bg-green-500 hover:bg-green-600 focus:ring-green-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 btn-submit">
                                Submit Ticket
                            </button>
                        </div>
                    </form>

                    <div class="mt-6">
                        <p
                            class="text-center text-xs flex items-center justify-center gap-1.5 {{ $widget->theme_mode === 'dark' ? 'text-zinc-500' : 'text-gray-400' }}">
                            Secured by
                            <img src="{{ asset('images/' . ($widget->theme_mode === 'dark' ? 'logodm.png' : 'logolm.png')) }}"
                                alt="Helpdesk" class="inline-block h-4 w-4">
                            <span
                                class="font-medium {{ $widget->theme_mode === 'dark' ? 'text-zinc-300' : 'text-gray-600' }}">Helpdesk</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('ticket-form');
        const submitBtn = document.getElementById('submit-btn');
        const errorAlert = document.getElementById('error-alert');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');
        const ticketNumberDisplay = document.getElementById('ticket-number');
        const successTextDisplay = document.getElementById('success-text');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            const originalBtnText = submitBtn.innerText;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;

            errorAlert.classList.add('hidden');
            successMessage.classList.add('hidden');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(
                    "{{ route('widget.submit', ['company' => $widget->company->slug, 'key' => $widget->widget_key]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify(data)
                    });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Something went wrong');
                }

                // Success
                form.reset();
                form.classList.add('hidden');

                // Show success message
                const successDiv = document.getElementById('success-message');
                successDiv.classList.remove('hidden');
                document.getElementById('ticket-number').textContent = result.ticket_number;
                document.getElementById('success-text').textContent = result.message;

            } catch (error) {
                // Error
                errorMessage.textContent = error.message;
                errorAlert.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                submitBtn.innerHTML = originalBtnText; // Restore button text
            }
        });
    </script>
</body>

</html>
