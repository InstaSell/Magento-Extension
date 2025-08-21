<?php
namespace Instavid\ShoppableVideos\Block\Widget;

/**
 * Widget Interface for Instavid Shoppable Videos
 * 
 * All future widgets (Stories, Video PiP, Banners, etc.) will implement this interface
 * to ensure consistency and maintainability.
 */
interface WidgetInterface
{
    /**
     * Get the widget type identifier
     *
     * @return string
     */
    public function getWidgetType(): string;

    /**
     * Get the widget display name
     *
     * @return string
     */
    public function getWidgetName(): string;

    /**
     * Get the widget description
     *
     * @return string
     */
    public function getWidgetDescription(): string;

    /**
     * Get the widget template path
     *
     * @return string
     */
    public function getTemplatePath(): string;

    /**
     * Get widget configuration parameters
     *
     * @return array
     */
    public function getWidgetParameters(): array;

    /**
     * Validate widget configuration
     *
     * @param array $data
     * @return bool
     */
    public function validateConfiguration(array $data): bool;

    /**
     * Get widget CSS classes
     *
     * @return string
     */
    public function getWidgetCssClasses(): string;

    /**
     * Get widget JavaScript configuration
     *
     * @return array
     */
    public function getJavaScriptConfig(): array;
} 