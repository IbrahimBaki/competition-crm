<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class CreateBranch
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, ?Authenticatable $actor = null): Branch
    {
        return \DB::transaction(function () use ($data, $actor) {
            $branch = Branch::create([
                'id' => Str::uuid(),
                'name' => $data['name'],
                'code' => $data['code'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'is_24_7' => $data['is_24_7'] ?? false,
                'is_active' => true,
            ]);

            $this->auditLogger->record(
                $actor,
                'organisation.branch.created',
                $branch,
                null,
                $branch->toArray()
            );

            return $branch;
        });
    }
}
