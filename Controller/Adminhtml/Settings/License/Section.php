<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Settings\License;

class Section extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    public function execute()
    {
        $content = $this->getLayout()
                        ->createBlock(\M2E\TikTokShop\Block\Adminhtml\System\Config\Sections\License::class);
        $this->setAjaxContent($content);

        return $this->getResult();
    }
}
