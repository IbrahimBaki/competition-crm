import { defineConfig } from 'orval';

export default defineConfig({
  supportCrm: {
    input: {
      target: '../docs/api/openapi.yaml',
    },
    output: {
      mode: 'tags-split',
      target: './src/api/generated/endpoints.ts',
      schemas: './src/api/generated/model',
      client: 'react-query',
      clean: true,
      prettier: false,
      filterTags: ['Organization', 'Security', 'Customers', 'Ticketing', 'Attachments', 'Workspace', 'Knowledge', 'Automation', 'Notifications', 'Integrations', 'Reporting', 'Channels'],
      override: {
        mutator: {
          path: './src/api/http/mutator.ts',
          name: 'apiRequest',
        },
        query: {
          useQuery: true,
          useMutation: true,
          signal: true,
        },
      },
    },
  },
});
