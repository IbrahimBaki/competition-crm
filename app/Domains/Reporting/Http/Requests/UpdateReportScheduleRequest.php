<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Requests;

use App\Domains\Reporting\Exceptions\ReportScheduleRecipientLimitException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'report_key' => ['sometimes', 'string', 'max:100'],
            'format' => ['sometimes', 'string', 'in:csv,xlsx,pdf'],
            'filters' => ['sometimes', 'array'],
            'frequency' => ['sometimes', 'string', 'in:daily,weekly,monthly'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'run_at_time' => ['sometimes', 'date_format:H:i'],
            'timezone' => ['sometimes', 'timezone'],
            'recipients' => ['sometimes', 'array', 'min:1'],
            'recipients.*' => ['string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function passedValidation(): void
    {
        if (! $this->has('recipients')) {
            return;
        }

        $max = (int) config('reporting.schedules.max_recipients', 25);
        $count = count($this->input('recipients', []));

        if ($count > $max) {
            throw ReportScheduleRecipientLimitException::for($count, $max);
        }
    }
}
