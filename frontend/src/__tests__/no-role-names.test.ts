import { describe, it, expect } from 'vitest';
import fs from 'fs';
import path from 'path';

describe('no role names in authorization logic', () => {
  it('should not contain role name identifiers as authorization predicates', () => {
    const __dirname = path.dirname(new URL(import.meta.url).pathname);
    const srcPath = path.join(__dirname, '../');
    const excludePaths = ['i18n', 'api/generated'];

    const roleNames = ['admin', 'administrator', 'supervisor', 'agent'];
    const violations: string[] = [];

    function scanDir(dir: string) {
      try {
        const files = fs.readdirSync(dir, { withFileTypes: true });

        for (const file of files) {
          const fullPath = path.join(dir, file.name);
          const relativePath = path.relative(srcPath, fullPath);

          // Skip excluded directories
          if (excludePaths.some((exclude) => relativePath.startsWith(exclude))) {
            continue;
          }

          if (file.isDirectory()) {
            scanDir(fullPath);
          } else if (file.name.endsWith('.ts') || file.name.endsWith('.tsx')) {
            const content = fs.readFileSync(fullPath, 'utf-8');
            const lines = content.split('\n');

            for (let i = 0; i < lines.length; i++) {
              const currentLine = lines[i];
              if (!currentLine) continue;

              // Skip comments and strings that are clearly i18n keys
              if (
                currentLine.trim().startsWith('//') ||
                currentLine.includes('i18n') ||
                currentLine.includes("t('") ||
                currentLine.includes('t("')
              ) {
                continue;
              }

              for (const role of roleNames) {
                // Look for role names used in authorization checks
                if (
                  (currentLine.includes(`'${role}'`) || currentLine.includes(`"${role}"`)) &&
                  (currentLine.includes('if') ||
                    currentLine.includes('?') ||
                    currentLine.includes('&&') ||
                    currentLine.includes('||') ||
                    currentLine.includes('includes') ||
                    currentLine.includes('===') ||
                    currentLine.includes('=='))
                ) {
                  violations.push(
                    `${relativePath}:${i + 1} - Found role name "${role}" in authorization logic`
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

    scanDir(srcPath);

    if (violations.length > 0) {
      throw new Error(`Found role names in authorization logic:\n${violations.join('\n')}`);
    }
    expect(violations).toHaveLength(0);
  });
});
