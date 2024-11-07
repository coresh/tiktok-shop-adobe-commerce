<?php

namespace M2E\TikTokShop\Plugin\Product\Action;

use Magento\Catalog\Model\Product\Action as ProductAction;

/**
 * Class \M2E\TikTokShop\Plugin\Product\Action\BulkUpdate
 */
class BulkUpdate extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    /** @var \Magento\Framework\Event\ManagerInterface */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($helperFactory);
    }

    /**
     * Magento Removed some events (plugins must be used instead): catalog_product_website_update_before
     * Programmed with compatibility with M1 version - just fire corresponding event
     */
    public function aroundUpdateWebsites($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('updateWebsites', $interceptor, $callback, $arguments);
    }

    public function processUpdateWebsites($interceptor, \Closure $callback, array $arguments)
    {
        $productIds = $arguments[0];
        $websiteIds = $arguments[1];
        $type = $arguments[2];

        $this->eventManager->dispatch(
            'catalog_product_website_update_before',
            [
                'product_ids' => $productIds,
                'website_ids' => $websiteIds,
                'action' => $type,
            ]
        );

        return $callback(...$arguments);
    }

    //########################################
}
