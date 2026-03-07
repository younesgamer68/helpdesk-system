<x-mail::message>
# You've Been Invited to {{ $user->company ? $user->company->name : config('app.name') }}!

Hi **{{ $user->name }}**,

You have been invited to join **{{ $user->company ? $user->company->name : config('app.name') }}** as a Support **{{ ucfirst($user->role) }}**.

To get started and access the helpdesk, simply accept your invitation by setting up your password below:

<x-mail::button :url="$signedUrl" color="primary">
Accept Invitation
</x-mail::button>

Alternatively, you can copy and paste the following link into your browser:
<br>
[{{ $signedUrl }}]({{ $signedUrl }})

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
