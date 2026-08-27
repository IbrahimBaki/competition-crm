<?php

namespace App\Domains\Ai\Services\Provider;

interface AiProvider
{
    public function complete(AiCompletionRequest $request): AiCompletionResult;
}
