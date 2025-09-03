<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Category;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\AbstractCategory
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Categories'));

        $content = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category::class);
        $this->addContent($content);

        return $this->getResultPage();
    }
}
