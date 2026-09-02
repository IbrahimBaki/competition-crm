import { useState } from 'react';
import { createRoot } from 'react-dom/client';
import '../../index.css';
import '../foundations/v2-global.css';
import { V2PortalBoundary } from '../foundations/V2PortalBoundary';
import { Button } from '../primitives/Button';
import { DesignSystemShowcase } from './DesignSystemShowcase';

function ShowcaseEntry() {
  const [rtl, setRtl] = useState(false);
  return <V2PortalBoundary dir={rtl ? 'rtl' : 'ltr'} lang={rtl ? 'ar' : 'en'}><div style={{ padding: '8px', background: 'var(--ds-surface-canvas)' }}><Button variant="ghost" onClick={() => setRtl((value) => !value)}>{rtl ? 'English LTR' : 'العربية RTL'}</Button></div><DesignSystemShowcase /></V2PortalBoundary>;
}
createRoot(document.getElementById('root')!).render(<ShowcaseEntry />);
