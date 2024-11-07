<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class SaveCategoryAttributes extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\CategoryAttributeFactory $attributeFactory;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\TikTokShop\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\TikTokShop\Model\Category\CategoryAttributeFactory $attributeFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\TikTokShop\Model\Category\Attribute\Manager $attributeManager
    ) {
        parent::__construct();

        $this->attributeFactory = $attributeFactory;
        $this->dictionaryRepository = $dictionaryRepository;
        $this->attributeManager = $attributeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost()->toArray();

        if (empty($post['dictionary_id'])) {
            $this->getMessageManager()->addError(__('Category not found.'));

            return $this->_redirect('*/*/index');
        }

        $dictionary = $this->dictionaryRepository->get($post['dictionary_id']);
        $allAttributes = array_merge(
            array_values($post['real_attributes'] ?? []),
            array_values($post['virtual_attributes'] ?? []),
            array_values($post['certifications_attributes'] ?? []),
        );

        $attributes = $this->getAttributes($dictionary->getId(), $allAttributes);
        $this->attributeManager->createOrUpdateAttributes($attributes, $dictionary);

        $this->messageManager->addSuccess(__('Category data was saved.'));

        if ($this->getRequest()->getParam('back') === 'edit') {
            return $this->_redirect('*/*/view', ['dictionary_id' => $post['dictionary_id']]);
        }

        if ($this->getRequest()->getParam('back') === 'categories_grid') {
            return $this->_redirect('*/tiktokshop_template_category/index');
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @param int $dictionaryId
     * @param array $inputAttributes
     *
     * @return \M2E\TikTokShop\Model\Category\CategoryAttribute[]
     */
    private function getAttributes(int $dictionaryId, array $inputAttributes): array
    {
        $attributes = [];
        foreach ($inputAttributes as $inputAttribute) {
            $recommendedValues = [];
            if (!empty($inputAttribute['value_tiktokshop_recommended'])) {
                $recommendedValues = $this->getRecommendedValues($inputAttribute['value_tiktokshop_recommended']);
            }

            $attributes[] = $this->attributeFactory->create()->create(
                $dictionaryId,
                $inputAttribute['attribute_type'],
                $inputAttribute['attribute_id'],
                $inputAttribute['attribute_name'],
                $inputAttribute['value_mode'],
                $recommendedValues,
                $inputAttribute['value_custom_value'] ?? '',
                $inputAttribute['value_custom_attribute'] ?? ''
            );
        }

        return $attributes;
    }

    /**
     * @param array|string $inputValues
     *
     * @return string[]
     */
    private function getRecommendedValues($inputValues): array
    {
        if (is_string($inputValues)) {
            $inputValues = [$inputValues];
        }

        $values = [];
        foreach ($inputValues as $value) {
            if (!empty($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
