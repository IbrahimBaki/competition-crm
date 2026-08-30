import { useQuery } from '@tanstack/react-query';
import { getGetQuickRepliesQueryKey } from '@/api/generated/workspace/workspace';
import { fetchQuickReplies } from '../api/wire';

export function useQuickRepliesQuery(perPage = 50) {
  return useQuery({
    queryKey: [...getGetQuickRepliesQueryKey(), perPage],
    queryFn: () => fetchQuickReplies({ perPage }),
  });
}
