<x-mail::message>
    # You've been invited!

    Hello,

    **{{ $invitedBy }}** has invited you to join their team on **{{ $tenantName }}**.

    As a member of the team, you'll be able to collaborate on messaging campaigns, manage contacts, and track
    performance.

    <x-mail::button :url="$url">
        Accept Invitation
    </x-mail::button>

    This invitation will expire in 7 days. If you were not expecting this invitation, you can safely ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>