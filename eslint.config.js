import tsParser from '@typescript-eslint/parser';

export default [
    {
        ignores: [
            'public/**',
            'vendor/**',
            'storage/**',
            'bootstrap/cache/**',
            'eslint.config.js',
            'vite.config.js',
        ],
    },
    {
        files: ['**/*.{js,jsx,ts,tsx}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            parser: tsParser,
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        rules: {
            'no-restricted-syntax': ['error', {
                selector: 'Literal[value=/\\b(pl|pr|ml|mr|text-left|text-right)-?\\w*/]',
                message: 'Use logical properties - this project is RTL-first.',
            }],
        },
    },
];
