# Coolify Lint Agent

Automatic code linting system that runs when files are saved. This agent watches for file changes and automatically runs the appropriate linters based on file type.

## Features

- 🔍 **Automatic Linting**: Runs linters automatically when files are saved
- 🚀 **Multi-Language Support**: PHP (Pint), JavaScript/Vue (ESLint), CSS (Prettier)
- ⚡ **Debounced Execution**: Prevents excessive linting on rapid saves
- 🎨 **Colored Output**: Clear, visual feedback on linting results
- 🛡️ **Error Recovery**: Continues working even if individual lints fail

## Supported File Types

| File Type | Linter | Auto-Fix |
|-----------|--------|----------|
| `*.php` | Laravel Pint | ✅ Yes |
| `*.blade.php` | Laravel Pint | ✅ Yes |
| `*.js` | ESLint | ✅ Yes |
| `*.vue` | ESLint | ✅ Yes |
| `*.css` | Prettier | ✅ Yes |

## Installation

### Prerequisites

- Node.js 18+ 
- PHP 8.4+
- Composer
- npm

### Quick Setup

```bash
# From the Zpanel root directory
cd /root/Zpanel

# Install dependencies
npm install

# Install PHP and JS dependencies in the Laravel project
cd implementation/phase-1/Zpanel
composer install
npm install
cd ../..

# Make the lint agent executable
chmod +x lint-agent.js
```

Or use the setup script:

```bash
npm run setup
```

## Usage

### Development Mode (Manual)

Run the lint agent in your terminal:

```bash
npm run lint:watch
```

You should see:

```
═══════════════════════════════════════════
  Coolify Lint Agent
  Automatic code linting on file save
═══════════════════════════════════════════

🚀 Starting Lint Agent...
📁 Watching: /root/Zpanel/implementation/phase-1/Zpanel
⏱️  Debounce delay: 500ms

✅ Lint Agent is ready and watching for changes...
💡 Save any file to trigger linting
```

Now, whenever you save a file in your editor, the appropriate linter will run automatically.

### As a Background Service (systemd)

For persistent linting across terminal sessions:

```bash
# Copy the service file
sudo cp lint-agent.service /etc/systemd/system/

# Reload systemd
sudo systemctl daemon-reload

# Enable and start the service
sudo systemctl enable lint-agent@$USER
sudo systemctl start lint-agent@$USER

# Check status
sudo systemctl status lint-agent@$USER

# View logs
sudo journalctl -u lint-agent@$USER -f
```

To stop the service:

```bash
sudo systemctl stop lint-agent@$USER
```

## Configuration

### Watched Directories

Edit `lint-agent.js` to modify watched patterns:

```javascript
watchPatterns: [
  'implementation/phase-1/Zpanel/app/**/*.php',
  'implementation/phase-1/Zpanel/resources/**/*.{js,vue,blade.php}',
  'implementation/phase-1/Zpanel/resources/**/*.css',
  // Add more patterns here
],
```

### Ignored Directories

```javascript
ignorePatterns: [
  '**/node_modules/**',
  '**/vendor/**',
  '**/storage/**',
  '**/bootstrap/cache/**',
  '**/.git/**',
  // Add more patterns here
],
```

### Debounce Delay

Change the delay before linting runs (in milliseconds):

```javascript
debounceDelay: 500, // Wait 500ms after last save
```

## Linter Configuration

### PHP (Laravel Pint)

Configuration: `implementation/phase-1/Zpanel/pint.json`

The agent uses the existing Pint configuration. To customize:

```bash
cd implementation/phase-1/Zpanel
./vendor/bin/pint --help
```

### JavaScript/Vue (ESLint)

Configuration: `.eslintrc.js`

Customizable rules for JavaScript and Vue files. The configuration includes:

- ES2021 support
- Vue 3 recommended rules
- Alpine.js, Livewire, and Laravel Echo globals
- Coolify-specific code style

To customize:

```javascript
// .eslintrc.js
rules: {
  'your-rule': 'error',
}
```

### CSS (Prettier)

Configuration: `.prettierrc.json`

Formatting rules for CSS and other files:

```json
{
  "semi": true,
  "singleQuote": true,
  "printWidth": 100,
  "tabWidth": 4
}
```

## IDE Integration

### VS Code

Add to `.vscode/settings.json`:

```json
{
  "editor.formatOnSave": false,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": false
  },
  "files.watcherExclude": {
    "**/node_modules/**": true,
    "**/vendor/**": true
  }
}
```

**Note**: Disable IDE auto-formatting to prevent conflicts with the lint agent.

### PhpStorm/WebStorm

1. Go to **Settings** → **Tools** → **File Watchers**
2. Disable any existing file watchers for PHP, JS, or CSS
3. The lint agent will handle linting automatically

### Vim/Neovim

The lint agent works seamlessly with Vim. Just save files normally with `:w`.

## Troubleshooting

### "Pint not found" Warning

```bash
cd implementation/phase-1/Zpanel
composer install
```

### "ESLint configuration not found"

The lint agent will skip JavaScript linting if no ESLint config is found. This is normal if you only work with PHP.

### Files Not Being Watched

Check that:
1. Files match the watch patterns
2. Files aren't in ignore patterns
3. File watcher limit isn't exceeded (Linux):
   ```bash
   echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
   sudo sysctl -p
   ```

### High CPU Usage

Increase the debounce delay in `lint-agent.js`:

```javascript
debounceDelay: 1000, // Increase to 1 second
```

## Architecture

```
┌─────────────────┐
│  File System    │
│  Changes        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Chokidar      │
│  File Watcher   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Debounce      │
│   Handler       │
└────────┬────────┘
         │
    ┌────┴────┐
    │  Queue  │
    └────┬────┘
         │
    ┌────┴────────────────┬──────────────┐
    ▼                     ▼              ▼
┌─────────┐         ┌──────────┐    ┌─────────┐
│  Pint   │         │ ESLint   │    │Prettier │
│  (PHP)  │         │ (JS/Vue) │    │  (CSS)  │
└─────────┘         └──────────┘    └─────────┘
```

## Performance

- **Debouncing**: Prevents running linters on every keystroke
- **Process Isolation**: Each lint runs in a separate process
- **Selective Linting**: Only lints changed files, not the entire project
- **Async Execution**: Non-blocking linting operations

## Integration with Development Workflow

The lint agent integrates seamlessly with Coolify's development workflow:

1. **Pre-commit Hook**: Works alongside existing Git hooks
2. **Laravel Pint**: Uses the configured `pint.json` settings
3. **CI/CD**: Catches issues before they reach CI pipeline
4. **Team Consistency**: Ensures all developers use the same linting rules

## Logs

### Viewing Real-time Logs

```bash
# If running manually
# Logs appear in the terminal

# If running as service
sudo journalctl -u lint-agent@$USER -f
```

### Log Format

```
[HH:MM:SS] 🔍 Linting PHP: app/Models/Application.php
[HH:MM:SS] ✅ PHP linting passed: app/Models/Application.php
```

## Contributing

To extend the lint agent:

1. Add new file patterns to `watchPatterns`
2. Create a new lint function (e.g., `lintTypeScript`)
3. Update `lintFile()` to handle the new file type
4. Update this README

## License

Apache-2.0 (same as Coolify/Zpanel)

## Support

For issues specific to the lint agent, check:

1. Lint agent is running: `ps aux | grep lint-agent`
2. Dependencies are installed: `npm list`
3. Linters are available: `./vendor/bin/pint --version`

For Coolify-specific issues, refer to the main project documentation.

---

**Happy Linting! 🎉**

