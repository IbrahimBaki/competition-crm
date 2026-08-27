<?php

namespace App\Domains\Channels\Email\Services\Parsing;

class QuotedTextStripper
{
    public function strip(string $body): string
    {
        $original = trim($body);

        if (! $original) {
            return $original;
        }

        $stripped = $this->stripSignature($original);
        $stripped = $this->stripQuotedBlocks($stripped);
        $stripped = $this->stripReplyHeaders($stripped);
        $stripped = $this->stripQuotedLines($stripped);

        $result = trim($stripped);

        return $result === '' ? $original : $result;
    }

    private function stripSignature(string $body): string
    {
        if (preg_match('/^(.+?)\n-- \n/ms', $body, $m)) {
            return $m[1];
        }

        return $body;
    }

    private function stripQuotedBlocks(string $body): string
    {
        if (preg_match('/^(.+?)\n-----Original Message-----/ms', $body, $m)) {
            return $m[1];
        }

        return $body;
    }

    private function stripReplyHeaders(string $body): string
    {
        $patterns = [
            '/^On .+?, .+ wrote:\s*\n/im',
            '/^في .+?، .+ كتب:\s*\n/im',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body)) {
                return trim(preg_replace($pattern, '', $body, 1));
            }
        }

        return $body;
    }

    private function stripQuotedLines(string $body): string
    {
        $lines = explode("\n", $body);
        $result = [];
        $inQuote = false;
        $quoteCount = 0;
        $totalQuoteLines = 0;

        foreach ($lines as $line) {
            if (mb_substr(ltrim($line), 0, 1, 'UTF-8') === '>') {
                $inQuote = true;
                $quoteCount++;
                $totalQuoteLines++;

                continue;
            }

            if ($inQuote && trim($line) === '') {
                $inQuote = false;

                continue;
            }

            $result[] = $line;
        }

        $output = trim(implode("\n", $result));

        return $output === '' ? $body : $output;
    }
}
