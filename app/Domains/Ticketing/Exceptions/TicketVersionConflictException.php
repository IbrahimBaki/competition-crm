<?php

namespace App\Domains\Ticketing\Exceptions;

use App\Support\Http\Errors\ErrorCode;
use App\Support\Http\Errors\HasApiErrorCode;
use Exception;

class TicketVersionConflictException extends Exception implements HasApiErrorCode
{
    public function __construct(
        private readonly int $currentVersion,
        private readonly ?string $currentAssigneeUuid,
        private readonly string $currentDepartmentUuid,
    ) {
        parent::__construct('Version conflict: expected version does not match current');
    }

    public function errorCode(): ErrorCode
    {
        return ErrorCode::TicketVersionConflict;
    }

    public function errorMeta(): array
    {
        return [
            'current_version' => $this->currentVersion,
            'current_assignee_uuid' => $this->currentAssigneeUuid,
            'current_department_uuid' => $this->currentDepartmentUuid,
        ];
    }

    public function getCurrentVersion(): int
    {
        return $this->currentVersion;
    }

    public function getCurrentAssigneeUuid(): ?string
    {
        return $this->currentAssigneeUuid;
    }

    public function getCurrentDepartmentUuid(): string
    {
        return $this->currentDepartmentUuid;
    }
}
