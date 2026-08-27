import { httpClient } from './client';

let inFlight: Promise<void> | null = null;

export async function ensureCsrfCookie(): Promise<void> {
  inFlight ??= httpClient
    .get('/sanctum/csrf-cookie', {
      baseURL: import.meta.env.VITE_API_BASE_URL,
    })
    .then(() => undefined)
    .finally(() => {
      inFlight = null;
    });
  return inFlight;
}
