<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product;

use M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Add\SourceMode as SourceModeBlock;

class Review extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    private $source;
    private \M2E\TikTokShop\Model\Listing $listing;

    public function __construct(
        \M2E\TikTokShop\Model\Listing $listing,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->listing = $listing;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingProductReview');
        $this->setTemplate('tiktokshop/listing/product/review.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $url = $this->getUrl('*/tiktokshop_listing/view', [
            'id' => $this->getRequest()->getParam('id'),
        ]);
        $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData([
                                'id' => __('go_to_the_listing'),
                                'label' => __('Go To The Listing'),
                                'onclick' => 'setLocation(\'' . $url . '\');',
                                'class' => 'primary',
                            ]);
        $this->setChild('review', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/tiktokshop_listing/view', [
            'id' => $this->getRequest()->getParam('id'),
            'do_list' => true,
        ]);
        $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData([
                                'label' => __('List Added Products Now'),
                                'onclick' => 'setLocation(\'' . $url . '\');',
                                'class' => 'primary',
                            ]);
        $this->getRequest()->getParam('disable_list', false) && $buttonBlock->setData('style', 'display: none');
        $this->setChild('save_and_list', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        if ($this->getSource() === SourceModeBlock::MODE_OTHER) {
            $url = $this->getUrl('*/tiktokshop_listing_unmanaged/index');
            $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                                ->setData([
                                    'label' => __('Back to Unmanaged Items'),
                                    'onclick' => 'setLocation(\'' . $url . '\');',
                                    'class' => 'primary go',
                                ]);
            $this->setChild('back_to_listing_other', $buttonBlock);
        }
        // ---------------------------------------
    }

    public function setSource(string $value): void
    {
        $this->source = $value;
    }

    public function getSource()
    {
        return $this->source;
    }
}
