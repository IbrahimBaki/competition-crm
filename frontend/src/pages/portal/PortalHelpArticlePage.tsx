import { useTranslation } from 'react-i18next';
import { useParams, Link } from 'react-router-dom';
import { useMutation, useQuery } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { PortalAsyncBoundary as AsyncBoundary } from '@/portal/components/PortalStates';
import { Button, Card, PageHeader } from '@/components/ui';

interface Article { id: string; title: string | { en?: string; ar?: string }; body: string | { en?: string; ar?: string } }
export function PortalHelpArticlePage() {
  const { t } = useTranslation();
  const { slug = '' } = useParams();
  const query = useQuery({ queryKey: ['public', 'knowledge', 'article', slug], queryFn: () => apiRequest<Article>({ url: `/public/knowledge/articles/${slug}`, method: 'GET' }), enabled: Boolean(slug) });
  const feedback=useMutation({mutationFn:(isHelpful:boolean)=>apiRequest({url:`/public/knowledge/articles/${slug}/feedback`,method:'POST',data:{is_helpful:isHelpful,visitor_key:localStorage.getItem('knowledge-visitor')??undefined},headers:{'Idempotency-Key':crypto.randomUUID()}})});
  const localise = (value: Article['title']) => typeof value === 'string' ? value : value[localStorage.getItem('locale') === 'ar' ? 'ar' : 'en'] ?? value.en ?? value.ar ?? '';
  return <div className="max-w-4xl mx-auto px-4 py-8"><Link to="/portal/help" className="mb-5 inline-block text-sm font-semibold text-blue-700">← {t('portal.help_article.back_to_help')}</Link><AsyncBoundary query={query}>{(article) => <><PageHeader title={localise(article.title)}/><Card className="p-6 sm:p-8"><div className="whitespace-pre-wrap leading-7 text-slate-700">{localise(article.body)}</div></Card><Card className="mt-5 p-5"><h2 className="font-semibold">Was this article helpful?</h2>{feedback.isSuccess?<p className="mt-3 text-sm text-emerald-700" role="status">Thank you for your feedback.</p>:<div className="mt-3 flex gap-2"><Button variant="secondary" busy={feedback.isPending} onClick={()=>feedback.mutate(true)}>Yes</Button><Button variant="secondary" busy={feedback.isPending} onClick={()=>feedback.mutate(false)}>No</Button></div>}{feedback.isError&&<p className="ui-alert ui-alert--danger mt-3" role="alert">Feedback could not be submitted.</p>}</Card></>}</AsyncBoundary></div>;
}
