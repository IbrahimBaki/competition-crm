import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

// `Intl.supportedValuesOf` is missing in some browser/test environments
// (notably older Safari and jsdom). This fallback list covers the timezones
// relevant to this product's operating regions plus UTC; StoreBranchRequest
// only validates `timezone` against PHP's timezone database, so any IANA
// name works — this list is a usability aid, not a validation constraint.
const FALLBACK_TIMEZONES = [
  'UTC',
  'Africa/Cairo',
  'Asia/Riyadh',
  'Asia/Dubai',
  'Asia/Amman',
  'Asia/Beirut',
  'Asia/Baghdad',
  'Africa/Casablanca',
  'Africa/Tunis',
  'Europe/London',
  'Europe/Istanbul',
];

function listTimezones(): string[] {
  const supportedValuesOf = (Intl as { supportedValuesOf?: (key: string) => string[] })
    .supportedValuesOf;
  if (typeof supportedValuesOf === 'function') {
    try {
      return supportedValuesOf('timeZone');
    } catch {
      return FALLBACK_TIMEZONES;
    }
  }
  return FALLBACK_TIMEZONES;
}

interface BranchTimezoneFieldProps {
  id: string;
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
}

export function BranchTimezoneField({ id, value, onChange, required }: BranchTimezoneFieldProps) {
  const { t } = useTranslation();
  const timezones = useMemo(listTimezones, []);

  return (
    <div>
      <label htmlFor={id} className="mb-1 block text-xs font-medium text-gray-600">
        {t('admin.organisation.branch.timezone_label')}
      </label>
      <select
        id={id}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        required={required}
        className="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
      >
        <option value="" disabled>
          {t('admin.organisation.branch.timezone_placeholder')}
        </option>
        {timezones.map((timezone) => (
          <option key={timezone} value={timezone}>
            {timezone}
          </option>
        ))}
      </select>
    </div>
  );
}
