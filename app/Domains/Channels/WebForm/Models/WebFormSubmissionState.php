<?php

namespace App\Domains\Channels\WebForm\Models;

enum WebFormSubmissionState: string
{
    case Accepted = 'accepted';
    case Duplicate = 'duplicate';
    case Rejected = 'rejected';
    case Failed = 'failed';
}
