<?php

namespace App\Livewire\Tickets\Kb;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('KB API Reference')]
class ApiReference extends Component
{
    public string $companySlug = '';

    public string $domain = '';

    public string $widgetVersion = '';

    public ?string $widgetArticleBaseUrl = '';

    public bool $widgetOpenInNewTab = true;

    public string $widgetDefaultLinkMode = 'portal';

    public ?string $copiedKey = null;

    protected function widgetDefaultsRules(): array
    {
        return [
            'widgetDefaultLinkMode' => ['required', Rule::in(['portal', 'custom'])],
            'widgetArticleBaseUrl' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\/[^\/]+\/kb\/article\/?$/i'],
            'widgetOpenInNewTab' => ['boolean'],
        ];
    }

    protected function widgetDefaultsMessages(): array
    {
        return [
            'widgetArticleBaseUrl.regex' => 'Article Base URL must use this format: https://yourcompany.com/kb/article',
        ];
    }

    public function mount(): void
    {
        $company = Auth::user()->company;

        $this->companySlug = $company->slug;
        $this->domain = config('app.domain');
        $this->widgetVersion = (string) (filemtime(resource_path('views/kb/widget-js.blade.php')) ?: time());
        $this->widgetArticleBaseUrl = $company->kb_widget_article_base_url
            ?? ($company->website ? rtrim($company->website, '/').'/kb/article' : '');
        $this->widgetDefaultLinkMode = $company->kb_widget_link_mode === 'custom' ? 'custom' : 'portal';
    }

    public function getBaseUrlProperty(): string
    {
        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return $protocol.'://'.$this->companySlug.'.'.$this->domain.'/api/kb/'.$this->companySlug;
    }

    public function getWidgetScriptSrcProperty(): string
    {
        $protocol = config('app.env') === 'local' ? 'http' : 'https';

        return $protocol.'://'.$this->companySlug.'.'.$this->domain.'/kb/widget.js?v='.$this->widgetVersion;
    }

    public function getWidgetScriptTagProperty(): string
    {
        $baseUrl = trim((string) $this->widgetArticleBaseUrl);
        $useCustomByDefault = $this->widgetDefaultLinkMode === 'custom' && $baseUrl !== '';

        $attributes = [
            'src="'.$this->widgetScriptSrc.'"',
            'defer',
        ];

        if ($baseUrl !== '') {
            $attributes[] = 'data-article-base-url="'.e($baseUrl).'"';
        }

        $attributes[] = 'data-default-link-mode="'.($useCustomByDefault ? 'custom' : 'portal').'"';

        $attributes[] = 'data-open-in-new-tab="'.($this->widgetOpenInNewTab ? 'true' : 'false').'"';

        return '<script '.implode(' ', $attributes).'></script>';
    }

    public function updatedWidgetDefaultLinkMode(): void
    {
        if ($this->widgetDefaultLinkMode !== 'custom') {
            $this->resetErrorBag('widgetArticleBaseUrl');

            return;
        }

        $this->updatedWidgetArticleBaseUrl();
    }

    public function updatedWidgetArticleBaseUrl(): void
    {
        if ($this->widgetDefaultLinkMode !== 'custom') {
            $this->resetErrorBag('widgetArticleBaseUrl');

            return;
        }

        $this->resetErrorBag('widgetArticleBaseUrl');

        if (blank($this->widgetArticleBaseUrl)) {
            return;
        }

        $this->validateOnly('widgetArticleBaseUrl', $this->widgetDefaultsRules(), $this->widgetDefaultsMessages());
    }

    public function saveWidgetDefaults(): void
    {
        if (! Schema::hasColumns('companies', ['kb_widget_link_mode', 'kb_widget_article_base_url'])) {
            $this->dispatch('show-toast', message: 'Database update required. Please run: php artisan migrate', type: 'error');

            return;
        }

        try {
            $this->validate($this->widgetDefaultsRules(), $this->widgetDefaultsMessages());
        } catch (ValidationException $exception) {
            $this->dispatch('show-toast', message: 'Please fix the highlighted fields and try again.', type: 'error');

            throw $exception;
        }

        if ($this->widgetDefaultLinkMode === 'custom' && blank($this->widgetArticleBaseUrl)) {
            $this->addError('widgetArticleBaseUrl', 'Please provide a custom article base URL to use custom mode.');
            $this->dispatch('show-toast', message: 'Custom URL is required when custom mode is selected.', type: 'error');

            return;
        }

        $company = Auth::user()->company;

        $company->update([
            'kb_widget_link_mode' => $this->widgetDefaultLinkMode,
            'kb_widget_article_base_url' => filled($this->widgetArticleBaseUrl)
                ? rtrim((string) $this->widgetArticleBaseUrl, '/')
                : null,
        ]);

        $this->dispatch('show-toast', message: 'Widget defaults saved.', type: 'success');
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
        return view('livewire.tickets.kb.api-reference');
    }
}
