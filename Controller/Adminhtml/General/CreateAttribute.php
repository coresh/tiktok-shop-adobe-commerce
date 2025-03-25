<?php

namespace M2E\TikTokShop\Controller\Adminhtml\General;

class CreateAttribute extends \M2E\TikTokShop\Controller\Adminhtml\AbstractGeneral
{
    /** @var \Magento\Eav\Model\Entity\Attribute\SetFactory */
    private $entityAttributeSetFactory;
    private \M2E\Core\Model\Magento\Attribute\BuilderFactory $builderFactory;
    private \M2E\Core\Model\Magento\Attribute\RelationFactory $relationFactory;

    public function __construct(
        \M2E\Core\Model\Magento\Attribute\BuilderFactory $builderFactory,
        \M2E\Core\Model\Magento\Attribute\RelationFactory $relationFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $entityAttributeSetFactory
    ) {
        parent::__construct();

        $this->entityAttributeSetFactory = $entityAttributeSetFactory;
        $this->builderFactory = $builderFactory;
        $this->relationFactory = $relationFactory;
    }

    public function execute()
    {
        $model = $this->builderFactory->create();

        $model->setLabel($this->getRequest()->getParam('store_label'))
              ->setCode($this->getRequest()->getParam('code'))
              ->setInputType($this->getRequest()->getParam('input_type'))
              ->setDefaultValue($this->getRequest()->getParam('default_value'))
              ->setScope($this->getRequest()->getParam('scope'));

        $attributeResult = $model->save();

        if (!isset($attributeResult['result']) || !$attributeResult['result']) {
            $this->setJsonContent($attributeResult);

            return $this->getResult();
        }

        foreach ($this->getRequest()->getParam('attribute_sets', []) as $seId) {
            /** @var \Magento\Eav\Model\Entity\Attribute\Set $set */
            $set = $this->entityAttributeSetFactory->create()->load($seId);

            if (!$set->getId()) {
                continue;
            }

            $model = $this->relationFactory->create();
            $model->setAttributeObj($attributeResult['obj'])
                  ->setAttributeSetObj($set);

            $setResult = $model->save();

            if (!isset($setResult['result']) || !$setResult['result']) {
                $this->setJsonContent($setResult);

                return $this->getResult();
            }
        }

        unset($attributeResult['obj']);
        $this->setJsonContent($attributeResult);

        return $this->getResult();
    }
}
