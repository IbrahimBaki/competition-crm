import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import { apiRequest } from '@/api/http/mutator';
import type { ApiPage } from '@/api/http/envelope';
import { PortalAsyncBoundary as AsyncBoundary } from '@/portal/components/PortalStates';
import { Card, Input, PageHeader } from '@/components/ui';

interface Article { id: string; slug?: string; title: string | { en?: string; ar?: string }; body?: string }
export function PortalHelpPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const query = useQuery({ queryKey: ['public', 'knowledge', search], queryFn: () => apiRequest<ApiPage<Article>>({ url: search.trim() ? '/public/knowledge/articles/search' : '/public/knowledge/articles', method: 'GET', params: search.trim() ? { q: search.trim(), per_page: 50 } : { per_page: 50 } }) });
  const label = (value: Article['title']) => typeof value === 'string' ? value : value[localStorage.getItem('locale') === 'ar' ? 'ar' : 'en'] ?? value.en ?? value.ar ?? 'Untitled article';
  return <div className="max-w-6xl mx-auto px-4 py-8"><PageHeader title={t('portal.help.title')} description="Find answers, guides, and troubleshooting steps."/><label className="sr-only" htmlFor="help-search">Search help articles</label><Input id="help-search" type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search the help center" className="mb-6 max-w-2xl"/><AsyncBoundary query={query}>{(data) => <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">{data.items.map((article) => <button key={article.id} onClick={() => navigate(`/portal/help/${article.slug ?? article.id}`)} className="text-start"><Card className="h-full p-5 transition hover:border-blue-300"><h2 className="font-semibold text-slate-900">{label(article.title)}</h2><p className="mt-2 text-sm text-slate-600">Open article</p></Card></button>)}</div>}</AsyncBoundary></div>;
}
