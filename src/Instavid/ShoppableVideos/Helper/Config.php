<?php
namespace Instavid\ShoppableVideos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration Helper for Instavid Shoppable Videos
 * 
 * Manages all configuration settings including URLs, widget settings, and environment switching.
 */
class Config extends AbstractHelper
{
    /**
     * Configuration paths
     */
    const XML_PATH_ENABLED = 'instavid_widget/general/enabled';
    const XML_PATH_ENVIRONMENT = 'instavid_widget/general/environment';
    const XML_PATH_JS_URL = 'instavid_widget/urls/%s/js_url';
    const XML_PATH_CSS_URL = 'instavid_widget/urls/%s/css_url';
    const XML_PATH_CAROUSEL_HEIGHT = 'instavid_widget/carousel/default_height';
    const XML_PATH_CAROUSEL_AUTOPLAY = 'instavid_widget/carousel/default_autoplay';
    const XML_PATH_CAROUSEL_LOOP = 'instavid_widget/carousel/default_loop';

    /**
     * Environment constants
     */
    const ENVIRONMENT_DEVELOPMENT = 'development';
    const ENVIRONMENT_STAGING = 'staging';
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * Check if extension is enabled
     *
     * @param string|null $storeId
     * @return bool
     */
    public function isEnabled(?string $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get current environment
     *
     * @param string|null $storeId
     * @return string
     */
    public function getEnvironment(?string $storeId = null): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENVIRONMENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: self::ENVIRONMENT_DEVELOPMENT;
    }

    /**
     * Get JavaScript URL for current environment
     *
     * @param string|null $storeId
     * @return string
     */
    public function getJavaScriptUrl(?string $storeId = null): string
    {
        $environment = $this->getEnvironment($storeId);
        $path = sprintf(self::XML_PATH_JS_URL, $environment);
        
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId)
            ?: $this->getDefaultJavaScriptUrl($environment);
    }

    /**
     * Get CSS URL for current environment
     *
     * @param string|null $storeId
     * @return string
     */
    public function getCssUrl(?string $storeId = null): string
    {
        $environment = $this->getEnvironment($storeId);
        $path = sprintf(self::XML_PATH_CSS_URL, $environment);
        
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId)
            ?: $this->getDefaultCssUrl($environment);
    }

    /**
     * Get default JavaScript URL for environment
     *
     * @param string $environment
     * @return string
     */
    private function getDefaultJavaScriptUrl(string $environment): string
    {
        $urls = [
            self::ENVIRONMENT_DEVELOPMENT => 'http://localhost:3000/short-videos/index.js',
            self::ENVIRONMENT_STAGING => 'https://staging.instavid.com/short-videos/index.js',
            self::ENVIRONMENT_PRODUCTION => 'https://d1w3cluksnvflo.cloudfront.net/short-videos/index.js'
        ];

        return $urls[$environment] ?? $urls[self::ENVIRONMENT_DEVELOPMENT];
    }

    /**
     * Get default CSS URL for environment
     *
     * @param string $environment
     * @return string
     */
    private function getDefaultCssUrl(string $environment): string
    {
        $urls = [
            self::ENVIRONMENT_DEVELOPMENT => 'http://localhost:3000/short-videos/index.css',
            self::ENVIRONMENT_STAGING => 'https://staging.instavid.com/short-videos/index.css',
            self::ENVIRONMENT_PRODUCTION => 'https://d1w3cluksnvflo.cloudfront.net/short-videos/index.css'
        ];

        return $urls[$environment] ?? $urls[self::ENVIRONMENT_DEVELOPMENT];
    }

    /**
     * Get default carousel height
     *
     * @param string|null $storeId
     * @return int
     */
    public function getDefaultCarouselHeight(?string $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CAROUSEL_HEIGHT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 594;
    }

    /**
     * Get default carousel autoplay setting
     *
     * @param string|null $storeId
     * @return bool
     */
    public function getDefaultCarouselAutoplay(?string $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CAROUSEL_AUTOPLAY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default carousel loop setting
     *
     * @param string|null $storeId
     * @return bool
     */
    public function getDefaultCarouselLoop(?string $storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CAROUSEL_LOOP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if current environment is development
     *
     * @param string|null $storeId
     * @return bool
     */
    public function isDevelopment(?string $storeId = null): bool
    {
        return $this->getEnvironment($storeId) === self::ENVIRONMENT_DEVELOPMENT;
    }

    /**
     * Check if current environment is staging
     *
     * @param string|null $storeId
     * @return bool
     */
    public function isStaging(?string $storeId = null): bool
    {
        return $this->getEnvironment($storeId) === self::ENVIRONMENT_STAGING;
    }

    /**
     * Check if current environment is production
     *
     * @param string|null $storeId
     * @return bool
     */
    public function isProduction(?string $storeId = null): bool
    {
        return $this->getEnvironment($storeId) === self::ENVIRONMENT_PRODUCTION;
    }

    /**
     * Get all configuration as array
     *
     * @param string|null $storeId
     * @return array
     */
    public function getAllConfig(?string $storeId = null): array
    {
        return [
            'enabled' => $this->isEnabled($storeId),
            'environment' => $this->getEnvironment($storeId),
            'urls' => [
                'js' => $this->getJavaScriptUrl($storeId),
                'css' => $this->getCssUrl($storeId)
            ],
            'carousel' => [
                'default_height' => $this->getDefaultCarouselHeight($storeId),
                'default_autoplay' => $this->getDefaultCarouselAutoplay($storeId),
                'default_loop' => $this->getDefaultCarouselLoop($storeId)
            ]
        ];
    }

    /**
     * Get environment-specific configuration
     *
     * @param string|null $storeId
     * @return array
     */
    public function getEnvironmentConfig(?string $storeId = null): array
    {
        $environment = $this->getEnvironment($storeId);
        
        return [
            'environment' => $environment,
            'is_development' => $this->isDevelopment($storeId),
            'is_staging' => $this->isStaging($storeId),
            'is_production' => $this->isProduction($storeId),
            'urls' => [
                'js' => $this->getJavaScriptUrl($storeId),
                'css' => $this->getCssUrl($storeId)
            ]
        ];
    }
} 