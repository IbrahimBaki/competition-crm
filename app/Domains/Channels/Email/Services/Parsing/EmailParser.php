<?php

namespace App\Domains\Channels\Email\Services\Parsing;

class EmailParser
{
    public function parse(string $rawMime): ParsedEmail
    {
        $parts = explode("\r\n\r\n", $rawMime, 2);
        $headersPart = $parts[0];
        $bodyPart = $parts[1] ?? '';

        $headers = $this->parseHeaders($headersPart);
        $messageId = $headers['message-id'] ?? null;
        $inReplyTo = $headers['in-reply-to'] ?? null;
        $referenceIds = $this->parseReferences($headers['references'] ?? '');
        $fromAddress = $this->extractEmail($headers['from'] ?? '');
        $fromName = $this->extractName($headers['from'] ?? '');
        $toAddress = $this->extractEmail($headers['to'] ?? '');
        $subject = $headers['subject'] ?? null;

        if ($subject && mb_strlen($subject, 'UTF-8') > 512) {
            $subject = mb_substr($subject, 0, 512, 'UTF-8');
        }

        [$textBody, $htmlBody] = $this->extractBodies($headers, $bodyPart);

        return new ParsedEmail(
            messageId: $messageId,
            inReplyTo: $inReplyTo,
            referenceIds: $referenceIds,
            fromAddress: $fromAddress,
            fromName: $fromName,
            toAddress: $toAddress,
            subject: $subject,
            textBody: $textBody,
            htmlBody: $htmlBody,
            headers: $headers,
        );
    }

    /** @return array<string,string> */
    private function parseHeaders(string $headersPart): array
    {
        $headers = [];
        $lines = explode("\r\n", $headersPart);
        $currentKey = null;
        $currentValue = '';

        foreach ($lines as $line) {
            if (preg_match('/^(\S+):\s*(.*)$/', $line, $m)) {
                if ($currentKey !== null) {
                    $headers[strtolower($currentKey)] = trim($currentValue);
                }
                $currentKey = $m[1];
                $currentValue = $m[2];
            } elseif ($line && $currentKey !== null && in_array($line[0], [' ', "\t"])) {
                $currentValue .= ' '.trim($line);
            }
        }

        if ($currentKey !== null) {
            $headers[strtolower($currentKey)] = trim($currentValue);
        }

        return $headers;
    }

    /** @return list<string> */
    private function parseReferences(string $references): array
    {
        if (! $references) {
            return [];
        }

        preg_match_all('/<([^>]+)>/', $references, $m);

        return $m[1] ?? [];
    }

    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return $m[1];
        }

        return trim($from);
    }

    private function extractName(string $from): ?string
    {
        if (preg_match('/^([^<]+)</', $from, $m)) {
            return trim($m[1], ' "');
        }

        return null;
    }

    /** @return array{string, ?string} */
    private function extractBodies(array $headers, string $bodyPart): array
    {
        $contentType = $headers['content-type'] ?? 'text/plain';

        if (str_contains($contentType, 'multipart')) {
            return $this->extractMultipartBodies($contentType, $bodyPart);
        }

        if (str_contains($contentType, 'text/html')) {
            return ['', $bodyPart];
        }

        return [$bodyPart, null];
    }

    /** @return array{string, ?string} */
    private function extractMultipartBodies(string $contentType, string $bodyPart): array
    {
        if (preg_match('/boundary="?([^";\r\n]+)"?/i', $contentType, $m)) {
            $boundary = $m[1];
            $parts = explode("--$boundary", $bodyPart);
            $textBody = '';
            $htmlBody = null;

            foreach ($parts as $part) {
                if (! trim($part) || str_starts_with(trim($part), '--')) {
                    continue;
                }

                [$partHeaders, $partBody] = explode("\r\n\r\n", $part, 2);
                $partType = '';
                foreach (explode("\r\n", $partHeaders) as $line) {
                    if (stripos($line, 'Content-Type:') === 0) {
                        $partType = $line;
                        break;
                    }
                }

                if (str_contains($partType, 'text/plain')) {
                    $textBody .= trim($partBody)."\n";
                } elseif (str_contains($partType, 'text/html')) {
                    $htmlBody = trim($partBody);
                }
            }

            return [trim($textBody), $htmlBody];
        }

        return [$bodyPart, null];
    }
}
