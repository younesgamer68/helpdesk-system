<x-layouts::auth>
    <div class="min-h-screen bg-[#0A170F] flex items-center justify-center px-4">

        <!-- ====== HEADER ====== -->
        <div class="absolute top-0 left-0 right-0 px-6 py-4 flex justify-between items-center border-b border-white">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 hover:opacity-80 transition">
                <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo" class="w-10 h-10 object-contain">
                <span class="text-white font-semibold text-sm">HelpDesk</span>
            </a>
            <a href="{{ route('login') }}" class="bg-white text-slate-900 px-6 py-2 rounded font-semibold text-sm hover:bg-gray-100 transition">
                Log in
            </a>
        </div>

        <!-- ====== MAIN CONTENT ====== -->
        <div class="w-full max-w-md text-center">



            <!-- Email Form -->
            <form method="POST" action="{{ route('register') }}" id="signup-form" class="space-y-6">
                @csrf

                <div class="text-left">
                    <label class="text-white text-sm font-medium block mb-3">Business email</label>
                    <input
                        id="emailInput"
                        name="email"
                        type="email"
                        autocomplete="email"
                        placeholder="Enter your business email"
                        value="{{ old('email') }}"
                        class="w-full bg-transparent border border-white/30 text-white placeholder-white/40 py-3 px-4 rounded-lg focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition"
                    >
                    @error('email')
                        <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <p id="emailError" class="text-red-400 text-sm mt-2 hidden"></p>
                </div>

                <button
                    type="submit"
                    id="signupBtn"
                    class="w-full text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2"
                    style="background-color:#0F766E;"
                    onmouseover="this.style.backgroundColor='#0d6963'"
                    onmouseout="this.style.backgroundColor='#0F766E'">
                    <span id="btnText">Sign up with email</span>
                    <svg id="btnSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/>
                        <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </button>
            </form>

         
        </div>

        <!-- Bottom Icon -->
        <div class="absolute bottom-6 right-6 bg-white p-2 rounded-lg shadow-lg">
            <svg class="w-6 h-6 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5-9h10v2H7z"/>
            </svg>
        </div>
    </div>

</x-layouts::auth>