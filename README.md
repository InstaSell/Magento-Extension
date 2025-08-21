# Instavid Shoppable Videos for Magento 2

Professional video commerce extension for Magento 2. Transform your store with interactive, shoppable video experiences that drive conversions and increase sales.

## 🚀 Features

- **Video Carousel Widgets** - Insert interactive video carousels anywhere on your site
- **Product Integration** - Seamlessly connect videos with your product catalog
- **Mobile Responsive** - Optimized for all devices and screen sizes
- **SEO Optimized** - Built with search engine optimization in mind
- **Easy Configuration** - Simple admin interface for managing video content
- **Performance Focused** - Lightweight and fast loading
- **Cart Integration** - Direct product addition from video interactions

## 📦 Installation

### ✨ **Standard Composer Installation**

```bash
# Add our repository to your composer.json
composer config repositories.instasell vcs https://github.com/InstaSell/Magento-Extension

# Install the extension
composer require instasell/magento-extension
```

### 🔧 **Enable Module**

```bash
php bin/magento module:enable Instavid_ShoppableVideos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Manual Installation

1. Download the extension from GitHub
2. Extract to `app/code/Instavid/ShoppableVideos/`
3. Run the setup commands above

## 🎯 Quick Start

### 1. Enable the Module
```bash
php bin/magento module:enable Instavid_ShoppableVideos
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### 2. Add Widget to Your Store
1. Go to **Admin → Content → Widgets**
2. Click **Add Widget**
3. Select **Instavid Video Carousel**
4. Choose your **Layout Update** (e.g., Homepage)
5. Configure your **Carousel Name**
6. Save and test!

### 3. Configure Your Videos
- Set up your video content in the Instavid dashboard
- Use the same **Carousel Name** you configured in the widget
- Your videos will automatically appear on your store

## 🔧 Configuration

### Widget Parameters

- **Carousel Name**: Unique identifier for your video carousel (required)
- **Layout**: Choose where to display the widget
- **Template**: Select from available display templates

### System Configuration

Navigate to **Admin → Stores → Configuration → Instavid → Shoppable Videos** to configure:

- API credentials
- Default video settings
- Performance options
- Display preferences

## 📱 Widget Usage

### Available Widgets

- **Instavid Video Carousel**: Main video carousel widget with product integration
- More widgets coming soon!

### Placement Options

- Homepage
- Category pages
- Product pages
- CMS pages
- Any custom location

## 🆘 Support

- **Email**: support@instasell.io
- **Documentation**: [https://docs.instavid.com/magento](https://docs.instavid.com/magento)
- **Issues**: [GitHub Issues](https://github.com/InstaSell/Magento-Extension/issues)
- **Website**: [https://instavid.co/](https://instavid.co/)

## 🔄 Updates

Keep your extension up to date

```bash
# Update the extension
composer update instasell/magento-extension

# Refresh Magento
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## 📋 Requirements

- **PHP**: 7.4 or higher
- **Magento**: 2.4.3 or higher
- **Framework**: 103.0.0 or higher

## 📄 License

This extension is licensed under the MIT License. See the LICENSE file for details.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

---

**Transform your Magento store with the power of shoppable videos!** 🎥✨

*Built with ❤️ by the InstaSell Team*