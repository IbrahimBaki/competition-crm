import { createContext, useContext, useLayoutEffect, useRef, useState, type ReactNode } from 'react';

const PortalContainerContext = createContext<HTMLElement | null>(null);
export function V2PortalBoundary({ children, dir, lang, className }: { children: ReactNode; dir?: 'ltr' | 'rtl'; lang?: string; className?: string }) {
  const ref = useRef<HTMLDivElement>(null);
  const [container, setContainer] = useState<HTMLElement | null>(null);
  useLayoutEffect(() => { setContainer(ref.current); }, []);
  return <PortalContainerContext.Provider value={container}><div ref={ref} data-ui="v2" dir={dir} lang={lang} className={className}>{children}</div></PortalContainerContext.Provider>;
}
export function useV2PortalContainer() { return useContext(PortalContainerContext) ?? undefined; }
