import { describe, expect, it } from 'vitest';
import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
function files(dir: string): string[] { return fs.readdirSync(dir, { withFileTypes: true }).flatMap((entry) => entry.isDirectory() ? files(path.join(dir, entry.name)) : [path.join(dir, entry.name)]); }
describe('V2 design-system architecture', () => {
  it('does not cross into feature, API, or auth policy layers', () => {
    const violations = files(root).filter((file) => /\.(ts|tsx)$/.test(file)).flatMap((file) => fs.readFileSync(file, 'utf8').split('\n').map((line, index) => ({ line, index })).filter(({ line }) => /from ['"](@\/)?(features|api|auth)\//.test(line)).map(({ index, line }) => `${path.relative(root, file)}:${index + 1} ${line.trim()}`));
    expect(violations).toEqual([]);
  });
  it('keeps raw Radix imports inside the design-system', () => {
    const src = path.resolve(root, '..');
    const violations = files(src).filter((file) => /\.(ts|tsx)$/.test(file) && !file.startsWith(root)).flatMap((file) => fs.readFileSync(file, 'utf8').split('\n').filter((line) => /from ['"]@radix-ui\//.test(line)).map((line) => `${path.relative(src, file)} ${line.trim()}`));
    expect(violations).toEqual([]);
  });
  it('does not use raw Tailwind visual utilities in V2 source', () => {
    const violations = files(root).filter((file) => /\.(ts|tsx)$/.test(file)).flatMap((file) => fs.readFileSync(file, 'utf8').split('\n').filter((line) => /(?:bg|text|border|rounded|shadow|animate)-(?:[a-z]+-|\[)/.test(line)).map((line) => `${path.relative(root, file)} ${line.trim()}`));
    expect(violations).toEqual([]);
  });
  it('keeps production primitives free of literal user-facing JSX copy', () => {
    const production = files(root).filter((file) => /\.(ts|tsx)$/.test(file) && !file.includes('__tests__') && !file.includes('showcase'));
    const violations = production.flatMap((file) => fs.readFileSync(file, 'utf8').split('\n').filter((line) => />[A-Za-z][^<{]*</.test(line)).map((line) => `${path.relative(root, file)} ${line.trim()}`));
    expect(violations).toEqual([]);
  });
});
