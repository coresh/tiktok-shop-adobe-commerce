<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Index extends AbstractManufacturerConfiguration
{
    public function execute()
    {
        $gridBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Grid::class);

        $this->setAjaxContent($gridBlock);

        return $this->getResult();
    }
}
