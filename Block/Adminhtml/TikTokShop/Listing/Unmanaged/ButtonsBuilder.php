<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged;

use M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\ButtonsBlock;

class ButtonsBuilder extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    public function _construct(): void
    {
        parent::_construct();

        $this->addButton('buttons_block', ['class_name' => ButtonsBlock::class]);
    }
}
