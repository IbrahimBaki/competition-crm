import { useState } from 'react';
import { CollectionPage } from '@/features/operations/CollectionPage';
import { ResourceField, ResourceFormDialog } from '@/features/operations/ResourceFormDialog';
import { Button } from '@/components/ui';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';

const fields: ResourceField[] = [
  { key: 'key', label: 'Rule key', required: true, placeholder: 'route-urgent-tickets' },
  { key: 'name_en', label: 'English name', required: true }, { key: 'name_ar', label: 'Arabic name', required: true },
  { key: 'trigger', label: 'Trigger', kind: 'select', required: true, options: ['ticket_created','ticket_updated','status_changed','priority_changed','department_transferred','message_posted','sla_warning_raised','sla_breached','scheduled','manual_escalation'].map((value) => ({value,label:value.replace(/_/g,' ')})) },
  { key: 'priority', label: 'Execution priority', kind: 'number' }, { key: 'conditions', label: 'Conditions (JSON array)', kind: 'json' },
  { key: 'actions', label: 'Actions (JSON array)', kind: 'json', required: true }, { key: 'cooldown_minutes', label: 'Cooldown minutes', kind: 'number' },
  { key: 'stop_on_match', label: 'Stop after this rule matches', kind: 'checkbox' }, { key: 'is_active', label: 'Active', kind: 'checkbox' },
];
const transform = (values: Record<string, unknown>) => { const {name_en,name_ar,...rest}=values; return {...rest,name:{en:name_en,ar:name_ar}}; };
const initial = (row: Record<string, unknown>) => { const name=row.name as Record<string,unknown>|undefined; return {...row,name_en:name?.en,name_ar:name?.ar}; };

export function AutomationRulesPage() {
  const { can } = usePermissions(); const [section,setSection]=useState<'rules'|'executions'>('rules'); const [editing,setEditing]=useState<Record<string,unknown>|null|undefined>(undefined); const manage=can(PERMISSIONS.AUTOMATION_RULES_MANAGE);
  return <div><div className="mb-5 flex gap-2" role="tablist" aria-label="Automation"><Button role="tab" aria-selected={section==='rules'} variant={section==='rules'?'primary':'secondary'} onClick={()=>setSection('rules')}>Rules</Button>{can(PERMISSIONS.AUTOMATION_EXECUTIONS_VIEW)&&<Button role="tab" aria-selected={section==='executions'} variant={section==='executions'?'primary':'secondary'} onClick={()=>setSection('executions')}>Execution history</Button>}</div>{section==='rules'?<><CollectionPage title="Automation rules" description="Create, order, activate, and maintain routing and lifecycle rules." endpoint="/automation/rules" queryKey={['automation-rules']} columns={[{key:'name',label:'Rule'},{key:'trigger',label:'Trigger'},{key:'priority',label:'Priority'},{key:'is_active',label:'State'}]} actions={manage?<Button onClick={()=>setEditing(null)}>New rule</Button>:undefined} rowActions={manage?(row)=><Button variant="secondary" onClick={()=>setEditing(row)}>Edit</Button>:undefined} deleteEndpoint={manage?(row)=>`/automation/rules/${String(row.uuid)}`:undefined}/><ResourceFormDialog open={editing!==undefined} title={editing?'Edit automation rule':'New automation rule'} endpoint={editing?`/automation/rules/${String(editing.uuid)}`:'/automation/rules'} method={editing?'PATCH':'POST'} queryKey={['automation-rules']} fields={fields} initial={editing?initial(editing):{priority:100,conditions:[],actions:[],is_active:true,stop_on_match:false}} transform={transform} onClose={()=>setEditing(undefined)}/></>:<CollectionPage title="Automation executions" description="Audit rule outcomes, affected tickets, changes, and failure reasons." endpoint="/automation/executions" queryKey={['automation-executions']} columns={[{key:'executed_at',label:'Executed'},{key:'trigger',label:'Trigger'},{key:'outcome',label:'Outcome'},{key:'ticket_id',label:'Ticket'},{key:'reason',label:'Reason'}]}/>}</div>;
}
