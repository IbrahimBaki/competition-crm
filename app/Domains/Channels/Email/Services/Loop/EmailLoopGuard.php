<?php

namespace App\Domains\Channels\Email\Services\Loop;

use App\Domains\Channels\Email\Models\EmailLoopGuard as LoopGuardModel;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Detects and suppresses email loops with a documented, deterministic mechanism.
 *
 * Mechanism:
 * - Every outbound ticket email is stamped with Message-ID of form <{uuid}@{config('channels.email.message_id_domain')}>
 * - Inbound mail whose In-Reply-To/References points at OUR Message-ID domain AND which is auto-reply/bounce is dropped
 * - Per-sender counter (keyed by sha256(lower(from_address))) in email_loop_guards table
 * - More than config('channels.email.loop.max_per_window') messages within config('channels.email.loop.window_minutes')
 *   window trips the guard, marking classification as Loop, state as suppressed, and emitting critical log + audit record
 */
class EmailLoopGuard
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function checkAndUpdate(string $fromAddress): bool
    {
        $senderHash = $this->hashSender($fromAddress);
        $windowMinutes = config('channels.email.loop.window_minutes', 5);
        $maxPerWindow = config('channels.email.loop.max_per_window', 10);
        $windowStart = now()->subMinutes($windowMinutes);

        return DB::transaction(function () use ($senderHash, $windowStart, $maxPerWindow) {
            $guard = LoopGuardModel::where('sender_hash', $senderHash)
                ->lockForUpdate()
                ->first();

            if ($guard && $guard->window_started_at->greaterThan($windowStart)) {
                $guard->increment('hits');

                if ($guard->hits > $maxPerWindow && ! $guard->tripped) {
                    $guard->update(['tripped' => true]);
                    $this->raiseAlert($guard);

                    return true;
                }

                return $guard->tripped;
            }

            if (! $guard) {
                LoopGuardModel::create([
                    'sender_hash' => $senderHash,
                    'window_started_at' => now(),
                    'hits' => 1,
                    'tripped' => false,
                ]);

                return false;
            }

            $guard->update([
                'window_started_at' => now(),
                'hits' => 1,
                'tripped' => false,
            ]);

            return false;
        });
    }

    private function hashSender(string $fromAddress): string
    {
        return hash('sha256', strtolower(trim($fromAddress)));
    }

    private function raiseAlert(LoopGuardModel $guard): void
    {
        $message = "Email loop detected for sender_hash: {$guard->sender_hash}, hits: {$guard->hits}";

        Log::channel(config('channels.email.alert_log_channel', 'single'))
            ->critical($message);

        $this->auditLogger->record(
            action: 'email.loop_detected',
            subject: $guard,
            details: [
                'sender_hash' => $guard->sender_hash,
                'hits' => $guard->hits,
                'window_minutes' => config('channels.email.loop.window_minutes', 5),
            ],
        );
    }
}
