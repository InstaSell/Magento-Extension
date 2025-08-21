<?php
namespace Instavid\ShoppableVideos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class Attribution extends AbstractHelper
{
    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @param Context $context
     * @param SessionManager $session
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        SessionManager $session,
        LoggerInterface $logger,
        RequestInterface $request,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->logger = $logger;
        $this->request = $request;
        $this->url = $url;
    }

    /**
     * Capture Instavid attribution from URL parameters
     *
     * @return void
     */
    public function captureInstavidAttribution()
    {
        try {
            $videoId = $this->request->getParam('instavid_video');
            $carouselName = $this->request->getParam('instavid_carousel');
            $source = $this->request->getParam('instavid_source');

            // Only capture if we have at least a video ID
            if ($videoId) {
                $attributionData = [
                    'source' => 'instavid',
                    'video_id' => $videoId,
                    'carousel_name' => $carouselName ?: 'unknown',
                    'instavid_source' => $source ?: 'video_click',
                    'attribution_timestamp' => time(),
                    'session_id' => $this->session->getSessionId(),
                    'captured_url' => $this->url->getCurrentUrl()
                ];

                // Store in session for tracking
                $this->session->setData('instavid_attribution', $attributionData);

                // Log attribution for analytics
                $this->logger->info('[Instavid] Attribution captured: ' . json_encode($attributionData));
            }

        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error capturing attribution: ' . $e->getMessage());
        }
    }

    /**
     * Set simple Instavid attribution data in session
     *
     * @param string $sku
     * @return void
     */
    public function setInstavidAttribution($sku)
    {
        try {
            $attributionData = [
                'source' => 'instavid',
                'sku' => $sku,
                'action' => 'add_to_cart',
                'timestamp' => time(),
                'session_id' => $this->session->getSessionId()
            ];

            // Store in session for tracking
            $this->session->setData('instavid_attribution', $attributionData);

            // Log attribution for analytics
            $this->logger->info('[Instavid] Attribution set: ' . json_encode($attributionData));

        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error setting attribution: ' . $e->getMessage());
        }
    }

    /**
     * Get current attribution data from session
     *
     * @return array|null
     */
    public function getInstavidAttribution()
    {
        try {
            return $this->session->getData('instavid_attribution');
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error getting attribution: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear attribution data from session
     *
     * @return void
     */
    public function clearInstavidAttribution()
    {
        try {
            $this->session->unsetData('instavid_attribution');
        } catch (\Exception $e) {
            $this->logger->error('[Instavid] Error clearing attribution: ' . $e->getMessage());
        }
    }

    /**
     * Check if attribution exists for current session
     *
     * @return bool
     */
    public function hasInstavidAttribution()
    {
        $attribution = $this->getInstavidAttribution();
        return $attribution !== null && !empty($attribution);
    }
}