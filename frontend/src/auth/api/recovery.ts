import { apiRequest } from '@/api/http/mutator';

/**
 * The recovery endpoints are intentionally isolated from the V2 route views.
 * They retain the existing mutator, CSRF, error, and request contracts.
 */
export function requestPasswordReset(email: string) {
  return apiRequest({ url: '/auth/password/forgot', method: 'POST', data: { email } });
}

export function resetPassword(data: { email: string; password: string; password_confirmation: string; token: string }) {
  return apiRequest({ url: '/auth/password/reset', method: 'POST', data });
}

export function acceptInvitation(token: string, data: { token: string; name: string; password: string }) {
  return apiRequest({ url: `/invitations/${token}/accept`, method: 'POST', data });
}
