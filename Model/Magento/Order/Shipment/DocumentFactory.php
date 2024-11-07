<?php

namespace M2E\TikTokShop\Model\Magento\Order\Shipment;

/**
 * Class \M2E\TikTokShop\Model\Magento\Order\Shipment\DocumentFactory
 */
class DocumentFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var \M2E\TikTokShop\Helper\Factory */
    protected $helperFactory;

    //########################################

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->helperFactory = $helperFactory;
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function create(\Magento\Sales\Model\Order $order, $items = [])
    {
        return $this->resolveFactory()->create($order, $items);
    }

    //########################################

    private function resolveFactory()
    {
        if (version_compare($this->helperFactory->getObject('Magento')->getVersion(), '2.2.0', '<')) {
            return $this->objectManager->get(\Magento\Sales\Model\Order\ShipmentFactory::class);
        }

        return $this->objectManager->get(\Magento\Sales\Model\Order\ShipmentDocumentFactory::class);
    }

    //########################################
}
