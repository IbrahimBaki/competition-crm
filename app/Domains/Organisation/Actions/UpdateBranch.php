<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateBranch
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Branch $branch, array $data, ?Authenticatable $actor = null): Branch
    {
        return \DB::transaction(function () use ($branch, $data, $actor) {
            $before = $branch->toArray();

            $branch->update([
                'name' => $data['name'] ?? $branch->name,
                'code' => $data['code'] ?? $branch->code,
                'timezone' => $data['timezone'] ?? $branch->timezone,
                'is_24_7' => $data['is_24_7'] ?? $branch->is_24_7,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.branch.updated',
                $branch->refresh(),
                $before,
                $branch->toArray()
            );

            return $branch;
        });
    }
}
