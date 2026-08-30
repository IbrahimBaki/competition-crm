import { useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import type { ReportQuery } from '../types';

/**
 * Allowed report filter keys that map to ReportQueryRequest::rules().
 * Any other key sent to the backend will be rejected with a 422.
 */
export const REPORT_FILTER_KEYS = [
  'date_from',
  'date_to',
  'timezone',
  'branch',
  'department',
  'team',
  'agent',
  'category',
  'priority',
  'channel',
  'tag',
] as const;

export type ReportFilterKey = (typeof REPORT_FILTER_KEYS)[number];

export interface ReportFilterState {
  filters: Partial<ReportQuery>;
}

/**
 * Parse report filter state from URL search params.
 */
function parseState(searchParams: URLSearchParams): ReportFilterState {
  const filters: Record<string, string> = {};

  for (const key of REPORT_FILTER_KEYS) {
    const value = searchParams.get(key);
    if (value) {
      filters[key] = value;
    }
  }

  return { filters: filters as Partial<ReportQuery> };
}

/**
 * Serialize report filter state to URL search params.
 */
function serializeState(state: ReportFilterState): URLSearchParams {
  const params = new URLSearchParams();

  for (const [key, value] of Object.entries(state.filters)) {
    if (value) {
      params.set(key, String(value));
    }
  }

  return params;
}

/**
 * Build the query object to send to the backend.
 * Ensures date_from and date_to are present and formatted as Y-m-d.
 */
export function buildReportQueryParams(state: ReportFilterState): Partial<ReportQuery> {
  return { ...state.filters };
}

/**
 * Hook to manage report filter state in the URL.
 * Supports get/set operations that persist to and restore from query params.
 */
export function useReportFilters() {
  const [searchParams, setSearchParams] = useSearchParams();
  const state = useMemo(
    () => parseState(searchParams),
    [searchParams]
  );

  const setState = (updates: Partial<ReportQuery>) => {
    const next: ReportFilterState = {
      filters: { ...state.filters, ...updates },
    };
    setSearchParams(serializeState(next));
  };

  const setFilter = (key: ReportFilterKey, value: string | undefined) => {
    const filters = { ...state.filters } as Record<string, string>;
    if (value === undefined) {
      delete filters[key];
    } else {
      filters[key] = value;
    }
    setState(filters as Partial<ReportQuery>);
  };

  const reset = () => {
    setSearchParams(new URLSearchParams());
  };

  /**
   * Check if required filters (date_from, date_to) are populated.
   */
  const isComplete = (): boolean => {
    return !!(
      state.filters.date_from &&
      state.filters.date_to
    );
  };

  return {
    values: state.filters,
    setValue: setState,
    setFilter,
    reset,
    isComplete,
  };
}
