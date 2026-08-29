import React from 'react';
import ReactDOM from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import { QueryClientProvider } from '@tanstack/react-query';
import { router } from './router';
import { queryClient } from './api/queryClient';
import { AuthProvider } from './auth/AuthProvider';
import { LocaleProvider } from './i18n/LocaleProvider';
import './i18n';
import './index.css';
import { ToastProvider } from './components/ui';

const root = ReactDOM.createRoot(document.getElementById('root') as HTMLElement);

root.render(
  <React.StrictMode>
    <QueryClientProvider client={queryClient}>
      <LocaleProvider>
        <AuthProvider>
          <ToastProvider>
            <RouterProvider router={router} />
          </ToastProvider>
        </AuthProvider>
      </LocaleProvider>
    </QueryClientProvider>
  </React.StrictMode>
);
