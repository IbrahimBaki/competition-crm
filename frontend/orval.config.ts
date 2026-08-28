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
      // Must list every tag used in openapi.yaml. A tag omitted here is silently
      // dropped from the generated client, and `clean: true` deletes whatever was
      // generated for it before — that is why the whole Portal surface had no
      // client and its pages ended up as stubs.
      filterTags: [
        'Organization',
        'Security',
        'Customers',
        'Ticketing',
        'Attachments',
        'Workspace',
        'Knowledge',
        'Automation',
        'Notifications',
        'Integrations',
        'Reporting',
        'Channels',
        'Portal',
        'Sla',
        'Ai',
        'Observability',
      ],
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
