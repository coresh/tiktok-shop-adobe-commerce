<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class GetEditedCategoryInfo extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Manager $dictionaryManager;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Manager $dictionaryManager
    ) {
        parent::__construct();

        $this->dictionaryManager = $dictionaryManager;
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $shopId = $this->getRequest()->getParam('shop_id');

        if (empty($categoryId) || empty($shopId)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Invalid input');
        }

        try {
            $dictionary = $this->dictionaryManager->getOrCreateDictionary((int)$shopId, $categoryId);
        } catch (\Throwable $e) {
            $this->setJsonContent([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => true,
            'dictionary_id' => $dictionary->getId(),
            'is_all_required_attributes_filled' => $dictionary->isAllRequiredAttributesFilled(),
            'path' => $dictionary->getPath(),
            'value' => $dictionary->getCategoryId(),
        ]);

        return $this->getResult();
    }
}
