<?php

namespace App\Livewire\Channels;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KbWidget extends Component
{
    public string $companySlug = '';

    public string $domain = '';

    public ?string $copiedKey = null;

    public function mount(): void
    {
        $this->companySlug = Auth::user()->company->slug;
        $this->domain = config('app.domain');
    }

    public function getScriptTagProperty(): string
    {
        $protocol = config('app.env') === 'local' ? 'http' : 'https';
        $widgetVersion = filemtime(resource_path('views/kb/widget-js.blade.php')) ?: time();
        $company = Auth::user()->company;

        $attributes = [
            'src="'.$protocol.'://'.$this->companySlug.'.'.$this->domain.'/kb/widget.js?v='.$widgetVersion.'"',
            'defer',
            'data-default-link-mode="'.($company->kb_widget_link_mode === 'custom' ? 'custom' : 'portal').'"',
        ];

        if (filled($company->kb_widget_article_base_url)) {
            $attributes[] = 'data-article-base-url="'.e(rtrim((string) $company->kb_widget_article_base_url, '/')).'"';
        }

        return '<script '.implode(' ', $attributes).'></script>';
    }

    public function getWidgetUrlProperty(): string
    {
        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return $protocol.'://'.$this->companySlug.'.'.$this->domain.'/kb/widget-demo';
    }

    public function copyToClipboard(string $text, string $key): void
    {
        $this->copiedKey = $key;

        $this->dispatch('copy-to-clipboard', text: $text);
        $this->dispatch('show-toast', message: 'Copied to clipboard!', type: 'success');
        $this->js('setTimeout(() => $wire.set("copiedKey", null), 2000)');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.channels.kb-widget');
    }
}
