import { useTranslation } from 'react-i18next';
export function PortalHelpArticlePage() {
  const { t } = useTranslation();
  return <div className="max-w-4xl mx-auto px-4 py-8"><h1>{t('portal.help_article.back_to_help')}</h1></div>;
}
