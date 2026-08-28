import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

export function PortalVerifyPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="text-center">
        <p className="text-lg">{t('portal.verify.check_email')}</p>
        <button onClick={() => navigate('/portal/login')} className="mt-4 text-blue-600">Back to login</button>
      </div>
    </div>
  );
}
