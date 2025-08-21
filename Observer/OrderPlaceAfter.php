<?php
namespace Instavid\ShoppableVideos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Instavid\ShoppableVideos\Service\WebhookService;
use Instavid\ShoppableVideos\Helper\Attribution;

class OrderPlaceAfter implements ObserverInterface
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
     * @var Attribution
     */
    protected $attributionHelper;

    /**
     * @param LoggerInterface $logger
     * @param WebhookService $webhookService
     * @param Attribution $attributionHelper
     */
    public function __construct(
        LoggerInterface $logger,
        WebhookService $webhookService,
        Attribution $attributionHelper
    ) {
        $this->logger = $logger;
        $this->webhookService = $webhookService;
        $this->attributionHelper = $attributionHelper;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        try {
            // Format order data for webhook
            $orderData = $this->webhookService->formatOrderData($order);

            // Get attribution data from session
            $orderData['attribution'] = $this->attributionHelper->getAttributionForWebhook();

            // Send webhook
            $this->webhookService->sendOrderAttribution($orderData, $order->getStoreId());

            // Log for debugging
            $isInstavidOrder = $this->attributionHelper->hasInstavidAttribution();
            $this->logger->info("[Instavid] Order placed webhook triggered for order: " . $order->getIncrementId() . 
                               " | From Instavid: " . ($isInstavidOrder ? 'YES' : 'NO'));

            // Clear attribution after order is placed
            if ($isInstavidOrder) {
                $this->attributionHelper->clearInstavidAttribution();
            }

        } catch (\Exception $e) {
            $this->logger->error("[Instavid] Error in OrderPlaceAfter observer: " . $e->getMessage());
        }

        return $this;
    }


}