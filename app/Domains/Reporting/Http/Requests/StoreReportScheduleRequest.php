<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Requests;

use App\Domains\Reporting\Exceptions\ReportScheduleRecipientLimitException;
use App\Domains\Reporting\Models\ReportSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreReportScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'report_key' => ['required', 'string', 'max:100'],
            'format' => ['required', 'string', 'in:csv,xlsx,pdf'],
            'filters' => ['required', 'array'],
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6', 'required_if:frequency,weekly'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31', 'required_if:frequency,monthly'],
            'run_at_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'timezone'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The recipient cap is a business rule with its own error code, so it is
     * enforced here rather than as a plain `max:` validation message.
     */
    protected function passedValidation(): void
    {
        $max = (int) config('reporting.schedules.max_recipients', 25);
        $count = count($this->input('recipients', []));

        if ($count > $max) {
            throw ReportScheduleRecipientLimitException::for($count, $max);
        }
    }

    /**
     * Server-owned columns are appended here so the controller can hand the
     * result straight to ReportSchedule::create().
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return array_merge($data, [
            'uuid' => (string) Str::uuid(),
            'created_by_user_id' => $this->user()?->id,
            'next_run_at' => ReportSchedule::nextRunFor(
                $data['frequency'],
                $data['run_at_time'],
                $data['timezone'],
            ),
        ]);
    }
}
