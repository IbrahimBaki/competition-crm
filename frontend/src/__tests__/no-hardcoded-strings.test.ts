import { describe, it, expect } from 'vitest';
import fs from 'fs';
import path from 'path';

// Modeled on no-role-names.test.ts's file-scanning approach. Flags the most
// common way user-facing copy sneaks in outside i18n: a literal string
// passed to an attribute that renders as visible/announced text.
describe('no hardcoded user-facing strings in the ticket UI', () => {
  it('does not pass literal strings to text-bearing JSX attributes under features/tickets', () => {
    const __dirname = path.dirname(new URL(import.meta.url).pathname);
    const targetPath = path.join(__dirname, '../features/tickets');

    const textAttributePattern = /\b(placeholder|aria-label|title|alt)=(["'])[A-Za-z][^"']*\2/;

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

        if (!file.name.endsWith('.tsx')) continue;
        if (file.name.includes('.test.')) continue;

        const content = fs.readFileSync(fullPath, 'utf-8');
        const lines = content.split('\n');

        lines.forEach((line, index) => {
          if (line.trim().startsWith('//')) return;
          if (textAttributePattern.test(line)) {
            violations.push(`${path.relative(targetPath, fullPath)}:${index + 1} - "${line.trim()}"`);
          }
        });
      }
    }

    scanDir(targetPath);

    if (violations.length > 0) {
      throw new Error(
        `Found literal text-bearing attributes outside i18n (use t(...) instead):\n${violations.join('\n')}`
      );
    }
    expect(violations).toHaveLength(0);
  });
});
