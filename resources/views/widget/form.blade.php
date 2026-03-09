<!DOCTYPE html>
<html lang="en" class="{{ $widget->theme_mode === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $widget->form_title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        /* Smooth focus transitions */
        input:focus, textarea:focus, select:focus {
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        /* Custom scrollbar for dark mode */
        .dark ::-webkit-scrollbar { width: 6px; }
        .dark ::-webkit-scrollbar-track { background: #18181b; }
        .dark ::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 3px; }

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
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        .btn-submit:hover::after { opacity: 1; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-zinc-950 transition-colors duration-200">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-xl">

            {{-- Success Message (Hidden by default) --}}
            <div id="success-message" class="hidden">
                <div class="bg-white dark:bg-zinc-900 border border-emerald-200 dark:border-emerald-800/40 rounded-xl p-8 shadow-lg dark:shadow-zinc-950/50 text-center">
                    <div class="mx-auto w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mb-5">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Ticket Submitted!</h3>
                    <p class="text-gray-600 dark:text-zinc-400 mb-4" id="success-text"></p>
                    <div class="inline-flex items-center gap-2 bg-gray-100 dark:bg-zinc-800 rounded-lg px-4 py-2">
                        <span class="text-sm text-gray-500 dark:text-zinc-500">Ticket</span>
                        <span class="text-sm font-mono font-semibold text-gray-900 dark:text-white" id="ticket-number"></span>
                    </div>
                </div>
            </div>

            {{-- Form Card --}}
            <div id="widget-form">
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-lg dark:shadow-zinc-950/50 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-8 pt-8 pb-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $widget->form_title }}</h1>
                        @if($widget->welcome_message)
                            <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400 leading-relaxed">{{ $widget->welcome_message }}</p>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 dark:border-zinc-800"></div>

                    {{-- Error Alert --}}
                    <div id="error-alert" class="hidden mx-8 mt-6">
                        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/40 rounded-lg p-4">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700 dark:text-red-400" id="error-message"></p>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form id="ticket-form" class="p-8 space-y-5">

                        {{-- Customer Name --}}
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                Your Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="customer_name" required
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors"
                                placeholder="John Doe">
                        </div>

                        {{-- Customer Email --}}
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                Your Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="customer_email" required
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors"
                                placeholder="john@example.com">
                        </div>

                        {{-- Customer Phone --}}
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                Phone Number
                                @if($widget->require_phone)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-gray-400 dark:text-zinc-500 text-xs font-normal">(Optional)</span>
                                @endif
                            </label>
                            <input type="tel" name="customer_phone" {{ $widget->require_phone ? 'required' : '' }}
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors"
                                placeholder="+1 (555) 123-4567">
                        </div>

                        {{-- Subject --}}
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="subject" required
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors"
                                placeholder="Brief description of your issue">
                        </div>

                        {{-- Description --}}
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" rows="4" required
                                class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors resize-none"
                                placeholder="Please provide details about your issue..."></textarea>
                        </div>

                        {{-- Category (Conditional) --}}
                        @if($widget->show_category && $widget->company->categories->count() > 0)
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                                    Category <span class="text-gray-400 dark:text-zinc-500 text-xs font-normal">(Optional)</span>
                                </label>
                                <select name="category_id"
                                    class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 dark:focus:ring-teal-400/20 dark:focus:border-teal-400 transition-colors">
                                    @foreach($widget->company->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <button type="submit" id="submit-btn"
                                class="btn-submit w-full bg-teal-500 hover:bg-teal-600 dark:bg-teal-500 dark:hover:bg-teal-400 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                <span>Submit Ticket</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <p class="text-center text-xs text-gray-400 dark:text-zinc-600 mt-4">
                    Secured by {{ $widget->company->name }}
                </p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('ticket-form');
        const submitBtn = document.getElementById('submit-btn');
        const errorAlert = document.getElementById('error-alert');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');
        const widgetForm = document.getElementById('widget-form');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Submitting...</span>
            `;

            errorAlert.classList.add('hidden');

            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const response = await fetch('{{ route("widget.submit", ["company"=> $widget->company->slug, "key" => $widget->widget_key]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    document.getElementById('success-text').textContent = result.message;
                    document.getElementById('ticket-number').textContent = result.ticket_number;
                    successMessage.classList.remove('hidden');
                    widgetForm.classList.add('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                errorMessage.textContent = error.message || 'An error occurred. Please try again.';
                errorAlert.classList.remove('hidden');

                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                submitBtn.innerHTML = `
                    <span>Submit Ticket</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                `;
            }
        });
    </script>
</body>
</html>