<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $widget->form_title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $widget->primary_color }};
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .btn-primary {
            background-color: var(--primary-color);
        }
        .btn-primary:hover {
            filter: brightness(0.9);
        }
        .text-primary {
            color: var(--primary-color);
        }
        .border-primary {
            border-color: var(--primary-color);
        }
        .focus\:ring-primary:focus {
            --tw-ring-color: var(--primary-color);
            --tw-ring-opacity: 0.2;
        }
    </style>
</head>
<body class="p-6" style="background: #0A170F;">
    <div class="max-w-2xl mx-auto">
        {{-- Success Message (Hidden by default) --}}
        <div id="success-message" class="hidden bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-start gap-3">1
                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-green-900 mb-1">Success!</h3>
                    <p class="text-green-700" id="success-text"></p>
                    <p class="text-sm text-green-600 mt-2">Ticket #: <span id="ticket-number" class="font-mono font-bold"></span></p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div id="widget-form" class="rounded-lg  p-8">
            {{-- Header --}}
            <div class="text-center mb-6">
                <h1 class="text-4xl font-bold text-white mb-2">{{ $widget->form_title }}</h1>
                @if($widget->welcome_message)
                    <p class="text-gray-300">{{ $widget->welcome_message }}</p>
                @endif
            </div>

            {{-- Error Alert --}}
            <div id="error-alert" class="hidden bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-red-700" id="error-message"></div>
                </div>
            </div>

            <form id="ticket-form" class="space-y-4">
                {{-- Customer Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                        Your Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="customer_name" required
                        class="w-full text-white px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors"
                        placeholder="John Doe">
                </div>

                {{-- Customer Email --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                        Your Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="customer_email" required
                        class="w-full text-white px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors"
                        placeholder="john@example.com">
                </div>

                {{-- Customer Phone --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                        Phone Number 
                        @if($widget->require_phone)
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-400 text-xs">(Optional)</span>
                        @endif
                    </label>
                    <input type="tel" name="customer_phone" {{ $widget->require_phone ? 'required' : '' }}
                        class="w-full text-white px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors"
                        placeholder="+1 (555) 123-4567">
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" required
                        class="w-full text-white px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors"
                        placeholder="Brief description of your issue">
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" rows="5" required
                        class="w-full text-white px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors resize-none"
                        placeholder="Please provide details about your issue..."></textarea>
                </div>

                {{-- Category (Conditional) --}}
                @if($widget->show_category && $widget->company->categories->count() > 0)
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-1.5">
                            Category <span class="text-gray-400 text-xs">(Optional)</span>
                        </label>
                        <select name="category_id"
                            class="w-full text-white text-gray-300 px-4 py-2.5 border bg-transparent border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary transition-colors">
                           
                            @foreach($widget->company->categories as $category)
                                <option  value="{{ $category->id }}" class="text-black">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
            @endif

                {{-- Submit Button --}}
                <button type="submit" id="submit-btn"
                    class="w-full btn-primary text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-lg">
                    <span>Submit Ticket</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
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
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Submitting...</span>
            `;
            
            // Hide errors
            errorAlert.classList.add('hidden');

            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const response = await fetch('{{ route("widget.submit", ["company"=> Auth::user()->company->slug, "key" => $widget->widget_key]) }}', {
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
                    // Show success message
                    document.getElementById('success-text').textContent = result.message;
                    document.getElementById('ticket-number').textContent = result.ticket_number;
                    successMessage.classList.remove('hidden');
                    widgetForm.classList.add('hidden');
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                // Show error
                errorMessage.textContent = error.message || 'An error occurred. Please try again.';
                errorAlert.classList.remove('hidden');
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <span>Submit Ticket</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                `;
            }
        });
    </script>
</body>
</html>