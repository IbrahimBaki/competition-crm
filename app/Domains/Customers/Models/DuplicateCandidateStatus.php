<?php

namespace App\Domains\Customers\Models;

enum DuplicateCandidateStatus: string
{
    case Pending = 'pending';
    case Merged = 'merged';
    case Dismissed = 'dismissed';
}
