<?php
namespace Instavid\ShoppableVideos\Service;

use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Webhook Service for Instavid Shoppable Videos
 * 
 * Handles all webhook communications with the Instavid platform
 * - Product synchronization (create, update, delete)
 * - Order attribution tracking
 * - Customer behavior analytics
 * - Real-time inventory updates
 */
class WebhookService
{
    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var string
     */
    protected $webhookEndpoint;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @param CurlFactory $curlFactory
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     */
    public function __construct(
        CurlFactory $curlFactory,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer
    ) {
        $this->curlFactory = $curlFactory;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
        
        // Load configuration
        $this->loadConfiguration();
    }

    /**
     * Load webhook configuration from system config
     */
    protected function loadConfiguration()
    {
        $this->webhookEndpoint = $this->scopeConfig->getValue(
            'instavid_webhook/general/webhook_url',
            ScopeInterface::SCOPE_STORE
        );
        
        $this->isEnabled = $this->scopeConfig->isSetFlag(
            'instavid_webhook/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if webhooks are enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled && !empty($this->webhookEndpoint);
    }

    /**
     * Send webhook data to Instavid platform
     *
     * @param array $data
     * @param string $eventType
     * @param int $storeId
     * @return bool
     */
    public function sendWebhook(array $data, string $eventType, int $storeId = 0): bool
    {
        if (!$this->isEnabled()) {
            $this->logger->info('[Instavid] Webhooks are disabled or not configured');
            return false;
        }

        try {
            $webhookData = [
                'event' => 'product.sync',
                'timestamp' => time(),
                'store_id' => $storeId,
                'data' => $data,
                'platform' => 'magento2',
                'version' => '1.0.0'
            ];

            $this->logger->info('[Instavid] Sending webhook: ' . $eventType, $webhookData);

            // Build the full endpoint URL based on event type
            $endpoint = $this->webhookEndpoint;
            if (strpos($eventType, "product") === 0) {
                $endpoint .= "/wh/magento/product-sync";
            } elseif (strpos($eventType, "order") === 0) {
                // Order endpoint not implemented yet
                $this->logger->info("[Instavid] Order webhook endpoint not implemented yet");
                return false;
            }

            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader('X-Instavid-Event', $eventType);
            $curl->addHeader('X-Instavid-Store', (string)$storeId);

            $curl->post($endpoint, $this->jsonSerializer->serialize($webhookData));

            $responseCode = $curl->getStatus();
            $responseBody = $curl->getBody();

            if ($responseCode >= 200 && $responseCode < 300) {
                $this->logger->info('[Instavid] Webhook successful: ' . $eventType, [
                    'response_code' => $responseCode,
                    'response_body' => $responseBody
                ]);
                return true;
            } else {
                $this->logger->error('[Instavid] Webhook failed: ' . $eventType, [
                    'response_code' => $responseCode,
                    'response_body' => $responseBody,
                    'webhook_data' => $webhookData
                ]);
                return false;
            }

        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Webhook exception: ' . $e->getMessage(), [
                'event' => $eventType,
                'store_id' => $storeId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send product creation webhook
     *
     * @param Product $product
     * @param int $storeId
     * @return bool
     */
    public function sendProductCreated(Product $product, int $storeId = 0): bool
    {
        $productData = $this->formatProductData($product);
        return $this->sendWebhook(['action' => 'create', 'product' => $productData], 'product.created', $storeId);
    }

    /**
     * Send product update webhook
     *
     * @param Product $product
     * @param int $storeId
     * @return bool
     */
    public function sendProductUpdated(Product $product, int $storeId = 0): bool
    {
        $productData = $this->formatProductData($product);
        return $this->sendWebhook(['action' => 'update', 'product' => $productData], 'product.updated', $storeId);
    }

    /**
     * Send product deletion webhook
     *
     * @param int $productId
     * @param int $storeId
     * @return bool
     */
    public function sendProductDeleted(int $productId, int $storeId = 0): bool
    {
        $productData = ['id' => $productId];
        return $this->sendWebhook(['action' => 'delete', 'product' => $productData], 'product.deleted', $storeId);
    }

    /**
     * Send order placement webhook
     *
     * @param Order $order
     * @param array $attributionData
     * @param int $storeId
     * @return bool
     */
    public function sendOrderPlaced(Order $order, array $attributionData = [], int $storeId = 0): bool
    {
        $orderData = $this->formatOrderData($order, $attributionData);
        return $this->sendWebhook($orderData, 'order.placed', $storeId);
    }

    /**
     * Send customer registration webhook
     *
     * @param array $customerData
     * @param int $storeId
     * @return bool
     */
    public function sendCustomerRegistered(array $customerData, int $storeId = 0): bool
    {
        return $this->sendWebhook($customerData, 'customer.registered', $storeId);
    }

    /**
     * Send customer login webhook
     *
     * @param array $customerData
     * @param int $storeId
     * @return bool
     */
    public function sendCustomerLogin(array $customerData, int $storeId = 0): bool
    {
        return $this->sendWebhook($customerData, 'customer.login', $storeId);
    }

    /**
     * Send cart update webhook
     *
     * @param array $cartData
     * @param int $storeId
     * @return bool
     */
    public function sendCartUpdated(array $cartData, int $storeId = 0): bool
    {
        return $this->sendWebhook($cartData, 'cart.updated', $storeId);
    }

    /**
     * Send video view webhook
     *
     * @param array $videoData
     * @param int $storeId
     * @return bool
     */
    public function sendVideoViewed(array $videoData, int $storeId = 0): bool
    {
        return $this->sendWebhook($videoData, 'video.viewed', $storeId);
    }

    /**
     * Send video interaction webhook
     *
     * @param array $interactionData
     * @param int $storeId
     * @return bool
     */
    public function sendVideoInteraction(array $interactionData, int $storeId = 0): bool
    {
        return $this->sendWebhook($interactionData, 'video.interaction', $storeId);
    }

    /**
     * Format product data for webhook
     *
     * @param Product $product
     * @return array
     */
    public function formatProductData(Product $product): array
    {
        try {
            $store = method_exists($product, 'getStore') ? $product->getStore() : null;
            $website = null;
            
            if ($store) {
                try {
                    if (method_exists($store, 'getWebsite')) {
                        $website = $store->getWebsite();
                    }
                } catch (\Exception $e) {
                    $this->logger->warning('[Instavid] Could not get website for store: ' . $e->getMessage());
                }
            }
            
            return [
                'id' => method_exists($product, 'getId') ? (int)$product->getId() : 0,
                'sku' => method_exists($product, 'getSku') ? $product->getSku() : '',
                'name' => method_exists($product, 'getName') ? $product->getName() : '',
                'description' => method_exists($product, 'getDescription') ? $product->getDescription() : '',
                'short_description' => method_exists($product, 'getShortDescription') ? $product->getShortDescription() : '',
                'price' => method_exists($product, 'getPrice') ? (float)$product->getPrice() : 0.0,
                'special_price' => method_exists($product, 'getSpecialPrice') && $product->getSpecialPrice() ? (float)$product->getSpecialPrice() : null,
                'status' => method_exists($product, 'getStatus') ? (int)$product->getStatus() : 1,
                'visibility' => method_exists($product, 'getVisibility') ? (int)$product->getVisibility() : 4,
                'type_id' => method_exists($product, 'getTypeId') ? $product->getTypeId() : 'simple',
                'attribute_set_id' => method_exists($product, 'getAttributeSetId') ? (int)$product->getAttributeSetId() : 0,
                'website_ids' => method_exists($product, 'getWebsiteIds') ? array_values(array_map('intval', $product->getWebsiteIds())) : [],
                'category_ids' => method_exists($product, 'getCategoryIds') ? array_values(array_map('intval', $product->getCategoryIds())) : [],
                'stock_data' => [
                    'qty' => method_exists($product, 'getQty') ? (float)$product->getQty() : 0.0,
                    'is_in_stock' => method_exists($product, 'getIsInStock') ? (bool)$product->getIsInStock() : true
                ],
                'images' => $this->getProductImages($product),
                'url' => $this->getProductUrl($product),
                'created_at' => method_exists($product, 'getCreatedAt') ? $product->getCreatedAt() : '',
                'updated_at' => method_exists($product, 'getUpdatedAt') ? $product->getUpdatedAt() : '',
                'custom_attributes' => $this->getCustomAttributes($product),
                'store' => [
                    'store_id' => $store ? (method_exists($store, 'getId') ? (int)$store->getId() : 0) : 0,
                    'store_code' => $store ? (method_exists($store, 'getCode') ? $store->getCode() : '') : '',
                    'store_name' => $store ? (method_exists($store, 'getName') ? $store->getName() : '') : '',
                    'store_url' => $store ? (method_exists($store, 'getBaseUrl') ? $store->getBaseUrl() : '') : '',
                    'website_id' => $website ? (method_exists($website, 'getId') ? (int)$website->getId() : 0) : 0,
                    'website_name' => $website ? (method_exists($website, 'getName') ? $website->getName() : '') : ''
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error formatting product data: ' . $e->getMessage());
            // Return minimal data if formatting fails
            return [
                'id' => method_exists($product, 'getId') ? $product->getId() : 0,
                'sku' => method_exists($product, 'getSku') ? $product->getSku() : '',
                'name' => method_exists($product, 'getName') ? $product->getName() : '',
                'error' => 'Data formatting failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send product sync webhook (legacy method for backward compatibility)
     *
     * @param array $productData
     * @param string $action
     * @param int $storeId
     * @return bool
     */
    public function sendProductSync(array $productData, string $action, int $storeId = 0): bool
    {
        $eventType = 'product.' . $action;
        return $this->sendWebhook(['action' => $action, 'product' => $productData], $eventType, $storeId);
    }

    /**
     * Format order data for webhook
     *
     * @param Order $order
     * @param array $attributionData
     * @return array
     */
    protected function formatOrderData(Order $order, array $attributionData = []): array
    {
        $orderData = [
            'order_id' => $order->getIncrementId(),
            'entity_id' => $order->getId(),
            'customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'customer_group_id' => $order->getCustomerGroupId(),
            'store_id' => $order->getStoreId(),
            'website_id' => $order->getWebsiteId(),
            'grand_total' => $order->getGrandTotal(),
            'subtotal' => $order->getSubtotal(),
            'shipping_amount' => $order->getShippingAmount(),
            'tax_amount' => $order->getTaxAmount(),
            'discount_amount' => $order->getDiscountAmount(),
            'currency_code' => $order->getOrderCurrencyCode(),
            'order_status' => $order->getStatus(),
            'payment_method' => $order->getPayment()->getMethod(),
            'shipping_method' => $order->getShippingMethod(),
            'created_at' => $order->getCreatedAt(),
            'updated_at' => $order->getUpdatedAt(),
            'items' => $this->formatOrderItems($order),
            'billing_address' => $this->formatAddress($order->getBillingAddress()),
            'shipping_address' => $this->formatAddress($order->getShippingAddress()),
            'attribution' => $attributionData
        ];

        return $orderData;
    }

    /**
     * Get product URL
     *
     * @param Product $product
     * @return string
     */
    protected function getProductUrl(Product $product): string
    {
        try {
            if (method_exists($product, 'getProductUrl')) {
                return $product->getProductUrl();
            }
            
            // Fallback: construct URL manually
            if (method_exists($product, 'getUrlKey') && $product->getUrlKey()) {
                $store = $product->getStore();
                if ($store && method_exists($store, 'getBaseUrl')) {
                    return $store->getBaseUrl() . $product->getUrlKey() . '.html';
                }
            }
            
            return '';
        } catch (\Exception $e) {
            $this->logger->warning('[Instavid] Could not get product URL: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get product images
     *
     * @param Product $product
     * @return array
     */
    protected function getProductImages(Product $product): array
    {
        $images = [];
        try {
            $mediaGalleryEntries = $product->getMediaGalleryEntries();
            if ($mediaGalleryEntries) {
                foreach ($mediaGalleryEntries as $entry) {
                    $imageUrl = '';
                    try {
                        if ($entry->getFile()) {
                            // Try to get the full URL using media config
                            if (method_exists($product, 'getMediaConfig')) {
                                $imageUrl = $product->getMediaConfig()->getMediaUrl($entry->getFile());
                            } else {
                                // Fallback: construct URL manually
                                $imageUrl = $entry->getFile();
                            }
                        }
                    } catch (\Exception $e) {
                        $this->logger->warning('[Instavid] Could not get image URL for file: ' . $entry->getFile());
                        $imageUrl = $entry->getFile(); // Use file path as fallback
                    }
                    
                    $images[] = [
                        'url' => $imageUrl,
                        'label' => $entry->getLabel(),
                        'position' => $entry->getPosition()
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error getting product images: ' . $e->getMessage());
        }
        return $images;
    }

    /**
     * Get custom attributes
     *
     * @param Product $product
     * @return array
     */
    protected function getCustomAttributes(Product $product): array
    {
        $attributes = [];
        try {
            if (method_exists($product, 'getCustomAttributes')) {
                $customAttributes = $product->getCustomAttributes();
                if ($customAttributes) {
                    foreach ($customAttributes as $attribute) {
                        try {
                            $attributes[$attribute->getAttributeCode()] = $attribute->getValue();
                        } catch (\Exception $e) {
                            $this->logger->warning('[Instavid] Could not get value for attribute: ' . $attribute->getAttributeCode());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error getting custom attributes: ' . $e->getMessage());
        }
        return $attributes;
    }

    /**
     * Format order items
     *
     * @param Order $order
     * @return array
     */
    protected function formatOrderItems(Order $order): array
    {
        $items = [];
        try {
            foreach ($order->getAllItems() as $item) {
                $items[] = [
                    'item_id' => $item->getItemId(),
                    'product_id' => $item->getProductId(),
                    'sku' => $item->getSku(),
                    'name' => $item->getName(),
                    'qty_ordered' => $item->getQtyOrdered(),
                    'qty_shipped' => $item->getQtyShipped(),
                    'qty_invoiced' => $item->getQtyInvoiced(),
                    'qty_refunded' => $item->getQtyRefunded(),
                    'price' => $item->getPrice(),
                    'original_price' => $item->getOriginalPrice(),
                    'row_total' => $item->getRowTotal(),
                    'row_total_incl_tax' => $item->getRowTotalInclTax(),
                    'tax_amount' => $item->getTaxAmount(),
                    'discount_amount' => $item->getDiscountAmount(),
                    'product_options' => $item->getProductOptions()
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error formatting order items: ' . $e->getMessage());
        }
        return $items;
    }

    /**
     * Format address data
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    protected function formatAddress($address): array
    {
        if (!$address) {
            return [];
        }

        return [
            'id' => $address->getId(),
            'address_type' => $address->getAddressType(),
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'company' => $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'region_id' => $address->getRegionId(),
            'postcode' => $address->getPostcode(),
            'country_id' => $address->getCountryId(),
            'telephone' => $address->getTelephone(),
            'fax' => $address->getFax()
        ];
    }

    /**
     * Test webhook connectivity
     *
     * @return array
     */
    public function testConnectivity(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Webhooks are disabled or not configured',
                'config' => [
                    'enabled' => $this->isEnabled,
                    'endpoint' => $this->webhookEndpoint
                ]
            ];
        }

        try {
            $testData = [
                'test' => true,
                'timestamp' => time(),
                'message' => 'Connectivity test from Magento 2'
            ];

            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader('X-Instavid-Event', 'test.connectivity');

            $curl->post($this->webhookEndpoint, $this->jsonSerializer->serialize($testData));

            $responseCode = $curl->getStatus();
            $responseBody = $curl->getBody();

            if ($responseCode >= 200 && $responseCode < 300) {
                return [
                    'success' => true,
                    'message' => 'Webhook connectivity test successful',
                    'response_code' => $responseCode,
                    'response_body' => $responseBody,
                    'config' => [
                        'endpoint' => $this->webhookEndpoint
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Webhook connectivity test failed',
                    'response_code' => $responseCode,
                    'response_body' => $responseBody,
                    'config' => [
                        'endpoint' => $this->webhookEndpoint
                    ]
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Webhook connectivity test exception: ' . $e->getMessage(),
                'exception' => $e->getMessage(),
                'config' => [
                    'endpoint' => $this->webhookEndpoint
                ]
            ];
        }
    }

    /**
     * Get webhook configuration status
     *
     * @return array
     */
    public function getConfigurationStatus(): array
    {
        return [
            'enabled' => $this->isEnabled,
            'endpoint_configured' => !empty($this->webhookEndpoint),
            'webhook_url' => $this->webhookEndpoint,
            'status' => $this->isEnabled() ? 'ready' : 'not_configured'
        ];
    }
} 