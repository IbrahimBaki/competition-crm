import js from '@eslint/js';
import globals from 'globals';
import reactHooks from 'eslint-plugin-react-hooks';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';

export default [
  { ignores: ['dist', 'src/api/generated', 'coverage', 'node_modules'] },
  {
    files: ['**/*.{ts,tsx}'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: globals.browser,
      parser: tsParser,
      parserOptions: {
        ecmaFeatures: { jsx: true },
      },
    },
    plugins: {
      '@typescript-eslint': tsPlugin,
      'react-hooks': reactHooks,
    },
    rules: {
      ...js.configs.recommended.rules,
      ...reactHooks.configs.recommended.rules,
      // Disable no-unused-vars (both base and @typescript-eslint) since it conflicts with type
      // definitions (interfaces, function-type literals, ambient declarations) where parameter
      // names are part of the contract but not used in the definition itself
      'no-unused-vars': 'off',
      '@typescript-eslint/no-unused-vars': 'off',
      // Axios restriction is enforced by tests, not by ESLint (flat config limitations)
    },
  },
];
