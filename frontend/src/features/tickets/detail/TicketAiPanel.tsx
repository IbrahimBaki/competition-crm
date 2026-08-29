import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { Button, Card } from '@/components/ui';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';

export function TicketAiPanel({ ticketId }: { ticketId: string }) {
  const { can }=usePermissions(); const [result,setResult]=useState<unknown>(null);
  const mutation=useMutation({mutationFn:(feature:string|undefined)=>apiRequest({url:`/tickets/${ticketId}/ai/${feature ?? ''}`,method:'POST',data:{},headers:{'Idempotency-Key':crypto.randomUUID()}}),onSuccess:setResult});
  if(!can(PERMISSIONS.AI_ASSISTANCE_USE)) return null;
  return <Card className="p-4"><h2 className="text-sm font-semibold text-slate-700">AI assistance</h2><p className="mt-1 text-xs text-slate-500">Generated content requires staff review before it can be used.</p><div className="mt-3 flex flex-wrap gap-2">{[['summary','Summarize'],['suggested-reply','Suggest reply'],['classify','Classify'],['suggested-articles','Find articles']].map(([feature,label])=><Button key={feature} variant="secondary" busy={mutation.isPending&&mutation.variables===feature} onClick={()=>mutation.mutate(feature)}>{label}</Button>)}</div>{mutation.isError&&<p className="ui-alert ui-alert--danger mt-3" role="alert">AI assistance is unavailable. Try again later.</p>}{result!==null&&<pre className="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-xs">{JSON.stringify(result,null,2)}</pre>}</Card>;
}
