import React, { createContext, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

interface LocaleContextType {
  setLocale: (locale: string) => void;
}

const LocaleContext = createContext<LocaleContextType | undefined>(undefined);

export function useLocale() {
  const context = React.useContext(LocaleContext);
  if (!context) {
    throw new Error('useLocale must be used within LocaleProvider');
  }
  return context;
}

interface LocaleProviderProps {
  children: React.ReactNode;
}

export function LocaleProvider({ children }: LocaleProviderProps) {
  const { i18n } = useTranslation();

  useEffect(() => {
    const locale = localStorage.getItem('locale') ?? 'en';
    i18n.changeLanguage(locale);
    document.documentElement.lang = locale;
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
    localStorage.setItem('locale', locale);
  }, [i18n]);

  const setLocale = (locale: string) => {
    localStorage.setItem('locale', locale);
    document.documentElement.lang = locale;
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
    i18n.changeLanguage(locale);
  };

  return <LocaleContext.Provider value={{ setLocale }}>{children}</LocaleContext.Provider>;
}
