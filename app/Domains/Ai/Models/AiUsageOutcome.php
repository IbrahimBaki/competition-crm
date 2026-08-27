<?php

namespace App\Domains\Ai\Models;

enum AiUsageOutcome: string
{
    case Success = 'success';
    case ProviderUnavailable = 'provider_unavailable';
    case BudgetBlocked = 'budget_blocked';
    case FeatureDisabled = 'feature_disabled';
}
