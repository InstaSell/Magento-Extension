# Installation Guide

## Standard Composer Installation

This extension uses the standard Composer VCS (Version Control System) method for distribution.

### 1. Add Repository

```bash
composer config repositories.instavid vcs https://github.com/instavid/shoppable-videos
```

### 2. Install Extension

```bash
composer require instavid/shoppable-videos
```

### 3. Enable Module

```bash
php bin/magento module:enable Instavid_ShoppableVideos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### 4. Verify Installation

Check that the module is enabled:
```bash
php bin/magento module:status Instavid_ShoppableVideos
```

## Updates

To update the extension:

```bash
composer update instavid/shoppable-videos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Manual Installation

If you prefer manual installation:

1. Download the extension from GitHub
2. Extract to `app/code/Instavid/ShoppableVideos/`
3. Run the enable commands above

## Requirements

- PHP 7.4 or higher
- Magento 2.4.3 or higher
- Magento Framework 103.0.0 or higher 