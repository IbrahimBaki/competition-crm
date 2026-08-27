import axios from 'axios';

export const httpClient = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL}${import.meta.env.VITE_API_PREFIX}`,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
});
