<?php

namespace M2E\TikTokShop\Controller\Adminhtml\General;

class GetCreateAttributeHtmlPopup extends \M2E\TikTokShop\Controller\Adminhtml\AbstractGeneral
{
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();

        /** @var \M2E\TikTokShop\Block\Adminhtml\General\CreateAttribute $block */
        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\General\CreateAttribute::class);
        $block->setData('handler_id', $post['handler_id']);

        if (isset($post['allowed_attribute_types'])) {
            $block->setData('allowed_types', explode(',', $post['allowed_attribute_types']));
        }

        if (isset($post['apply_to_all_attribute_sets']) && !$post['apply_to_all_attribute_sets']) {
            $block->setData('apply_to_all', false);
        }

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
