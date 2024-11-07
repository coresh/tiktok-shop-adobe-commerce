<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class ItemsByIssue extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    /**
     * @ingeritdoc
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->setAjaxContent(
                $this->getLayout()->createBlock(
                    \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByIssue\Grid::class
                )
            );

            return $this->getResult();
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\ItemsByIssue::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Items By Issue'));

        return $this->getResult();
    }
}
