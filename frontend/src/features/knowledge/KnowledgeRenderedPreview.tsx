import { useQuery } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { AsyncBoundary } from '@/shell/AsyncBoundary';
import { Card } from '@/components/ui';

export function KnowledgeRenderedPreview({ articleId, state }: { articleId:string; state:string }) {
  const query=useQuery({queryKey:['knowledge',articleId,'render'],queryFn:({signal})=>apiRequest<Record<string,string>>({url:'/knowledge/articles/render',method:'POST',data:{article_id:articleId},headers:{'Idempotency-Key':crypto.randomUUID()},signal}),enabled:state==='published'});
  if(state!=='published') return <Card className="p-5"><h2 className="text-lg font-semibold">Rendered reply preview</h2><p className="mt-2 text-sm text-slate-500">Publish this article to preview the customer-ready reply.</p></Card>;
  return <Card className="p-5"><h2 className="text-lg font-semibold">Rendered reply preview</h2><AsyncBoundary query={query}>{content=><div className="mt-4 grid gap-4 lg:grid-cols-2"><section><h3 className="text-xs font-semibold uppercase text-slate-500">English</h3><p className="mt-2 whitespace-pre-wrap text-sm leading-6">{content.en}</p></section><section dir="rtl"><h3 className="text-xs font-semibold uppercase text-slate-500">العربية</h3><p className="mt-2 whitespace-pre-wrap text-sm leading-6">{content.ar}</p></section></div>}</AsyncBoundary></Card>;
}
