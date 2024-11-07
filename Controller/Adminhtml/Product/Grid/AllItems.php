<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Grid;

class AllItems extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('All Items'));

        return $this->getResult();
    }
}
