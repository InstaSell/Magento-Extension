# Instavid Shoppable Videos for Magento 2

Professional video commerce extension for Magento 2. Transform your store with interactive, shoppable video experiences that drive conversions and increase sales.

## Installation

### Standard Composer Installation

```bash
# Add our repository to your composer.json
composer config repositories.instavid vcs https://github.com/instavid/shoppable-videos

# Install the extension
composer require instavid/shoppable-videos
```

### Enable Module

```bash
php bin/magento module:enable Instavid_ShoppableVideos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Manual Installation

1. Download the extension from GitHub
2. Extract to `app/code/Instavid/ShoppableVideos/`
3. Run the setup commands above

## Quick Start

### 1. Enable the Module

```bash
php bin/magento module:enable Instavid_ShoppableVideos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### 2. Add Widget to Your Store

1. Navigate to Admin → Content → Widgets
2. Click Add Widget
3. Select Instavid Video Carousel
4. Choose your Layout Update (e.g., Homepage)
6. Save and test

### 3. Configure Your Videos

- Set up your video content in the Instavid dashboard
- Use the same Carousel Name you configured in the widget
- Your videos will automatically appear on your store

## Configuration

### Widget Parameters

- **Carousel Name**: Unique identifier for your video carousel (required)
- **Layout**: Choose where to display the widget
- **Template**: Select from available display templates

### System Configuration

Navigate to Admin → Stores → Configuration → Instavid → Shoppable Videos to configure:

- API credentials
- Default video settings
- Performance options
- Display preferences

## Widget Usage

### Available Widgets

- **Instavid Video Carousel**: Main video carousel widget with product integration
- Additional widgets are planned for future releases

### Placement Options

- Homepage
- Category pages
- Product pages
- CMS pages
- Any custom location

## Support

- **Email**: support@instasell.io
- **Issues**: GitHub Issues
- **Website**: https://instavid.co/

## Updates

Keep your extension up to date:

```bash
# Update the extension
composer update instavid/shoppable-videos

# Refresh Magento
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Requirements

- **PHP**: 7.4 or higher
- **Magento**: 2.4.3 or higher
- **Framework**: 103.0.0 or higher

## Contributing

We welcome contributions. Please see our Contributing Guidelines for details.

---

**Transform your Magento store with the power of shoppable videos.**

*Developed by the Instavid Team*