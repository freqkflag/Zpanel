# Pre-Commit Hooks Setup

Automate code quality checks before every commit.

## Installation

### 1. Install pre-commit

```bash
# Using pip
pip install pre-commit

# Using Homebrew (macOS)
brew install pre-commit

# Using apt (Ubuntu/Debian)
sudo apt install pre-commit
```

### 2. Install hooks

```bash
# From project root
pre-commit install

# Install for commit-msg as well
pre-commit install --hook-type commit-msg
```

### 3. Verify installation

```bash
pre-commit run --all-files
```

## What Gets Checked

### Code Quality Checks

1. **Trailing whitespace** - Removes trailing whitespace
2. **End of file fixer** - Ensures files end with newline
3. **YAML validation** - Checks YAML syntax
4. **Large files** - Prevents committing files >1MB
5. **Merge conflicts** - Detects unresolved conflicts
6. **Private keys** - Prevents committing SSH keys

### PHP-Specific Checks

1. **Laravel Pint** - Enforces PSR-12 code style
2. **PHPStan** - Static analysis for type safety
3. **Pest Unit Tests** - Runs fast unit tests

## Usage

### Automatic Checks

Hooks run automatically on `git commit`:

```bash
git add .
git commit -m "feat: add new feature"

# Pre-commit hooks run automatically:
# - Trailing whitespace...............Passed
# - End of file fixer.................Passed
# - Laravel Pint......................Passed
# - PHPStan...........................Passed
# - Pest Unit Tests...................Passed
```

### Manual Execution

Run hooks manually:

```bash
# Run all hooks
pre-commit run --all-files

# Run specific hook
pre-commit run pint --all-files
pre-commit run phpstan --all-files

# Skip hooks for emergency commits
git commit --no-verify -m "emergency fix"
```

## Configuration

Edit `.pre-commit-config.yaml` to customize:

```yaml
repos:
  - repo: local
    hooks:
      - id: pint
        name: Laravel Pint
        entry: implementation/phase-1/Zpanel/vendor/bin/pint
        language: system
        types: [php]
        pass_filenames: false
```

## Troubleshooting

### Hooks Failing

If hooks fail:

1. **Fix the issues** reported by the hook
2. **Stage the fixes**: `git add .`
3. **Retry commit**: `git commit -m "..."`

### Slow Hook Execution

If hooks are slow:

```bash
# Run only changed files (when supported)
pre-commit run --files path/to/changed/file.php

# Skip slow hooks occasionally
SKIP=phpstan git commit -m "quick fix"
```

### Update Hooks

```bash
# Update to latest hook versions
pre-commit autoupdate

# Clean and reinstall
pre-commit clean
pre-commit install
```

## Best Practices

- ✅ **DO** run pre-commit before pushing
- ✅ **DO** fix all issues before committing
- ✅ **DO** keep hooks fast (<30 seconds)
- ❌ **DON'T** skip hooks regularly
- ❌ **DON'T** commit with `--no-verify` unless emergency

## CI Integration

Pre-commit checks also run in CI/CD:

```yaml
# .github/workflows/code-quality.yml
- name: Run pre-commit
  uses: pre-commit/action@v3.0.0
```

This ensures consistency between local and CI environments.

## Additional Hooks

### Commit Message Validation

Add conventional commits validation:

```yaml
- repo: https://github.com/compilerla/conventional-pre-commit
  rev: v3.0.0
  hooks:
    - id: conventional-pre-commit
      stages: [commit-msg]
```

### Spell Checking

```yaml
- repo: https://github.com/codespell-project/codespell
  rev: v2.2.6
  hooks:
    - id: codespell
      args: [--ignore-words-list, 'connexion,ba']
```

## Customization

### Team-Specific Hooks

Add custom hooks for your team:

```yaml
- repo: local
  hooks:
    - id: custom-check
      name: Custom Validation
      entry: ./scripts/custom-check.sh
      language: system
      pass_filenames: false
```

## Additional Resources

- [Pre-commit Documentation](https://pre-commit.com/)
- [Supported Hooks](https://pre-commit.com/hooks.html)
- [Creating Custom Hooks](https://pre-commit.com/#creating-new-hooks)

