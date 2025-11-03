# Lint Agent - Quick Start Guide

Get automatic linting up and running in 2 minutes!

## 🚀 Quick Setup

### Step 1: Run Setup Script

```bash
cd /root/Zpanel
./setup-lint-agent.sh
```

This will:
- ✅ Check prerequisites (Node.js, PHP, Composer)
- ✅ Install all dependencies
- ✅ Configure the lint agent

### Step 2: Start the Lint Agent

Choose one of these methods:

#### Option A: Manual (Recommended for Development)

```bash
npm run lint:watch
```

Leave this running in a terminal. You'll see lint results as you save files.

#### Option B: As a Background Service

```bash
# Install service
sudo cp lint-agent.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable lint-agent@$USER
sudo systemctl start lint-agent@$USER

# Check it's running
sudo systemctl status lint-agent@$USER
```

### Step 3: Test It!

1. Open any PHP file in `implementation/phase-1/Zpanel/app/`
2. Make a small change (add a space, remove a semicolon, etc.)
3. Save the file (Ctrl+S / Cmd+S)
4. Watch the lint agent terminal - you should see:
   ```
   🔍 Linting PHP: app/Models/YourFile.php
   ✅ PHP linting passed: app/Models/YourFile.php
   ```

That's it! The lint agent is now watching for changes and will automatically lint your files on save.

## 📝 What Gets Linted?

| File Type | Tool | Auto-Fix |
|-----------|------|----------|
| `*.php` | Laravel Pint | ✅ |
| `*.blade.php` | Laravel Pint | ✅ |
| `*.js` | ESLint | ✅ |
| `*.vue` | ESLint | ✅ |
| `*.css` | Prettier | ✅ |

## 🛑 Stopping the Lint Agent

### If Running Manually
Press `Ctrl+C` in the terminal

### If Running as Service
```bash
sudo systemctl stop lint-agent@$USER
```

## 💡 Tips

1. **Multiple Projects**: The agent only watches the Zpanel/Coolify project
2. **Performance**: Debounced by 500ms - won't run on every keystroke
3. **Conflicts**: Disable auto-formatting in your IDE to avoid conflicts
4. **Logs**: All output is color-coded for easy reading

## 🐛 Troubleshooting

### "Pint not found"
```bash
cd implementation/phase-1/Zpanel
composer install
```

### Files Not Being Watched
Check the file matches watch patterns in `lint-agent.js`

### High CPU Usage
Increase debounce delay in `lint-agent.js` (line 31):
```javascript
debounceDelay: 1000, // Increase from 500 to 1000
```

## 📖 Full Documentation

See [README-LINT-AGENT.md](README-LINT-AGENT.md) for complete documentation.

## VS Code Integration

If using VS Code, the workspace is pre-configured! Just:

1. Open the workspace
2. Accept the recommended extensions prompt
3. Run the task: `Ctrl+Shift+P` → "Run Task" → "Start Lint Agent"

---

**Happy Linting! 🎉**

