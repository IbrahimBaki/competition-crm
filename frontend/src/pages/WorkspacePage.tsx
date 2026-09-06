import { useTranslation } from 'react-i18next';
import { V2PortalBoundary } from '@/design-system/foundations/V2PortalBoundary';
import { WorkspaceV2 } from '@/features/workspace/landing/WorkspaceV2';

export function WorkspacePage() {
  const { i18n } = useTranslation();
  const rtl = i18n.dir(i18n.language) === 'rtl';

  return (
    <V2PortalBoundary dir={rtl ? 'rtl' : 'ltr'} lang={rtl ? 'ar' : 'en'}>
      <WorkspaceV2 />
    </V2PortalBoundary>
  );
}
