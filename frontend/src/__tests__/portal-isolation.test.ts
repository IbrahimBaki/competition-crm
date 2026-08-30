import { describe, it, expect } from 'vitest';
import fs from 'fs';
import path from 'path';

describe('portal isolation', () => {
  it('should not import staff shell, staff auth, or staff features from portal code', () => {
    const __dirname = path.dirname(new URL(import.meta.url).pathname);
    const srcPath = path.join(__dirname, '../');

    // Paths to scan for violations
    const portalPaths = [
      path.join(srcPath, 'portal'),
      path.join(srcPath, 'pages/portal'),
    ];

    // Forbidden imports (staff-only, not portal)
    const forbiddenImports = [
      'shell/',
      'auth/AuthProvider',
      'auth/ProtectedRoute',
      'auth/permissions',
      'auth/RequirePermission',
      'auth/session',
      '@/shell',
      '@/auth',
      'features/tickets/',
      'features/customers/',
      'features/admin/',
      'features/workspace/',
      'api/generated/admin/',
      'api/generated/workspace/',
      'api/generated/customers/',
      'api/generated/tickets/',
    ];

    const violations: string[] = [];

    function scanDir(dir: string) {
      try {
        const files = fs.readdirSync(dir, { withFileTypes: true });

        for (const file of files) {
          const fullPath = path.join(dir, file.name);
          const relativePath = path.relative(srcPath, fullPath);

          if (file.isDirectory()) {
            scanDir(fullPath);
          } else if (file.name.endsWith('.ts') || file.name.endsWith('.tsx')) {
            const content = fs.readFileSync(fullPath, 'utf-8');
            const lines = content.split('\n');

            for (let i = 0; i < lines.length; i++) {
              const line = lines[i];
              if (!line || line.trim().startsWith('//')) continue;

              // Check each forbidden import
              for (const forbidden of forbiddenImports) {
                if (line.includes(`from '`) && line.includes(forbidden)) {
                  violations.push(`${relativePath}:${i + 1} - Forbidden import: ${line.trim()}`);
                  break;
                }
              }
            }
          }
        }
      } catch (err) {
        // skip
      }
    }

    // Scan both portal paths
    for (const portalPath of portalPaths) {
      if (fs.existsSync(portalPath)) {
        scanDir(portalPath);
      }
    }

    if (violations.length > 0) {
      throw new Error(`Portal isolation violations found:\n${violations.join('\n')}`);
    }
    expect(violations).toHaveLength(0);
  });
});
