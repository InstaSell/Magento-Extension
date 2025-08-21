<?php
namespace Instavid\ShoppableVideos\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;

class GetCount extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Cart $cart
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cart $cart,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
    }

    /**
     * Get current cart count and status
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $quote = $this->cart->getQuote();
            $cartCount = $quote->getItemsCount();
            $cartTotal = $quote->getGrandTotal();
            $isCustomerLoggedIn = $this->customerSession->isLoggedIn();
            $customerId = $isCustomerLoggedIn ? $this->customerSession->getCustomerId() : null;
            
            return $result->setData([
                'success' => true,
                'cart_count' => (int)$cartCount,
                'cart_total' => (float)$cartTotal,
                'is_customer_logged_in' => $isCustomerLoggedIn,
                'customer_id' => $customerId,
                'currency_code' => $quote->getQuoteCurrencyCode()
            ]);
            
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => 'Failed to get cart information',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
        }
    }
} 