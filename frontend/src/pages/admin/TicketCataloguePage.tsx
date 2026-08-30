import { useTranslation } from 'react-i18next';
import { TicketStatusesPanel } from '@/features/admin/catalogue/TicketStatusesPanel';
import { TicketCategoriesPanel } from '@/features/admin/catalogue/TicketCategoriesPanel';
import { TicketPrioritiesPanel } from '@/features/admin/catalogue/TicketPrioritiesPanel';
import { useState } from 'react';
import { CollectionPage } from '@/features/operations/CollectionPage';
import { ResourceField, ResourceFormDialog } from '@/features/operations/ResourceFormDialog';
import { Button } from '@/components/ui';
import { usePermissions } from '@/auth/usePermissions';
import { PERMISSIONS } from '@/auth/permissions';

const statusFields: ResourceField[]=[{key:'name_en',label:'English name',required:true},{key:'name_ar',label:'Arabic name',required:true},{key:'lifecycle_type',label:'Lifecycle',kind:'select',required:true,options:['new','open','pending_customer','pending_third_party','resolved','closed','spam','merged'].map(value=>({value,label:value.replace(/_/g,' ')}))},{key:'position',label:'Position',kind:'number'},{key:'is_active',label:'Active',kind:'checkbox'},{key:'is_default',label:'Default status',kind:'checkbox'}];
const categoryFields: ResourceField[]=[{key:'code',label:'Code',required:true},{key:'name_en',label:'English name',required:true},{key:'name_ar',label:'Arabic name',required:true},{key:'parent_uuid',label:'Parent category UUID'},{key:'is_active',label:'Active',kind:'checkbox'}];
const localInitial=(row:Record<string,unknown>)=>{const name=row.name as Record<string,unknown>|undefined;return {...row,name_en:name?.en,name_ar:name?.ar,parent_uuid:row.parent_id};};
const localTransform=(values:Record<string,unknown>)=>{const {name_en,name_ar,...rest}=values;return {...rest,name:{en:name_en,ar:name_ar}};};

export function TicketCataloguePage() {
  const { t } = useTranslation();
  const { can }=usePermissions(); const [status,setStatus]=useState<Record<string,unknown>|null|undefined>(undefined); const [category,setCategory]=useState<Record<string,unknown>|null|undefined>(undefined);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="mb-6 text-3xl font-bold text-gray-900">{t('admin.catalogue.title')}</h1>
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.statuses.heading')}</h2>
        {can(PERMISSIONS.TICKET_STATUSES_MANAGE)?<><CollectionPage title="Ticket statuses" description="Control lifecycle labels, order, availability, and the default state." endpoint="/ticket-statuses" queryKey={['ticket-statuses']} columns={[{key:'name',label:'Status'},{key:'lifecycle_type',label:'Lifecycle'},{key:'position',label:'Position'},{key:'is_active',label:'Active'}]} actions={<Button onClick={()=>setStatus(null)}>New status</Button>} rowActions={(row)=><Button variant="secondary" onClick={()=>setStatus(row)}>Edit</Button>} deleteEndpoint={(row)=>`/ticket-statuses/${String(row.id??row.uuid)}`}/><ResourceFormDialog open={status!==undefined} title={status?'Edit ticket status':'New ticket status'} endpoint={status?`/ticket-statuses/${String(status.id??status.uuid)}`:'/ticket-statuses'} method={status?'PATCH':'POST'} queryKey={['ticket-statuses']} fields={statusFields} initial={status?localInitial(status):{position:0,is_active:true,is_default:false}} transform={localTransform} onClose={()=>setStatus(undefined)}/></>:<TicketStatusesPanel />}
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.categories.heading')}</h2>
        {can(PERMISSIONS.TICKET_CATEGORIES_MANAGE)?<><CollectionPage title="Ticket categories" description="Maintain the customer and staff classification hierarchy." endpoint="/ticket-categories" queryKey={['ticket-categories']} columns={[{key:'name',label:'Category'},{key:'code',label:'Code'},{key:'depth',label:'Depth'},{key:'is_active',label:'Active'}]} actions={<Button onClick={()=>setCategory(null)}>New category</Button>} rowActions={(row)=><Button variant="secondary" onClick={()=>setCategory(row)}>Edit</Button>} deleteEndpoint={(row)=>`/ticket-categories/${String(row.id??row.uuid)}`}/><ResourceFormDialog open={category!==undefined} title={category?'Edit ticket category':'New ticket category'} endpoint={category?`/ticket-categories/${String(category.id??category.uuid)}`:'/ticket-categories'} method={category?'PATCH':'POST'} queryKey={['ticket-categories']} fields={categoryFields} initial={category?localInitial(category):{is_active:true}} transform={localTransform} onClose={()=>setCategory(undefined)}/></>:<TicketCategoriesPanel />}
      </div>

      <div>
        <h2 className="mb-4 text-2xl font-semibold text-gray-800">{t('admin.catalogue.priorities.heading')}</h2>
        <TicketPrioritiesPanel />
      </div>
    </div>
  );
}
