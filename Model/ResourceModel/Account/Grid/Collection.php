<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Account\Grid;

use M2E\TikTokShop\Model\ResourceModel\SearchResultTrait;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection implements SearchResultInterface
{
    use SearchResultTrait;

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\TikTokShop\Model\ResourceModel\Account::class,
        );
    }
}
