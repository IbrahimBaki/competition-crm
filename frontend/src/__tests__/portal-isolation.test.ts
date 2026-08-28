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

    // Forbidden import patterns
    const forbiddenPatterns = [
      // Staff shell
      { pattern: /from\s+['"].*@\/shell\//g, reason: 'staff shell (@/ alias)' },
      { pattern: /from\s+['"]\.\.\/\.\.\/shell\//g, reason: 'staff shell (relative)' },
      { pattern: /from\s+['"].*shell\//g, reason: 'staff shell (generic)' },

      // Staff auth/permissions
      { pattern: /from\s+['"].*@\/auth\//g, reason: 'staff auth (@/ alias)' },
      { pattern: /from\s+['"]\.\.\/\.\.\/auth\//g, reason: 'staff auth (relative)' },
      { pattern: /from\s+['"].*auth\/.*(?:AuthProvider|ProtectedRoute|permissions|RequirePermission)/g, reason: 'staff auth' },

      // Staff features
      { pattern: /from\s+['"].*@\/features\/(tickets|customers|admin|workspace)/g, reason: 'staff features (@/ alias)' },
      { pattern: /from\s+['"]\.\.\/\.\.\/features\/(tickets|customers|admin|workspace)/g, reason: 'staff features (relative)' },

      // Generated client (must be portal or public knowledge only)
      { pattern: /from\s+['"].*api\/generated\/(admin|workspace|customers|tickets)\/[^'"]*/g, reason: 'non-portal generated client' },
      { pattern: /from\s+['"].*@\/api\/generated\/(admin|workspace|customers|tickets)/g, reason: 'non-portal generated client (@/ alias)' },
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
              const currentLine = lines[i];
              if (!currentLine) continue;

              // Skip comments
              if (currentLine.trim().startsWith('//')) {
                continue;
              }

              // Check each forbidden pattern
              for (const { pattern, reason } of forbiddenPatterns) {
                if (pattern.test(currentLine)) {
                  violations.push(
                    `${relativePath}:${i + 1} - Forbidden import from ${reason}: ${currentLine.trim()}`
                  );
                }
              }
            }
          }
        }
      } catch (err) {
        // Directory might not exist, skip
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
