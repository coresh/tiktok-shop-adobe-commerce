<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class NewTemplateHtml extends AbstractTemplate
{
    public function execute()
    {
        $nick = $this->getRequest()->getParam('nick');

        $this->setAjaxContent(
            $this->getLayout()->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Template\NewTemplate\Form::class
            )
                 ->setData('nick', $nick)
        );

        return $this->getResult();
    }
}
