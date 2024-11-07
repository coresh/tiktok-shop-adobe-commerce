<?php

namespace M2E\TikTokShop\Controller\Adminhtml\General;

class IsAttributeCodeUnique extends \M2E\TikTokShop\Controller\Adminhtml\AbstractGeneral
{
    /** @var \Magento\Eav\Model\Entity\AttributeFactory */
    private $attributeFactory;

    /** @var \Magento\Catalog\Model\ProductFactory */
    private $catalogProductFactory;

    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->attributeFactory = $attributeFactory;
        $this->catalogProductFactory = $catalogProductFactory;
    }

    public function execute()
    {
        $attributeObj = $this->attributeFactory->create()->loadByCode(
            $this->catalogProductFactory->create()->getResource()->getTypeId(),
            $this->getRequest()->getParam('code')
        );

        $this->setJsonContent([
            'status' => $attributeObj->getId() === null,
        ]);

        return $this->getResult();
    }
}
