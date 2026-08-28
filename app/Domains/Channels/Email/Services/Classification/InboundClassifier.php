<?php

namespace App\Domains\Channels\Email\Services\Classification;

use App\Domains\Channels\Email\Models\InboundClassification;
use App\Domains\Channels\Email\Services\Parsing\ParsedEmail;

class InboundClassifier
{
    public function classify(ParsedEmail $email): InboundClassification
    {
        // 1. Bounce detection
        if ($this->isBounce($email)) {
            return InboundClassification::Bounce;
        }

        // 2. Auto-reply / OOO detection
        if ($this->isAutoReply($email)) {
            return InboundClassification::AutoReply;
        }

        // 3. Loop is handled by the loop guard (set by the pipeline)
        // 4. Reply vs New is determined by correlation (set by the pipeline)

        // Default to Reply; caller sets to New if correlation fails
        return InboundClassification::Reply;
    }

    private function isBounce(ParsedEmail $email): bool
    {
        $contentType = $email->headers['content-type'] ?? '';
        if (str_contains($contentType, 'multipart/report')) {
            if (str_contains($contentType, 'report-type=delivery-status')) {
                return true;
            }
        }

        $from = strtolower($email->fromAddress);
        if (preg_match('/(mailer-daemon|postmaster)@/', $from)) {
            return true;
        }

        $returnPath = $email->headers['return-path'] ?? null;
        if ($returnPath === '<>') {
            return true;
        }

        return false;
    }

    private function isAutoReply(ParsedEmail $email): bool
    {
        $autoSubmitted = strtolower($email->headers['auto-submitted'] ?? 'no');
        if ($autoSubmitted !== 'no' && $autoSubmitted !== '') {
            return true;
        }

        if (isset($email->headers['x-auto-response-suppress'])) {
            return true;
        }

        $precedence = strtolower($email->headers['precedence'] ?? '');
        if (in_array($precedence, ['bulk', 'auto_reply', 'auto-reply', 'junk'])) {
            return true;
        }

        if (isset($email->headers['x-autoreply']) || isset($email->headers['x-autorespond'])) {
            return true;
        }

        return false;
    }
}
