<x-mail::message>
    # Your invitation has expired

    Hi **{{ $user->name }}**,

    Your invitation link to join **{{ $user->company ? $user->company->name : config('app.name') }}** has expired.

    Please contact your administrator and ask them to resend a new invitation link.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
