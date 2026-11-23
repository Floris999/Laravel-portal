<x-mail::message>
# {{ __('auth.verify_email_heading') }}!

{{ __('auth.verify_email_description') }}

<x-mail::button :url="$verificationUrl">
{{ __('auth.verify_email_button') }}
</x-mail::button>

{{ __('auth.verify_email_no_action') }}

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
