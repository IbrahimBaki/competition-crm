<?php

namespace App\Domains\Automation\Models;

enum RuleActionType: string
{
    case Assign = 'assign';
    case Reassign = 'reassign';
    case TransferDepartment = 'transfer_department';
    case RaisePriority = 'raise_priority';
    case ChangeStatus = 'change_status';
    case AddTag = 'add_tag';
    case Notify = 'notify';
    case Escalate = 'escalate';
}
