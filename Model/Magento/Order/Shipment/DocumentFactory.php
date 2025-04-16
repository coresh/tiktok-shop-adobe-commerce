<?php

namespace M2E\TikTokShop\Model\Magento\Order\Shipment;

/**
 * Class \M2E\TikTokShop\Model\Magento\Order\Shipment\DocumentFactory
 */
class DocumentFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    //########################################

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
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
        /** @var \M2E\Core\Helper\Magento $helper */
        $helper = $this->objectManager->get(\M2E\Core\Helper\Magento::class);

        if (version_compare($helper->getVersion(), '2.2.0', '<')) {
            return $this->objectManager->get(\Magento\Sales\Model\Order\ShipmentFactory::class);
        }

        return $this->objectManager->get(\Magento\Sales\Model\Order\ShipmentDocumentFactory::class);
    }

    //########################################
}
