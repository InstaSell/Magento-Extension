<?php
namespace Instavid\ShoppableVideos\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\FormKey;
use Magento\Framework\Data\Form\FormKey as FormKeyValidator;

/**
 * Abstract Widget Base Class
 * 
 * Provides common functionality for all Instavid widgets:
 * - Carousel, Stories, Video PiP, Banners, etc.
 */
abstract class AbstractWidget extends Template implements WidgetInterface
{
    protected $registry;
    protected $cart;
    protected $customerSession;
    protected $formKey;
    protected $formKeyValidator;
    protected $widgetData;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        Cart $cart,
        CustomerSession $customerSession,
        FormKey $formKey,
        FormKeyValidator $formKeyValidator,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->formKeyValidator = $formKeyValidator;
        $this->widgetData = $data;

        parent::__construct($context, $data);
    }

    /**
     * Get current page type
     *
     * @return string
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
     *
     * @return int|null
     */
    public function getCurrentProductId(): ?int
    {
        $product = $this->registry->registry('current_product');
        return $product ? (int)$product->getId() : null;
    }

    /**
     * Get current category ID
     *
     * @return int|null
     */
    public function getCurrentCategoryId(): ?int
    {
        $category = $this->registry->registry('current_category');
        return $category ? (int)$category->getId() : null;
    }

    /**
     * Get cart item count
     *
     * @return int
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
     *
     * @return bool
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
     *
     * @return int|null
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
     *
     * @return string
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
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getWidgetData(string $key, $default = null)
    {
        return $this->widgetData[$key] ?? $default;
    }

    /**
     * Generate unique widget ID
     *
     * @param string $prefix
     * @param int $length
     * @return string
     */
    protected function generateWidgetId(string $prefix = 'instavid', int $length = 8): string
    {
        return $prefix . '-' . substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', $length)), 0, $length);
    }

    /**
     * Get widget CSS classes
     *
     * @return string
     */
    public function getWidgetCssClasses(): string
    {
        $classes = [
            'instavid-widget',
            'instavid-' . strtolower($this->getWidgetType()),
            'instavid-responsive'
        ];

        return implode(' ', $classes);
    }

    /**
     * Get widget JavaScript configuration
     *
     * @return array
     */
    public function getJavaScriptConfig(): array
    {
        return [
            'widgetType' => $this->getWidgetType(),
            'widgetId' => $this->generateWidgetId(strtolower($this->getWidgetType())),
            'pageType' => $this->getPageType(),
            'productId' => $this->getCurrentProductId(),
            'categoryId' => $this->getCurrentCategoryId(),
            'customerId' => $this->getCustomerId(),
            'isLoggedIn' => $this->isCustomerLoggedIn(),
            'cartItemCount' => $this->getCartItemCount(),
            'formKey' => $this->getFormKey(),
            'timestamp' => time()
        ];
    }
} 