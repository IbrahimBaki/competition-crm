<?php

namespace App\Domains\Channels\WebForm\Services\Intake;

use App\Domains\Channels\WebForm\Exceptions\WebFormInactiveException;
use App\Domains\Channels\WebForm\Models\WebForm;
use App\Domains\Channels\WebForm\Models\WebFormSubmission;
use App\Domains\Channels\WebForm\Models\WebFormSubmissionState;
use App\Domains\Channels\WebForm\Services\Dedupe\SubmissionFingerprint;
use App\Domains\Channels\WebForm\Services\Dedupe\WebFormDeduplicator;
use App\Domains\Channels\WebForm\Services\Validation\WebFormPayloadValidator;
use App\Domains\Customers\Actions\CreateCustomer;
use App\Domains\Customers\Services\CustomerIntakeGuard;
use App\Domains\Customers\Services\Identity\CustomerIdentityResolver;
use App\Domains\Ticketing\Actions\CreateTicket;
use App\Domains\Ticketing\Models\TicketPriority;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SubmitWebForm
{
    public function __construct(
        private readonly WebFormPayloadValidator $payloadValidator,
        private readonly SubmissionFingerprint $fingerprinter,
        private readonly WebFormDeduplicator $deduplicator,
        private readonly CustomerIdentityResolver $identityResolver,
        private readonly CustomerIntakeGuard $intakeGuard,
        private readonly CreateCustomer $createCustomer,
        private readonly CreateTicket $createTicket,
    ) {}

    public function handle(
        WebForm $form,
        array $payload,
        ?string $ipHash,
        ?string $userAgent,
    ): WebFormSubmissionResult {
        // Verify form is active
        if (! $form->is_active) {
            throw new WebFormInactiveException;
        }

        // Validate the payload
        $normalised = $this->payloadValidator->validate($form, $payload);

        // Extract submitter identities
        $identities = [];
        $submitterIdentity = null;
        foreach ($form->fields as $field) {
            if ($field->maps_to === 'email' && isset($normalised[$field->key])) {
                $identities[] = ['type' => 'email', 'value' => $normalised[$field->key]];
                $submitterIdentity ??= $normalised[$field->key];
            } elseif ($field->maps_to === 'phone' && isset($normalised[$field->key])) {
                $identities[] = ['type' => 'phone', 'value' => $normalised[$field->key]];
                $submitterIdentity ??= $normalised[$field->key];
            }
        }

        // Compute fingerprint
        $fingerprint = $this->fingerprinter->for($form, $normalised, $submitterIdentity);

        // Check for duplicate
        $duplicate = $this->deduplicator->findRecent($form, $fingerprint);

        return DB::transaction(function () use (
            $form,
            $normalised,
            $ipHash,
            $userAgent,
            $fingerprint,
            $duplicate,
            $identities,
        ) {
            if ($duplicate && $duplicate->ticket) {
                // Record as duplicate and return original ticket reference
                $submission = WebFormSubmission::create([
                    'web_form_id' => $form->id,
                    'state' => WebFormSubmissionState::Duplicate->value,
                    'fingerprint' => $fingerprint,
                    'tracking_token' => Str::random(32),
                    'payload' => $normalised,
                    'submitter_ip_hash' => $ipHash,
                    'user_agent' => $userAgent,
                    'duplicate_of_id' => $duplicate->id,
                ]);

                return new WebFormSubmissionResult(
                    ticketReference: $duplicate->ticket->reference,
                    trackingToken: $duplicate->tracking_token,
                    wasDuplicate: true,
                );
            }

            // Resolve or create customer
            $resolution = $this->identityResolver->resolve($identities);

            if ($resolution->customer) {
                $customer = $resolution->customer;
                // Guard against blocked customers
                $this->intakeGuard->guardCustomer($customer);
            } else {
                // Create new customer
                $customer = $this->createCustomer->execute([
                    'name' => $normalised[$this->findFieldByMapsTo($form, 'name')] ?? 'Anonymous',
                    'company_account_id' => null,
                    'preferred_locale' => app()->getLocale(),
                ]);
            }

            // Extract subject and body
            $subject = null;
            $body = null;

            foreach ($form->fields as $field) {
                if ($field->maps_to === 'subject') {
                    $subject = $normalised[$field->key] ?? null;
                }
                if ($field->maps_to === 'body') {
                    $body = $normalised[$field->key] ?? null;
                }
            }

            $subject ??= (string) $form->title;
            $body ??= json_encode($normalised, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Create ticket
            $ticket = $this->createTicket->handle(
                customer: $customer,
                department: $form->department,
                priority: TicketPriority::from($form->default_priority),
                subject: $subject,
                body: $body,
                category: $form->category,
                customFields: [],
            );

            // Create submission record
            $trackingToken = Str::random(32);
            $submission = WebFormSubmission::create([
                'web_form_id' => $form->id,
                'ticket_id' => $ticket->id,
                'customer_id' => $customer->id,
                'state' => WebFormSubmissionState::Accepted->value,
                'fingerprint' => $fingerprint,
                'tracking_token' => $trackingToken,
                'payload' => $normalised,
                'submitter_ip_hash' => $ipHash,
                'user_agent' => $userAgent,
            ]);

            // TODO: Dispatch acknowledgement notification

            return new WebFormSubmissionResult(
                ticketReference: $ticket->reference,
                trackingToken: $trackingToken,
                wasDuplicate: false,
            );
        });
    }

    private function findFieldByMapsTo(WebForm $form, string $mapsTo): ?string
    {
        return $form->fields->first(fn ($field) => $field->maps_to === $mapsTo)?->key;
    }
}
