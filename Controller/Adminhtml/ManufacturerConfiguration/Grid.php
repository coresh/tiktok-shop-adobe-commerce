<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Grid extends AbstractManufacturerConfiguration
{
    public function execute()
    {
        $block = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Grid::class);

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
