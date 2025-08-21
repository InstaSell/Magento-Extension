<?php
namespace Instavid\ShoppableVideos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\FormKey;

class Carousel extends Template
{
    protected $registry;
    protected $carouselName;
    protected $cart;
    protected $customerSession;
    protected $formKey;

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

        // Accept carousel_name passed as widget parameter or fallback to default
        $this->carouselName = isset($data['carousel_name']) ? $data['carousel_name'] : 'Homepage 1';

        parent::__construct($context, $data);
    }

    public function getPageType()
    {
        if ($this->registry->registry('current_product')) {
            return 'product';
        } elseif ($this->registry->registry('current_category')) {
            return 'other';
        } else {
            return 'other';
        }
    }

    public function getCurrentProductId()
    {
        $product = $this->registry->registry('current_product');
        if ($product) {
            return $product->getId();
        }
        return null;
    }

    public function getCurrentCategoryId()
    {
        $category = $this->registry->registry('current_category');
        if ($category) {
            return $category->getId();
        }
        return null;
    }

    // Add a getter to expose carousel name to template
    public function getCarouselName()
    {
        return $this->carouselName;
    }

    /**
     * Get current cart item count
     *
     * @return int
     */
    public function getCartItemCount()
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
    public function isCustomerLoggedIn()
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
    public function getCustomerId()
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
    public function getFormKey()
    {
        try {
            return $this->formKey->getFormKey();
        } catch (\Exception $e) {
            return '';
        }
    }
} 