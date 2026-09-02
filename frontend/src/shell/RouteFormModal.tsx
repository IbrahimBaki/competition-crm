import type { ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';
import { Dialog } from '@/components/ui';

interface RouteFormModalProps {
  title: string;
  fallback: string;
  children: ReactNode;
}

/** Keeps direct create/edit URLs shareable while presenting the form as a modal. */
export function RouteFormModal({ title, fallback, children }: RouteFormModalProps) {
  const navigate = useNavigate();
  return <Dialog open title={title} onClose={() => navigate(fallback)} className="ui-dialog--form-route">{children}</Dialog>;
}
