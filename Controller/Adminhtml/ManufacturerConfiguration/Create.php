<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class Create extends AbstractManufacturerConfiguration
{
    public function execute()
    {
        $block = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\CreateOrEdit::class);

        $this->addContent($block);

        $this->getResult()->getConfig()->getTitle()->prepend(__('Create'));

        return $this->getResult();
    }
}
