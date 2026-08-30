import { describe, it, expect } from 'vitest';
import fs from 'fs';
import path from 'path';

describe('no SLA recalculation in the ticket UI', () => {
  it('does not contain client-side date arithmetic under features/tickets', () => {
    const __dirname = path.dirname(new URL(import.meta.url).pathname);
    const targetPath = path.join(__dirname, '../features/tickets');

    const forbiddenPatterns = [
      /Date\.now\(\)\s*-/,
      /getTime\(\)\s*-/,
      /differenceIn/,
      /dayjs\([^)]*\)\.diff/,
    ];

    const violations: string[] = [];

    function scanDir(dir: string) {
      let files: fs.Dirent[];
      try {
        files = fs.readdirSync(dir, { withFileTypes: true });
      } catch {
        return;
      }

      for (const file of files) {
        const fullPath = path.join(dir, file.name);

        if (file.isDirectory()) {
          scanDir(fullPath);
          continue;
        }

        if (!file.name.endsWith('.ts') && !file.name.endsWith('.tsx')) continue;
        if (file.name.includes('.test.')) continue;

        const content = fs.readFileSync(fullPath, 'utf-8');
        const lines = content.split('\n');

        lines.forEach((line, index) => {
          forbiddenPatterns.forEach((pattern) => {
            if (pattern.test(line)) {
              violations.push(`${path.relative(targetPath, fullPath)}:${index + 1} - "${line.trim()}"`);
            }
          });
        });
      }
    }

    scanDir(targetPath);

    if (violations.length > 0) {
      throw new Error(`Found SLA-style date arithmetic in the ticket UI:\n${violations.join('\n')}`);
    }
    expect(violations).toHaveLength(0);
  });
});
