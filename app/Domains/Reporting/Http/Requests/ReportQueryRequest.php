<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Requests;

use App\Domains\Organisation\Models\Branch;
use App\Domains\Organisation\Models\Department;
use App\Domains\Organisation\Models\Team;
use App\Domains\Reporting\Exceptions\ReportRangeTooLargeException;
use App\Domains\Reporting\Services\Filters\ReportFilter;
use App\Domains\Ticketing\Models\TicketCategory;
use App\Domains\Ticketing\Models\TicketTag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class ReportQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'timezone' => ['nullable', 'timezone'],
            'branch' => ['nullable', 'uuid', 'exists:branches,uuid'],
            'department' => ['nullable', 'uuid', 'exists:departments,uuid'],
            'team' => ['nullable', 'uuid', 'exists:teams,uuid'],
            'agent' => ['nullable', 'uuid', 'exists:users,uuid'],
            'category' => ['nullable', 'uuid', 'exists:ticket_categories,uuid'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'channel' => ['nullable', 'string', 'in:email,chat,phone,portal'],
            'tag' => ['nullable', 'uuid', 'exists:ticket_tags,uuid'],
        ];
    }

    public function toFilter(): ReportFilter
    {
        $timezone = $this->input('timezone') ?? config('app.timezone');

        $from = CarbonImmutable::createFromFormat('Y-m-d', $this->input('date_from'), $timezone)
            ->startOfDay()
            ->setTimezone('UTC');

        $to = CarbonImmutable::createFromFormat('Y-m-d', $this->input('date_to'), $timezone)
            ->endOfDay()
            ->setTimezone('UTC');

        $rangeDays = $to->diffInDays($from);
        if ($rangeDays > config('reporting.max_range_days')) {
            throw ReportRangeTooLargeException::for(config('reporting.max_range_days'));
        }

        return new ReportFilter(
            from: $from,
            to: $to,
            timezone: $timezone,
            branchId: $this->resolveBranchId(),
            departmentId: $this->resolveDepartmentId(),
            teamId: $this->resolveTeamId(),
            agentId: $this->resolveAgentId(),
            categoryId: $this->resolveCategoryId(),
            priority: $this->input('priority'),
            channel: $this->input('channel'),
            tagId: $this->resolveTagId(),
        );
    }

    private function resolveBranchId(): ?int
    {
        if (! $uuid = $this->input('branch')) {
            return null;
        }

        return Branch::where('uuid', $uuid)->value('id');
    }

    private function resolveDepartmentId(): ?int
    {
        if (! $uuid = $this->input('department')) {
            return null;
        }

        return Department::where('uuid', $uuid)->value('id');
    }

    private function resolveTeamId(): ?int
    {
        if (! $uuid = $this->input('team')) {
            return null;
        }

        return Team::where('uuid', $uuid)->value('id');
    }

    private function resolveAgentId(): ?int
    {
        if (! $uuid = $this->input('agent')) {
            return null;
        }

        return User::where('uuid', $uuid)->value('id');
    }

    private function resolveCategoryId(): ?int
    {
        if (! $uuid = $this->input('category')) {
            return null;
        }

        return TicketCategory::where('uuid', $uuid)->value('id');
    }

    private function resolveTagId(): ?int
    {
        if (! $uuid = $this->input('tag')) {
            return null;
        }

        return TicketTag::where('uuid', $uuid)->value('id');
    }
}
