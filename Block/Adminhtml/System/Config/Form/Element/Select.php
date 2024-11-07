<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element;

class Select extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Select
{
    use AbstractElementTrait;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setData('style', 'width: auto;');
    }
}
