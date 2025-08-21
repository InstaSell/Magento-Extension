<?php
namespace Instavid\ShoppableVideos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Instavid\ShoppableVideos\Helper\Attribution;
use Psr\Log\LoggerInterface;

class CaptureAttribution implements ObserverInterface
{
    /**
     * @var Attribution
     */
    protected $attributionHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Attribution $attributionHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Attribution $attributionHelper,
        LoggerInterface $logger
    ) {
        $this->attributionHelper = $attributionHelper;
        $this->logger = $logger;
    }

    /**
     * Capture Instavid attribution when user visits any page
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            // Capture attribution from URL parameters
            $this->attributionHelper->captureInstavidAttribution();
            
            // Log if attribution was captured
            if ($this->attributionHelper->hasInstavidAttribution()) {
                $attribution = $this->attributionHelper->getInstavidAttribution();
                $this->logger->info("[Instavid] Attribution captured: " . json_encode($attribution));
            }
            
        } catch (\Exception $e) {
            $this->logger->error("[Instavid] Error capturing attribution: " . $e->getMessage());
        }

        return $this;
    }
}