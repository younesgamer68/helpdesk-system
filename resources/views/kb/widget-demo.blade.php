<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Widget Preview - {{ $company->name }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-zinc-100 dark:bg-zinc-950 antialiased min-h-screen">

    {{-- Top bar --}}
    <div
        class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Widget Preview</span>
            <span
                class="text-xs bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full font-medium">Live</span>
        </div>
        <a href="{{ url()->previous() }}"
            class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">&larr;
            Back</a>
    </div>

    {{-- Simulated website content --}}
    <div class="max-w-3xl mx-auto px-6 py-16 space-y-8">
        <div class="space-y-4">
            <div class="h-8 w-48 bg-zinc-300 dark:bg-zinc-800 rounded-lg"></div>
            <div class="h-4 w-full bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="h-4 w-5/6 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="h-4 w-4/6 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
        </div>
        <div class="space-y-4">
            <div class="h-6 w-40 bg-zinc-300 dark:bg-zinc-800 rounded-lg"></div>
            <div class="h-4 w-full bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="h-4 w-3/4 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="h-4 w-5/6 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="h-32 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
            <div class="h-32 bg-zinc-200 dark:bg-zinc-800 rounded-xl"></div>
        </div>
        <div class="space-y-4">
            <div class="h-4 w-full bg-zinc-200 dark:bg-zinc-800 rounded"></div>
            <div class="h-4 w-2/3 bg-zinc-200 dark:bg-zinc-800 rounded"></div>
        </div>

        <p class="text-center text-sm text-zinc-400 dark:text-zinc-500 pt-8">
            This is a preview of how the widget appears on your website.
            <br>Click the <span class="text-emerald-600 dark:text-emerald-400 font-medium">teal button</span> in the
            bottom-right corner to try it.
        </p>
    </div>

    {{-- Load the actual widget with company defaults --}}
    <script src="{{ $widgetScriptUrl }}" defer data-default-link-mode="{{ $widgetDefaultLinkMode }}"
        @if (filled($widgetArticleBaseUrl)) data-article-base-url="{{ $widgetArticleBaseUrl }}" @endif></script>
</body>

</html>
