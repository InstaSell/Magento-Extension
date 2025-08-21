<?php
namespace Instavid\ShoppableVideos\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Instavid\ShoppableVideos\Helper\Attribution;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Add implements HttpPostActionInterface, HttpGetActionInterface, CsrfAwareActionInterface
{
    protected $context;
    protected $resultJsonFactory;
    protected $cart;
    protected $productRepository;
    protected $customerSession;
    protected $formKey;
    protected $attributionHelper;
    protected $logger;
    protected $resourceConnection;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        FormKey $formKey,
        Attribution $attributionHelper,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection
    ) {
        $this->context = $context;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->attributionHelper = $attributionHelper;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    protected function getRequest()
    {
        return $this->context->getRequest();
    }

    protected function getProductData($sku)
    {
        $connection = $this->resourceConnection->getConnection();
        
        $select = $connection->select()
            ->from(['e' => $this->resourceConnection->getTableName('catalog_product_entity')], [
                'entity_id',
                'sku'
            ])
            ->where('e.sku = ?', $sku);

        $productData = $connection->fetchRow($select);
        
        if (!$productData) {
            $this->logger->warning('Product not found in database', ['sku' => $sku]);
            throw new NoSuchEntityException(__('Product not found'));
        }

        // Get status attribute
        $statusSelect = $connection->select()
            ->from(['attr' => $this->resourceConnection->getTableName('catalog_product_entity_int')], ['value'])
            ->join(
                ['def' => $this->resourceConnection->getTableName('eav_attribute')],
                'attr.attribute_id = def.attribute_id',
                []
            )
            ->where('attr.entity_id = ?', $productData['entity_id'])
            ->where('def.attribute_code = ?', 'status')
            ->where('attr.store_id = ?', 0);

        $status = $connection->fetchOne($statusSelect);
        
        // Get name attribute
        $nameSelect = $connection->select()
            ->from(['attr' => $this->resourceConnection->getTableName('catalog_product_entity_varchar')], ['value'])
            ->join(
                ['def' => $this->resourceConnection->getTableName('eav_attribute')],
                'attr.attribute_id = def.attribute_id',
                []
            )
            ->where('attr.entity_id = ?', $productData['entity_id'])
            ->where('def.attribute_code = ?', 'name')
            ->where('attr.store_id = ?', 0);

        $name = $connection->fetchOne($nameSelect);

        // Get visibility attribute
        $visibilitySelect = $connection->select()
            ->from(['attr' => $this->resourceConnection->getTableName('catalog_product_entity_int')], ['value'])
            ->join(
                ['def' => $this->resourceConnection->getTableName('eav_attribute')],
                'attr.attribute_id = def.attribute_id',
                []
            )
            ->where('attr.entity_id = ?', $productData['entity_id'])
            ->where('def.attribute_code = ?', 'visibility')
            ->where('attr.store_id = ?', 0);

        $visibility = $connection->fetchOne($visibilitySelect);

        $this->logger->info('Product data retrieved', [
            'entity_id' => $productData['entity_id'],
            'sku' => $sku,
            'status' => $status,
            'visibility' => $visibility,
            'name' => $name
        ]);

        return [
            'entity_id' => $productData['entity_id'],
            'sku' => $productData['sku'],
            'status' => $status ?: 0,
            'visibility' => $visibility ?: 1,
            'name' => $name ?: 'Unknown Product'
        ];
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $request = $this->getRequest();
            $sku = $request->getParam('sku');
            $qty = (int)$request->getParam('qty', 1);
            
            $this->logger->info('Cart add request received', ['sku' => $sku, 'qty' => $qty]);
            
            if (!$sku) {
                return $result->setData([
                    'success' => false,
                    'message' => 'Product SKU is required'
                ]);
            }

            if ($qty <= 0) {
                return $result->setData([
                    'success' => false,
                    'message' => 'Quantity must be greater than 0'
                ]);
            }

            try {
                $productData = $this->getProductData($sku);
            } catch (NoSuchEntityException $e) {
                return $result->setData([
                    'success' => false,
                    'message' => 'Product not found'
                ]);
            }

            // Enhanced product validation
            if ($productData['status'] != Status::STATUS_ENABLED) {
                $this->logger->warning('Product status is not enabled', [
                    'sku' => $sku,
                    'status' => $productData['status'],
                    'expected' => Status::STATUS_ENABLED
                ]);
                return $result->setData([
                    'success' => false,
                    'message' => 'Product is not available for purchase (status: ' . $productData['status'] . ')'
                ]);
            }

            // Check if product is visible and purchasable
            if ($productData['visibility'] == 1) { // 1 = Not Visible Individually
                $this->logger->warning('Product is not visible individually', [
                    'sku' => $sku,
                    'visibility' => $productData['visibility']
                ]);
                return $result->setData([
                    'success' => false,
                    'message' => 'Product is not available for purchase (visibility issue)'
                ]);
            }

            // Get the actual product object
            try {
                $product = $this->productRepository->getById($productData['entity_id']);
                $this->logger->info('Product loaded successfully', [
                    'product_id' => $productData['entity_id'], 
                    'sku' => $sku,
                    'type_id' => $product->getTypeId()
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to load product', [
                    'product_id' => $productData['entity_id'], 
                    'sku' => $sku, 
                    'error' => $e->getMessage()
                ]);
                return $result->setData([
                    'success' => false,
                    'message' => 'Failed to load product: ' . $e->getMessage()
                ]);
            }

            // Check if product is available for purchase
            if ($product->getStatus() != Status::STATUS_ENABLED) {
                $this->logger->warning('Product status check failed', [
                    'sku' => $sku,
                    'product_status' => $product->getStatus(),
                    'expected' => Status::STATUS_ENABLED
                ]);
                return $result->setData([
                    'success' => false,
                    'message' => 'Product is not available for purchase (product status check failed)'
                ]);
            }
            
            $buyRequest = [
                'qty' => $qty
            ];

            try {
                $this->cart->addProduct($product, $buyRequest);
                $this->cart->save();
                $this->logger->info('Product added to cart successfully', [
                    'product_id' => $productData['entity_id'], 
                    'sku' => $sku
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Failed to add product to cart', [
                    'product_id' => $productData['entity_id'], 
                    'sku' => $sku, 
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return $result->setData([
                    'success' => false,
                    'message' => 'Failed to add product to cart: ' . $e->getMessage()
                ]);
            }
            
            $quote = $this->cart->getQuote();
            $cartCount = $quote->getItemsCount();
            $cartTotal = $quote->getGrandTotal();
            
            $this->attributionHelper->setInstavidAttribution($sku);
            
            return $result->setData([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'product_name' => $productData['name'],
                'sku' => $sku,
                'attribution_set' => true,
                'is_customer' => $this->customerSession->isLoggedIn(),
                'customer_id' => $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomerId() : null
            ]);
            
        } catch (LocalizedException $e) {
            $this->logger->error('Localized exception in cart add', [
                'sku' => $sku ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected exception in cart add', [
                'sku' => $sku ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $result->setData([
                'success' => false,
                'message' => 'Failed to add product to cart. Please try again.'
            ]);
        }
    }
}