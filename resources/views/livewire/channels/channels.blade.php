<div class="space-y-6">
    <div>
        <h1 class="text-3xl text-zinc-900 dark:text-zinc-100">Channels</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Manage how customers reach you</p>
    </div>

    <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-800">
        @php
            $tabs = [
                'form_widget' => 'Form Widget',
                'ai_chatbot_widget' => 'AI Chatbot Widget',
            ];
        @endphp

        @foreach ($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')" @class([
                'px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px',
                'border-teal-500 text-teal-400' => $activeTab === $key,
                'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' =>
                    $activeTab !== $key,
            ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div>
        @if ($activeTab === 'form_widget')
            @livewire('settings.form-widget')
        @else
            @livewire('channels.ai-chatbot-widget')
        @endif
    </div>
</div>
