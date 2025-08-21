# Git Workflow Guide

## Daily Development

### 1. Start Working
```bash
# Make sure you're on the main branch
git checkout main
git pull origin main

# Create a feature branch
git checkout -b feature/new-feature-name
```

### 2. Make Changes
- Edit your files
- Test your changes
- Commit frequently with clear messages

```bash
git add .
git commit -m "Add new feature: description of what you added"
```

### 3. Push and Create Pull Request
```bash
git push origin feature/new-feature-name
```

Then create a Pull Request on GitHub to merge into main.

## Releasing a New Version

### 1. Update Version in composer.json
```json
{
    "version": "1.1.0"
}
```

### 2. Commit Version Change
```bash
git add composer.json
git commit -m "Bump version to 1.1.0"
git push origin main
```

### 3. Create Release Tag
```bash
# Create annotated tag
git tag -a v1.1.0 -m "Release v1.1.0 - New Stories Widget"

# Push tag to GitHub
git push origin v1.1.0
```

### 4. Update Documentation
- Update README.md if needed
- Update INSTALLATION.md if needed
- Commit documentation updates

## Customer Updates

When customers want to update:

```bash
# They run this command
composer update instavid/shoppable-videos

# Then refresh Magento
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Branch Strategy

- **main**: Production-ready code
- **feature/***: New features in development
- **hotfix/***: Quick fixes for production issues

## Commit Message Format

```
Type: Brief description

- Use present tense ("Add feature" not "Added feature")
- Start with a verb
- Keep first line under 50 characters
- Add details after blank line if needed

Examples:
- "Add Stories Widget"
- "Fix carousel navigation bug"
- "Update documentation for v1.1.0"
``` 