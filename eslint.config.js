import tsParser from '@typescript-eslint/parser';

export default [
    {
        ignores: [
            'public/**',
            'vendor/**',
            'storage/**',
            'bootstrap/cache/**',
            'bootstrap/ssr/**',
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
                selector: 'Literal[value=/(^|\\s)((pl|pr|ml|mr)-\\S+|text-(left|right))(\\s|$)/]',
                message: 'Use logical properties - this project is RTL-first.',
            }],
        },
    },
];
