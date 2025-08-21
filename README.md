# Instavid Shoppable Videos for Magento 2

The most powerful and easy-to-use shoppable video extension for Magento 2. Transform your store with interactive video carousels that drive engagement and sales.

## ✨ Features

- **🎯 Easy Widget Integration** - Drag & drop widgets anywhere on your site
- **📱 Responsive Design** - Works perfectly on all devices
- **🔄 Page-Type Aware** - Automatically adapts to product, category, and homepage
- **⚡ Lightweight** - Minimal impact on page load times
- **🔧 Simple Configuration** - Just name your carousel and you're ready to go
- **🎬 External Video System** - Integrates with your existing video infrastructure

## 🚀 Quick Start

### 1. Install the Extension

```bash
# Upload the extension to your Magento store
# Extract to app/code/Instavid/ShoppableVideos/

# Enable the module
php bin/magento module:enable Instavid_ShoppableVideos

# Run setup
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### 2. Add Widgets to Your Pages

1. Go to **Admin Panel** → **Content** → **Widgets**
2. Click **Add Widget**
3. Select **Instavid Video Carousel**
4. Choose your **Layout Update** (where to place it)
5. Enter a **Carousel Name** (e.g., "Homepage Hero", "Category Videos")
6. Save and enjoy!

## 📍 Widget Placement Options

- **CMS Pages** - Add to any static page
- **Category Pages** - Show category-specific videos
- **Product Pages** - Display product demonstration videos
- **Homepage** - Create engaging hero sections

## ⚙️ Configuration

The widget automatically detects page type and configures itself:

- **Product Pages**: Shows product-specific video content
- **Category Pages**: Displays category-focused videos
- **Other Pages**: Renders homepage-style carousels

## 🎨 Customization

The extension provides clean, minimal containers that integrate seamlessly with your existing Instavid video system. No complex configuration needed - just name your carousel and let the system handle the rest.

## 🔒 Compatibility

- **Magento 2.4.x** (2.4.0 and above)
- **PHP 7.4+**
- **All themes** (Luma, Blank, custom themes)

## 📞 Support

For technical support or customization requests, contact our development team.

## 📄 License

This extension is proprietary software. All rights reserved.

---

**Instavid Shoppable Videos** - Making video commerce simple and effective. 