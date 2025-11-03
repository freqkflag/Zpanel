# Lint Agent Integration Guide

How to integrate the lint agent with popular IDEs, editors, and CI/CD pipelines.

## Table of Contents

- [Visual Studio Code](#visual-studio-code)
- [PhpStorm / WebStorm](#phpstorm--webstorm)
- [Vim / Neovim](#vim--neovim)
- [Sublime Text](#sublime-text)
- [CI/CD Integration](#cicd-integration)
- [Docker Integration](#docker-integration)
- [Git Hooks](#git-hooks)

---

## Visual Studio Code

### Automatic Setup

The workspace comes pre-configured! Just open the project:

```bash
code /root/Zpanel
```

### Manual Configuration

If needed, add to `.vscode/settings.json`:

```json
{
  "editor.formatOnSave": false,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": false
  },
  "files.autoSave": "afterDelay",
  "files.autoSaveDelay": 1000
}
```

### Run via Tasks

1. Press `Ctrl+Shift+P` (Cmd+Shift+P on Mac)
2. Type "Run Task"
3. Select "Start Lint Agent"

Or use the keyboard shortcut:
- `Ctrl+Shift+B` → Select "Start Lint Agent"

### Recommended Extensions

Install these for the best experience:

- **PHP Intelephense** - PHP language support
- **Laravel Extra Intellisense** - Laravel autocomplete
- **Laravel Blade** - Blade template support
- **ESLint** - JavaScript linting
- **Prettier** - Code formatting
- **Laravel Pint** - PHP formatting integration

---

## PhpStorm / WebStorm

### Disable Built-in File Watchers

1. Go to **Settings** → **Tools** → **File Watchers**
2. Disable or remove existing watchers for:
   - PHP CS Fixer
   - PHP_CodeSniffer
   - ESLint
   - Prettier

### External Tool Configuration

Add the lint agent as an external tool:

1. **Settings** → **Tools** → **External Tools** → **+**
2. Configure:
   - **Name**: Lint Agent
   - **Program**: `node`
   - **Arguments**: `$ProjectFileDir$/lint-agent.js`
   - **Working directory**: `$ProjectFileDir$`

### Run Configuration

Create a run configuration:

1. **Run** → **Edit Configurations** → **+** → **Shell Script**
2. Configure:
   - **Name**: Lint Agent
   - **Script path**: `/root/Zpanel/lint-agent.js`
   - **Interpreter path**: `/usr/bin/node`
   - **Working directory**: `/root/Zpanel`

### Keyboard Shortcut

1. **Settings** → **Keymap**
2. Search for "Lint Agent" (your external tool)
3. Assign a shortcut (e.g., `Ctrl+Alt+L`)

---

## Vim / Neovim

### Terminal Split

The simplest approach - run in a split terminal:

```vim
:terminal npm run lint:watch
```

### Background Job (Vim 8+ / Neovim)

Add to your `.vimrc` or `init.vim`:

```vim
" Auto-start lint agent on Coolify project
augroup LintAgent
  autocmd!
  autocmd VimEnter */Zpanel/* call StartLintAgent()
augroup END

function! StartLintAgent()
  if isdirectory(expand('%:p:h') . '/implementation/phase-1/Zpanel')
    call jobstart('npm run lint:watch', {
      \ 'cwd': '/root/Zpanel',
      \ 'on_stdout': {channel, msg -> execute('echo "Lint: " . join(msg)')},
    \ })
    echo "Lint Agent started"
  endif
endfunction
```

### With tmux

If you use tmux:

```bash
# In your tmux session
tmux split-window -v 'cd /root/Zpanel && npm run lint:watch'
```

Add to `.tmux.conf`:

```bash
# Auto-start lint agent in Coolify projects
bind-key L split-window -v 'cd /root/Zpanel && npm run lint:watch'
```

---

## Sublime Text

### Build System

Create `Tools` → `Build System` → `New Build System`:

```json
{
  "cmd": ["npm", "run", "lint:watch"],
  "working_dir": "/root/Zpanel",
  "variants": [
    {
      "name": "Stop",
      "cmd": ["pkill", "-f", "node.*lint-agent.js"]
    }
  ]
}
```

Save as `Lint-Agent.sublime-build`

### Usage

- Start: `Ctrl+B` (Cmd+B on Mac)
- Stop: `Ctrl+Shift+B` → Select "Stop"

---

## CI/CD Integration

### GitHub Actions

Add to `.github/workflows/lint.yml`:

```yaml
name: Lint Code

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  lint:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        tools: composer
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
    
    - name: Install PHP dependencies
      working-directory: ./implementation/phase-1/Zpanel
      run: composer install --no-interaction --prefer-dist
    
    - name: Install Node dependencies
      working-directory: ./implementation/phase-1/Zpanel
      run: npm install
    
    - name: Run PHP Linter (Pint)
      working-directory: ./implementation/phase-1/Zpanel
      run: ./vendor/bin/pint --test
    
    - name: Run JavaScript Linter (ESLint)
      working-directory: ./implementation/phase-1/Zpanel
      run: npx eslint resources/js --ext .js,.vue
```

### GitLab CI

Add to `.gitlab-ci.yml`:

```yaml
stages:
  - lint

lint:php:
  stage: lint
  image: php:8.4-cli
  before_script:
    - apt-get update && apt-get install -y git unzip
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - cd implementation/phase-1/Zpanel
    - composer install --no-interaction --prefer-dist
  script:
    - ./vendor/bin/pint --test
  only:
    - merge_requests
    - main
    - develop

lint:javascript:
  stage: lint
  image: node:18
  before_script:
    - cd implementation/phase-1/Zpanel
    - npm install
  script:
    - npx eslint resources/js --ext .js,.vue
  only:
    - merge_requests
    - main
    - develop
```

### Jenkins Pipeline

Add to `Jenkinsfile`:

```groovy
pipeline {
    agent any
    
    stages {
        stage('Lint PHP') {
            steps {
                dir('implementation/phase-1/Zpanel') {
                    sh 'composer install --no-interaction --prefer-dist'
                    sh './vendor/bin/pint --test'
                }
            }
        }
        
        stage('Lint JavaScript') {
            steps {
                dir('implementation/phase-1/Zpanel') {
                    sh 'npm install'
                    sh 'npx eslint resources/js --ext .js,.vue'
                }
            }
        }
    }
    
    post {
        always {
            cleanWs()
        }
    }
}
```

---

## Docker Integration

### Development Container

Add to `.devcontainer/devcontainer.json`:

```json
{
  "name": "Coolify Development",
  "dockerComposeFile": "../docker-compose.dev.yml",
  "service": "app",
  "workspaceFolder": "/workspace",
  
  "postCreateCommand": "cd /workspace && ./setup-lint-agent.sh",
  "postStartCommand": "npm run lint:watch &",
  
  "customizations": {
    "vscode": {
      "extensions": [
        "bmewburn.vscode-intelephense-client",
        "dbaeumer.vscode-eslint",
        "esbenp.prettier-vscode"
      ]
    }
  }
}
```

### Docker Compose

Add to `docker-compose.yml`:

```yaml
services:
  lint-agent:
    image: node:18
    working_dir: /app
    volumes:
      - .:/app
    command: npm run lint:watch
    restart: unless-stopped
```

Run with:

```bash
docker-compose up lint-agent
```

---

## Git Hooks

### Pre-commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

# Get list of PHP files being committed
PHP_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep '\.php$')

if [ -n "$PHP_FILES" ]; then
    echo "Running Laravel Pint on staged PHP files..."
    cd implementation/phase-1/Zpanel
    
    for FILE in $PHP_FILES; do
        ./vendor/bin/pint "$FILE"
        git add "$FILE"
    done
fi

# Get list of JS/Vue files being committed
JS_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep -E '\.(js|vue)$')

if [ -n "$JS_FILES" ]; then
    echo "Running ESLint on staged JS/Vue files..."
    cd implementation/phase-1/Zpanel
    
    for FILE in $JS_FILES; do
        npx eslint --fix "$FILE"
        git add "$FILE"
    done
fi

exit 0
```

Make it executable:

```bash
chmod +x .git/hooks/pre-commit
```

### Husky Integration

If using Husky:

```bash
npm install --save-dev husky lint-staged
npx husky install
```

Add to `package.json`:

```json
{
  "lint-staged": {
    "implementation/phase-1/Zpanel/**/*.php": [
      "cd implementation/phase-1/Zpanel && ./vendor/bin/pint"
    ],
    "implementation/phase-1/Zpanel/**/*.{js,vue}": [
      "eslint --fix"
    ]
  }
}
```

---

## Troubleshooting Integration Issues

### Issue: Lint agent not detecting changes

**Solution**: Check file watcher limits (Linux):

```bash
# Check current limit
cat /proc/sys/fs/inotify/max_user_watches

# Increase limit
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

### Issue: Conflicts with IDE auto-formatting

**Solution**: Disable IDE auto-formatting:

- **VS Code**: Set `"editor.formatOnSave": false`
- **PhpStorm**: Disable File Watchers
- **Sublime**: Disable format-on-save plugins

### Issue: Performance issues with large projects

**Solution**: Increase debounce delay:

Edit `lint-agent.js`:
```javascript
debounceDelay: 1000, // Increase from 500ms to 1000ms
```

### Issue: Missing linters

**Solution**: Install dependencies:

```bash
cd /root/Zpanel
./setup-lint-agent.sh
```

---

## Advanced Configuration

### Custom Watch Patterns

Edit `lint-agent.js` to add custom patterns:

```javascript
watchPatterns: [
  'implementation/phase-1/Zpanel/app/**/*.php',
  'implementation/phase-1/Zpanel/resources/**/*.{js,vue,blade.php}',
  'your/custom/path/**/*.php', // Add custom paths
],
```

### Environment-Specific Configuration

Use environment variables:

```bash
# Set custom debounce delay
export LINT_AGENT_DEBOUNCE=1000

# Set custom project root
export LINT_AGENT_ROOT=/custom/path
```

Then modify `lint-agent.js` to read these values.

---

## Support

For integration issues:

1. Check the logs: `npm run lint:watch` output
2. Verify dependencies: `npm list` and `composer list`
3. Test linters manually:
   - PHP: `./vendor/bin/pint --test file.php`
   - JS: `npx eslint file.js`
   - CSS: `npx prettier --check file.css`

---

**Need more help?** See [README-LINT-AGENT.md](README-LINT-AGENT.md) for full documentation.

