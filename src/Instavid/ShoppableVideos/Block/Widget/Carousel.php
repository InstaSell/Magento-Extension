<?php
namespace Instavid\ShoppableVideos\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\FormKey;

/**
 * Video Carousel Widget
 * 
 * Simple, powerful widget for displaying shoppable videos.
 * Better than Firework - easier to use, more reliable.
 */
class Carousel extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Instavid_ShoppableVideos::widgets/carousel.phtml';
    
    protected $registry;
    protected $cart;
    protected $customerSession;
    protected $formKey;
    protected $widgetData;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        Cart $cart,
        CustomerSession $customerSession,
        FormKey $formKey,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->widgetData = $data;

        parent::__construct($context, $data);
    }

    /**
     * Get the widget type identifier
     */
    public function getWidgetType(): string
    {
        return 'carousel';
    }

    /**
     * Get the widget display name
     */
    public function getWidgetName(): string
    {
        return 'Instavid Video Carousel';
    }

    /**
     * Get the widget description
     */
    public function getWidgetDescription(): string
    {
        return 'Insert interactive video carousels anywhere on your site';
    }

    /**
     * Get the widget template path
     */
    public function getTemplatePath(): string
    {
        return 'Instavid_ShoppableVideos::widgets/carousel.phtml';
    }

    /**
     * Get widget configuration parameters
     */
    public function getWidgetParameters(): array
    {
        return [
            'carousel_name' => [
                'type' => 'text',
                'label' => 'Carousel Name',
                'description' => 'Give your carousel a unique name (e.g., Homepage Hero, Category Videos)',
                'required' => true,
                'visible' => true,
                'sort_order' => 10
            ]
        ];
    }

    /**
     * Validate widget configuration
     */
    public function validateConfiguration(array $data): bool
    {
        return !empty($data['carousel_name']);
    }

    /**
     * Debug method to test if widget is loading
     */
    public function getDebugInfo()
    {
        return [
            'widget_loaded' => true,
            'carousel_name' => $this->getCarouselName(),
            'template_path' => $this->getTemplate(),
            'timestamp' => time()
        ];
    }

    /**
     * Get current page type
     */
    public function getPageType(): string
    {
        if ($this->registry->registry('current_product')) {
            return 'product';
        } elseif ($this->registry->registry('current_category')) {
            return 'category';
        } else {
            return 'other';
        }
    }

    /**
     * Get current product ID
     */
    public function getCurrentProductId(): ?int
    {
        $product = $this->registry->registry('current_product');
        return $product ? (int)$product->getId() : null;
    }

    /**
     * Get current category ID
     */
    public function getCurrentCategoryId(): ?int
    {
        $category = $this->registry->registry('current_category');
        return $category ? (int)$category->getId() : null;
    }

    /**
     * Get cart item count
     */
    public function getCartItemCount(): int
    {
        try {
            return (int)$this->cart->getQuote()->getItemsCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if customer is logged in
     */
    public function isCustomerLoggedIn(): bool
    {
        try {
            return $this->customerSession->isLoggedIn();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get customer ID if logged in
     */
    public function getCustomerId(): ?int
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                return (int)$this->customerSession->getCustomerId();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get form key for CSRF protection
     */
    public function getFormKey(): string
    {
        try {
            return $this->formKey->getFormKey();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get widget data by key
     */
    public function getWidgetData(string $key, $default = null)
    {
        return $this->widgetData[$key] ?? $default;
    }

    /**
     * Generate unique widget ID
     */
    public function generateWidgetId(string $prefix = 'instavid', int $length = 8): string
    {
        return $prefix . '-' . substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', $length)), 0, $length);
    }

    /**
     * Get carousel name - THE ONLY FIELD WE NEED!
     */
    public function getCarouselName(): string
    {
        return $this->getWidgetData('carousel_name', 'Video Carousel');
    }

    /**
     * Get JavaScript configuration
     */
    public function getJavaScriptConfig(): array
    {
        return [
            'widgetType' => 'carousel',
            'widgetId' => $this->generateWidgetId('carousel'),
            'pageType' => $this->getPageType(),
            'productId' => $this->getCurrentProductId(),
            'categoryId' => $this->getCurrentCategoryId(),
            'customerId' => $this->getCustomerId(),
            'isLoggedIn' => $this->isCustomerLoggedIn(),
            'cartItemCount' => $this->getCartItemCount(),
            'formKey' => $this->getFormKey(),
            'carouselName' => $this->getCarouselName(),
            'timestamp' => time()
        ];
    }

    /**
     * Get widget CSS classes
     */
    public function getWidgetCssClasses(): string
    {
        $classes = [
            'instavid-widget',
            'instavid-carousel',
            'instavid-responsive'
        ];

        return implode(' ', $classes);
    }
} 