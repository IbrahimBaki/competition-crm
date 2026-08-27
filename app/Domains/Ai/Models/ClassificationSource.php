<?php

namespace App\Domains\Ai\Models;

enum ClassificationSource: string
{
    case Human = 'human';
    case Ai = 'ai';
}
