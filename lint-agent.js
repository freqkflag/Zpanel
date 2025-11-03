#!/usr/bin/env node

/**
 * Lint Agent - Automatic linting on file save
 * 
 * This script watches for file changes and runs appropriate linters
 * based on the file type. It's designed to integrate with the
 * Coolify/Laravel development workflow.
 */

const chokidar = require('chokidar');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

// ANSI color codes for terminal output
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  dim: '\x1b[2m',
  red: '\x1b[31m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  blue: '\x1b[34m',
  magenta: '\x1b[35m',
  cyan: '\x1b[36m',
};

// Configuration
const config = {
  projectRoot: path.join(__dirname, 'implementation/phase-1/Zpanel'),
  debounceDelay: 500, // ms to wait before running linter
  watchPatterns: [
    'implementation/phase-1/Zpanel/app/**/*.php',
    'implementation/phase-1/Zpanel/resources/**/*.{js,vue,blade.php}',
    'implementation/phase-1/Zpanel/resources/**/*.css',
    'implementation/phase-1/Zpanel/routes/**/*.php',
    'implementation/phase-1/Zpanel/config/**/*.php',
    'implementation/phase-1/Zpanel/database/**/*.php',
    'implementation/phase-1/Zpanel/tests/**/*.php',
  ],
  ignorePatterns: [
    '**/node_modules/**',
    '**/vendor/**',
    '**/storage/**',
    '**/bootstrap/cache/**',
    '**/.git/**',
  ],
};

// Queue to manage linting tasks
const lintQueue = new Map();
const timers = new Map();

/**
 * Log with timestamp and color
 */
function log(message, color = colors.reset) {
  const timestamp = new Date().toLocaleTimeString();
  console.log(`${colors.dim}[${timestamp}]${colors.reset} ${color}${message}${colors.reset}`);
}

/**
 * Execute a command and return a promise
 */
function execCommand(command, args, cwd) {
  return new Promise((resolve, reject) => {
    const proc = spawn(command, args, {
      cwd,
      stdio: ['ignore', 'pipe', 'pipe'],
    });

    let stdout = '';
    let stderr = '';

    proc.stdout.on('data', (data) => {
      stdout += data.toString();
    });

    proc.stderr.on('data', (data) => {
      stderr += data.toString();
    });

    proc.on('close', (code) => {
      resolve({
        code,
        stdout,
        stderr,
      });
    });

    proc.on('error', (error) => {
      reject(error);
    });
  });
}

/**
 * Run Laravel Pint on PHP files
 */
async function lintPhp(filePath) {
  const relativePath = path.relative(config.projectRoot, filePath);
  log(`🔍 Linting PHP: ${relativePath}`, colors.cyan);

  try {
    const result = await execCommand(
      './vendor/bin/pint',
      [filePath, '--quiet'],
      config.projectRoot
    );

    if (result.code === 0) {
      log(`✅ PHP linting passed: ${relativePath}`, colors.green);
      return { success: true };
    } else {
      log(`❌ PHP linting failed: ${relativePath}`, colors.red);
      if (result.stdout) console.log(result.stdout);
      if (result.stderr) console.error(result.stderr);
      return { success: false, output: result.stderr || result.stdout };
    }
  } catch (error) {
    log(`⚠️  Error running Pint: ${error.message}`, colors.yellow);
    return { success: false, error: error.message };
  }
}

/**
 * Run ESLint on JavaScript/Vue files
 */
async function lintJavaScript(filePath) {
  const relativePath = path.relative(config.projectRoot, filePath);
  log(`🔍 Linting JS/Vue: ${relativePath}`, colors.cyan);

  // Check if ESLint is configured
  const eslintConfig = path.join(config.projectRoot, '.eslintrc.js');
  const eslintConfigJson = path.join(config.projectRoot, '.eslintrc.json');
  const packageJson = path.join(config.projectRoot, 'package.json');

  const hasEslintConfig = fs.existsSync(eslintConfig) || 
                          fs.existsSync(eslintConfigJson) ||
                          (fs.existsSync(packageJson) && 
                           JSON.parse(fs.readFileSync(packageJson, 'utf8')).eslintConfig);

  if (!hasEslintConfig) {
    log(`⚠️  No ESLint configuration found, skipping JS/Vue linting`, colors.yellow);
    return { success: true, skipped: true };
  }

  try {
    const result = await execCommand(
      'npx',
      ['eslint', filePath, '--fix'],
      config.projectRoot
    );

    if (result.code === 0) {
      log(`✅ JS/Vue linting passed: ${relativePath}`, colors.green);
      return { success: true };
    } else {
      log(`❌ JS/Vue linting failed: ${relativePath}`, colors.red);
      if (result.stdout) console.log(result.stdout);
      return { success: false, output: result.stdout };
    }
  } catch (error) {
    log(`⚠️  Error running ESLint: ${error.message}`, colors.yellow);
    return { success: false, error: error.message };
  }
}

/**
 * Run Prettier on CSS files
 */
async function lintCss(filePath) {
  const relativePath = path.relative(config.projectRoot, filePath);
  log(`🔍 Formatting CSS: ${relativePath}`, colors.cyan);

  try {
    const result = await execCommand(
      'npx',
      ['prettier', '--write', filePath],
      config.projectRoot
    );

    if (result.code === 0) {
      log(`✅ CSS formatting passed: ${relativePath}`, colors.green);
      return { success: true };
    } else {
      log(`❌ CSS formatting failed: ${relativePath}`, colors.red);
      if (result.stderr) console.error(result.stderr);
      return { success: false, output: result.stderr };
    }
  } catch (error) {
    log(`⚠️  Error running Prettier: ${error.message}`, colors.yellow);
    return { success: false, error: error.message };
  }
}

/**
 * Determine which linter to run based on file extension
 */
async function lintFile(filePath) {
  const ext = path.extname(filePath);
  const basename = path.basename(filePath);

  // Determine file type and run appropriate linter
  if (ext === '.php' || basename.endsWith('.blade.php')) {
    return await lintPhp(filePath);
  } else if (ext === '.js' || ext === '.vue') {
    return await lintJavaScript(filePath);
  } else if (ext === '.css') {
    return await lintCss(filePath);
  } else {
    log(`⚠️  Unknown file type: ${filePath}`, colors.yellow);
    return { success: true, skipped: true };
  }
}

/**
 * Debounced file change handler
 */
function handleFileChange(filePath) {
  // Clear existing timer for this file
  if (timers.has(filePath)) {
    clearTimeout(timers.get(filePath));
  }

  // Set new timer
  const timer = setTimeout(async () => {
    timers.delete(filePath);
    
    // Check if file still exists
    if (!fs.existsSync(filePath)) {
      return;
    }

    // Run linter
    try {
      await lintFile(filePath);
    } catch (error) {
      log(`❌ Unexpected error linting ${filePath}: ${error.message}`, colors.red);
    }
  }, config.debounceDelay);

  timers.set(filePath, timer);
}

/**
 * Initialize the file watcher
 */
function startWatcher() {
  log('🚀 Starting Lint Agent...', colors.bright + colors.green);
  log(`📁 Watching: ${config.projectRoot}`, colors.blue);
  log(`⏱️  Debounce delay: ${config.debounceDelay}ms`, colors.blue);
  log('', '');

  const watcher = chokidar.watch(config.watchPatterns, {
    ignored: config.ignorePatterns,
    persistent: true,
    ignoreInitial: true,
    awaitWriteFinish: {
      stabilityThreshold: 300,
      pollInterval: 100,
    },
  });

  watcher
    .on('change', (filePath) => {
      handleFileChange(filePath);
    })
    .on('add', (filePath) => {
      log(`📄 New file detected: ${path.relative(config.projectRoot, filePath)}`, colors.dim);
      handleFileChange(filePath);
    })
    .on('ready', () => {
      log('✅ Lint Agent is ready and watching for changes...', colors.green);
      log('💡 Save any file to trigger linting', colors.dim);
      log('', '');
    })
    .on('error', (error) => {
      log(`❌ Watcher error: ${error.message}`, colors.red);
    });

  // Graceful shutdown
  process.on('SIGINT', () => {
    log('', '');
    log('🛑 Shutting down Lint Agent...', colors.yellow);
    watcher.close().then(() => {
      log('👋 Goodbye!', colors.blue);
      process.exit(0);
    });
  });
}

// Check dependencies
function checkDependencies() {
  const packageJsonPath = path.join(config.projectRoot, 'package.json');
  const composerJsonPath = path.join(config.projectRoot, 'composer.json');

  if (!fs.existsSync(config.projectRoot)) {
    log(`❌ Project root not found: ${config.projectRoot}`, colors.red);
    log(`💡 Run this script from the Zpanel directory`, colors.yellow);
    process.exit(1);
  }

  if (!fs.existsSync(packageJsonPath)) {
    log(`⚠️  Warning: package.json not found at ${packageJsonPath}`, colors.yellow);
  }

  if (!fs.existsSync(composerJsonPath)) {
    log(`⚠️  Warning: composer.json not found at ${composerJsonPath}`, colors.yellow);
  }

  // Check if Pint exists
  const pintPath = path.join(config.projectRoot, 'vendor/bin/pint');
  if (!fs.existsSync(pintPath)) {
    log(`⚠️  Warning: Laravel Pint not found. Run 'composer install' in ${config.projectRoot}`, colors.yellow);
  }
}

// Main entry point
if (require.main === module) {
  console.clear();
  log('═══════════════════════════════════════════', colors.bright);
  log('  Coolify Lint Agent', colors.bright + colors.cyan);
  log('  Automatic code linting on file save', colors.dim);
  log('═══════════════════════════════════════════', colors.bright);
  log('', '');

  checkDependencies();
  startWatcher();
}

module.exports = { lintFile, lintPhp, lintJavaScript, lintCss };

