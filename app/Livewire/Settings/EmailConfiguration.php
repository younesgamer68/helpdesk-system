<?php

namespace App\Livewire\Settings;

use App\Models\CompanyMailSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailConfiguration extends Component
{
    public string $smtpHost = '';

    public ?int $smtpPort = 587;

    public string $smtpUsername = '';

    public string $smtpPassword = '';

    public string $smtpEncryption = 'tls';

    public string $fromName = '';

    public string $fromEmail = '';

    public string $mailSubjectPrefix = '';

    public string $mailFooterText = '';

    public function mount(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $settings = Auth::user()->company->mailSettings;

        if ($settings) {
            $this->smtpHost = $settings->smtp_host ?? '';
            $this->smtpPort = $settings->smtp_port ?? 587;
            $this->smtpUsername = $settings->smtp_username ?? '';
            $this->smtpPassword = $settings->smtp_password ?? '';
            $this->smtpEncryption = $settings->smtp_encryption ?? 'tls';
            $this->fromName = $settings->from_name ?? '';
            $this->fromEmail = $settings->from_email ?? '';
            $this->mailSubjectPrefix = $settings->mail_subject_prefix ?? '';
            $this->mailFooterText = $settings->mail_footer_text ?? '';
        }
    }

    public function save(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'smtpHost' => ['nullable', 'string', 'max:255'],
            'smtpPort' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtpUsername' => ['nullable', 'string', 'max:255'],
            'smtpPassword' => ['nullable', 'string', 'max:255'],
            'smtpEncryption' => ['required', 'in:tls,ssl,starttls,none'],
            'fromName' => ['nullable', 'string', 'max:255'],
            'fromEmail' => ['nullable', 'email', 'max:255'],
            'mailSubjectPrefix' => ['nullable', 'string', 'max:50'],
            'mailFooterText' => ['nullable', 'string', 'max:500'],
        ]);

        $company = Auth::user()->company;

        $data = [
            'smtp_host' => $this->smtpHost ?: null,
            'smtp_port' => $this->smtpPort,
            'smtp_username' => $this->smtpUsername ?: null,
            'smtp_encryption' => $this->smtpEncryption,
            'from_name' => $this->fromName ?: null,
            'from_email' => $this->fromEmail ?: null,
            'mail_subject_prefix' => $this->mailSubjectPrefix ?: null,
            'mail_footer_text' => $this->mailFooterText ?: null,
        ];

        // Only update password if a new one was provided
        if ($this->smtpPassword) {
            $data['smtp_password'] = $this->smtpPassword;
        }

        CompanyMailSettings::updateOrCreate(
            ['company_id' => $company->id],
            $data,
        );

        $this->dispatch('email-config-saved');
    }

    public function render()
    {
        return view('livewire.settings.email-configuration');
    }
}
