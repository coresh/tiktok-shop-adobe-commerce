<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Category;

class Search extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private const SEARCH_LIMIT = 20;

    private \M2E\TikTokShop\Model\Category\Search $categorySearch;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Search $categorySearch
    ) {
        parent::__construct();

        $this->categorySearch = $categorySearch;
    }

    public function execute()
    {
        $shopId = (int)$this->getRequest()->getParam('shop_id');
        $searchQuery = $this->getRequest()->getParam('search_query');

        $result = [
            'categories' => [],
            'has_more' => false,
        ];

        if (empty($searchQuery)) {
            $this->setJsonContent($result);

            return $this->getResult();
        }

        $searchResult = $this->categorySearch->process($shopId, $searchQuery, self::SEARCH_LIMIT + 1);

        $result['categories'] = array_map(static function (\M2E\TikTokShop\Model\Category\Search\ResultItem $item) {
            return [
                'id' => $item->categoryId,
                'path' => $item->path,
                'is_invite' => $item->isInviteOnly,
            ];
        }, $searchResult->getAll());

        $result['has_more'] = count($result['categories']) > self::SEARCH_LIMIT;

        $this->setJsonContent($result);

        return $this->getResult();
    }
}
