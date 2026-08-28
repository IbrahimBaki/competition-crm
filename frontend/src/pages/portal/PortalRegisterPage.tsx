import { type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

export function PortalRegisterPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    navigate('/portal/verify');
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
      <div className="w-full max-w-md space-y-8">
        <h2 className="text-3xl font-bold text-gray-900">{t('portal.register.title')}</h2>
        <form onSubmit={handleSubmit} className="space-y-6">
          <input type="text" placeholder={t('portal.register.name_label')} className="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          <input type="email" placeholder={t('portal.register.email_label')} className="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          <input type="password" placeholder={t('portal.register.password_label')} className="w-full px-3 py-2 border border-gray-300 rounded-md" required />
          <button type="submit" className="w-full px-4 py-2 bg-blue-600 text-white rounded-md">{t('portal.register.submit_button')}</button>
        </form>
      </div>
    </div>
  );
}
