<?php

namespace App\Domains\Knowledge\Models;

enum ArticleState: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Archived = 'archived';
}
