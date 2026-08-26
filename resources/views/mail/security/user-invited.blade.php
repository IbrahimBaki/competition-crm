<?php /* @var App\Domains\Security\Models\UserInvitation $invitation */ ?>
<?php /* @var string $rawToken */ ?>

You are invited to join Support CRM. Please accept this invitation using the link below:

{{ config('app.spa_url') }}/accept-invite?token={{ $rawToken }}

This link expires in 72 hours.
