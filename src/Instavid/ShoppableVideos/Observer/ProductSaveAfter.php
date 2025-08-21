<?php
namespace Instavid\ShoppableVideos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Instavid\ShoppableVideos\Service\WebhookService;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var WebhookService
     */
    protected $webhookService;

    /**
     * @param LoggerInterface $logger
     * @param WebhookService $webhookService
     */
    public function __construct(
        LoggerInterface $logger,
        WebhookService $webhookService
    ) {
        $this->logger = $logger;
        $this->webhookService = $webhookService;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        try {
            // Determine if this is a new product or update
            $action = $product->isObjectNew() ? 'create' : 'update';

            // Format product data for webhook
            $productData = $this->webhookService->formatProductData($product);

            // Get valid store ID (avoid admin store 0)
            $storeId = $product->getStoreId();
            if ($storeId === 0) {
                // If admin store, get the first non-admin store
                $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class);
                $stores = $storeManager->getStores();
                foreach ($stores as $store) {
                    if ($store->getId() > 0) {
                        $storeId = $store->getId();
                        break;
                    }
                }
            }
            
            // Send webhook
            $this->webhookService->sendProductSync($productData, $action, $storeId);

            // Log for debugging (keep existing logging but make it more concise)
            $this->logger->info("[Instavid] Product {$action} webhook triggered for SKU: " . $product->getSku());

        } catch (\Exception $e) {
            $this->logger->error("[Instavid] Error in ProductSaveAfter observer: " . $e->getMessage());
        }

        return $this;
    }
}
