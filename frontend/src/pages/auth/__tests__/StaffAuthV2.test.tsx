import { MemoryRouter } from 'react-router-dom';
import type { ReactNode } from 'react';
import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { configureAxe } from 'vitest-axe';
import { AxiosError } from 'axios';
import { afterEach, describe, expect, it, vi } from 'vitest';
import i18n from '@/i18n';
import { LoginPage } from '../../LoginPage';
import { TwoFactorPage } from '../../TwoFactorPage';

const mocks = vi.hoisted(() => ({ login: vi.fn(), completeTwoFactor: vi.fn() }));
vi.mock('@/auth/AuthProvider', () => ({ useAuth: () => ({ login: mocks.login, completeTwoFactor: mocks.completeTwoFactor }) }));
const axe = configureAxe({ rules: { 'color-contrast': { enabled: false } } });
function rejectedAuthError(message: string) { return new AxiosError(message, undefined, undefined, undefined, { status: 422, data: { error: { message } } } as never); }

function renderRoute(node: ReactNode, path = '/login') {
  return render(<MemoryRouter initialEntries={[path]}>{node}</MemoryRouter>);
}

afterEach(async () => {
  vi.clearAllMocks();
  await i18n.changeLanguage('en');
});

describe('Staff authentication V2', () => {
  it('renders an accessible labelled login form in the scoped V2 boundary', async () => {
    const { container } = renderRoute(<LoginPage />);
    expect(screen.getByRole('heading', { name: 'Sign In' })).toBeVisible();
    expect(screen.getByRole('textbox', { name: /Email/ })).toHaveAttribute('autocomplete', 'username');
    expect(screen.getByLabelText(/Password/)).toHaveAttribute('autocomplete', 'current-password');
    expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute('dir', 'ltr');
    expect((await axe(container)).violations).toEqual([]);
  });

  it('submits credentials, shows a stable loading action, and focuses a returned error', async () => {
    const user = userEvent.setup();
    let settle: () => void = () => undefined;
    mocks.login.mockReturnValue(new Promise<void>((resolve) => { settle = resolve; }));
    renderRoute(<LoginPage />);
    await user.type(screen.getByRole('textbox', { name: /Email/ }), 'staff@example.test');
    await user.type(screen.getByLabelText(/Password/), 'correct horse battery staple');
    await user.click(screen.getByRole('button', { name: 'Sign In' }));
    expect(mocks.login).toHaveBeenCalledWith('staff@example.test', 'correct horse battery staple');
    expect(screen.getByRole('button', { name: 'Sign In' })).toHaveAttribute('aria-busy', 'true');
    await act(async () => { settle(); });
    await waitFor(() => expect(screen.getByRole('button', { name: 'Sign In' })).not.toHaveAttribute('aria-busy'));

    mocks.login.mockRejectedValueOnce(rejectedAuthError('Invalid credentials'));
    await user.click(screen.getByRole('button', { name: 'Sign In' }));
    const alert = await screen.findByRole('alert');
    expect(alert).toHaveTextContent('Invalid credentials');
    expect(alert).toHaveFocus();
  });

  it('authors the login boundary and LTR credential inputs correctly in Arabic', async () => {
    await i18n.changeLanguage('ar');
    const { container } = renderRoute(<LoginPage />);
    expect(container.querySelector('[data-ui="v2"]')).toHaveAttribute('dir', 'rtl');
    expect(screen.getByRole('heading', { name: 'تسجيل الدخول' })).toBeVisible();
    expect(screen.getByRole('textbox', { name: /البريد الإلكتروني/ })).toHaveAttribute('dir', 'ltr');
    expect((await axe(container)).violations).toEqual([]);
  });

  it('submits the existing 2FA challenge with one-time-code semantics and exposes errors', async () => {
    const user = userEvent.setup();
    mocks.completeTwoFactor.mockRejectedValueOnce(rejectedAuthError('Incorrect authentication code'));
    const { container } = renderRoute(<TwoFactorPage />, '/login/two-factor');
    const code = screen.getByRole('textbox', { name: /Authentication Code/ });
    expect(code).toHaveAttribute('autocomplete', 'one-time-code');
    await user.type(code, '123456');
    await user.keyboard('{Enter}');
    expect(mocks.completeTwoFactor).toHaveBeenCalledWith('123456');
    expect(await screen.findByRole('alert')).toHaveTextContent('Incorrect authentication code');
    expect((await axe(container)).violations).toEqual([]);
  });
});
