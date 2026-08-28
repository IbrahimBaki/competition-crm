<?php

namespace App\Domains\Security\Permissions;

enum Scope: string
{
    case Own = 'own';
    case Team = 'team';
    case Department = 'department';
    case Any = 'any';
}
