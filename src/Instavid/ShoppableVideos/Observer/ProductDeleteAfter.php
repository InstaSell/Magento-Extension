<?php
namespace Instavid\ShoppableVideos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Instavid\ShoppableVideos\Service\WebhookService;

class ProductDeleteAfter implements ObserverInterface
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
            // Format minimal product data for deletion webhook
            $productData = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'store_id' => $product->getStoreId()
            ];

            // Send webhook
            $this->webhookService->sendProductSync($productData, 'delete', $product->getStoreId());

            // Log for debugging
            $this->logger->info("[Instavid] Product delete webhook triggered for SKU: " . $product->getSku());

        } catch (\Exception $e) {
            $this->logger->error("[Instavid] Error in ProductDeleteAfter observer: " . $e->getMessage());
        }

        return $this;
    }
}