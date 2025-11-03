module.exports = {
  root: true,
  env: {
    browser: true,
    node: true,
    es2021: true,
  },
  extends: [
    'eslint:recommended',
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  rules: {
    // Coolify/Laravel specific rules
    'no-console': 'off', // Allow console in development
    'no-unused-vars': ['warn', { 
      argsIgnorePattern: '^_',
      varsIgnorePattern: '^_' 
    }],
    'no-undef': 'error',
    'semi': ['error', 'always'],
    'quotes': ['error', 'single', { avoidEscape: true }],
    'indent': ['error', 4],
    'comma-dangle': ['error', 'always-multiline'],
    'no-trailing-spaces': 'error',
    'eol-last': ['error', 'always'],
    'object-curly-spacing': ['error', 'always'],
    'array-bracket-spacing': ['error', 'never'],
    'space-before-function-paren': ['error', {
      anonymous: 'always',
      named: 'never',
      asyncArrow: 'always',
    }],
  },
  overrides: [
    {
      // Vue files
      files: ['*.vue'],
      parser: 'vue-eslint-parser',
      extends: [
        'plugin:vue/vue3-recommended',
      ],
      rules: {
        'vue/multi-word-component-names': 'off',
        'vue/no-v-html': 'warn',
      },
    },
  ],
  globals: {
    // Alpine.js
    Alpine: 'readonly',
    // Livewire
    Livewire: 'readonly',
    // Laravel Echo
    Echo: 'readonly',
    // Axios
    axios: 'readonly',
  },
};

