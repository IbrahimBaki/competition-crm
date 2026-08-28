<?php

namespace App\Domains\Organisation\Actions;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\BranchWorkingHour;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;

class ReplaceBranchWorkingHours
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Branch $branch, array $rows, ?Authenticatable $actor = null): Collection
    {
        return \DB::transaction(function () use ($branch, $rows, $actor) {
            $before = $branch->workingHours()->get()->map(fn ($h) => $h->toArray())->toArray();

            // Replace all rows
            BranchWorkingHour::where('branch_id', $branch->id)->delete();

            // Ensure all 7 days exist
            for ($i = 0; $i < 7; $i++) {
                $row = collect($rows)->firstWhere('day_of_week', $i);

                if ($row) {
                    BranchWorkingHour::create([
                        'branch_id' => $branch->id,
                        'day_of_week' => $i,
                        'is_working' => $row['is_working'] ?? false,
                        'opens_at' => $row['opens_at'] ?? null,
                        'closes_at' => $row['closes_at'] ?? null,
                    ]);
                } else {
                    BranchWorkingHour::create([
                        'branch_id' => $branch->id,
                        'day_of_week' => $i,
                        'is_working' => false,
                        'opens_at' => null,
                        'closes_at' => null,
                    ]);
                }
            }

            $after = $branch->workingHours()->get()->map(fn ($h) => $h->toArray())->toArray();

            $this->auditLogger->record(
                $actor,
                'organisation.branch.working_hours.updated',
                $branch,
                ['working_hours' => $before],
                ['working_hours' => $after]
            );

            return $branch->workingHours()->get();
        });
    }
}
