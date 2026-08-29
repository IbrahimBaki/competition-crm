import { useQuery, useQueryClient } from '@tanstack/react-query';
import { apiRequest } from '@/api/http/mutator';
import { Button, Card, useToast } from '@/components/ui';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';
import { AsyncBoundary } from '@/shell/AsyncBoundary';

interface Version { id: string; version: number; published_at?: string }

export function KnowledgeLifecyclePanel({ articleId, state }: { articleId:string; state?:string }) {
  const { can }=usePermissions(); const client=useQueryClient(); const { notify }=useToast();
  const versions=useQuery({queryKey:['knowledge',articleId,'versions'],queryFn:()=>apiRequest<Version[]>({url:`/knowledge/articles/${articleId}/versions`,method:'GET'})});
  const act=async(next:string)=>{try{await apiRequest({url:`/knowledge/articles/${articleId}/state`,method:'POST',data:{state:next},headers:{'Idempotency-Key':crypto.randomUUID()}});await client.invalidateQueries({queryKey:['knowledge',articleId]});notify(`Article moved to ${next.replace('_',' ')}.`);}catch{notify('The article state could not be changed.','danger');}};
  const restore=async(version:number)=>{try{await apiRequest({url:`/knowledge/articles/${articleId}/versions/${version}/restore`,method:'POST',headers:{'Idempotency-Key':crypto.randomUUID()}});await client.invalidateQueries({queryKey:['knowledge',articleId]});notify(`Version ${version} restored.`);}catch{notify('The version could not be restored.','danger');}};
  return <Card className="p-5"><h2 className="text-lg font-semibold">Lifecycle and versions</h2><p className="mt-1 text-sm text-slate-500">Current state: {state??'draft'}</p><div className="mt-4 flex flex-wrap gap-2">{can(PERMISSIONS.KNOWLEDGE_ARTICLES_UPDATE)&&state==='draft'&&<Button onClick={()=>void act('in_review')}>Send for review</Button>}{can(PERMISSIONS.KNOWLEDGE_ARTICLES_PUBLISH)&&state==='in_review'&&<Button onClick={()=>void act('published')}>Publish</Button>}{can(PERMISSIONS.KNOWLEDGE_ARTICLES_ARCHIVE)&&state!=='archived'&&<Button variant="danger" onClick={()=>void act('archived')}>Archive</Button>}</div><div className="mt-5"><AsyncBoundary query={versions}>{(items)=><ul className="divide-y divide-slate-100">{items.length===0&&<li className="py-3 text-sm text-slate-500">No saved versions yet.</li>}{items.map((version)=><li key={version.id} className="flex items-center justify-between gap-3 py-3"><span className="text-sm">Version {version.version}{version.published_at?` · ${new Date(version.published_at).toLocaleString()}`:''}</span>{can(PERMISSIONS.KNOWLEDGE_ARTICLES_VERSIONS_RESTORE)&&<Button variant="secondary" onClick={()=>void restore(version.version)}>Restore</Button>}</li>)}</ul>}</AsyncBoundary></div></Card>;
}
