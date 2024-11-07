<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Variation\Product\Manage;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer;

class View extends AbstractContainer
{
    private \M2E\TikTokShop\Model\Product $listingProduct;
    private array $filterByIds;

    public function __construct(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $filterByIds = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->listingProduct = $listingProduct;
        $this->filterByIds = $filterByIds;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('add');
    }

    protected function _prepareLayout()
    {
        $gridBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Variation\Product\Manage\View\Grid::class,
                '',
                [
                    'listingProduct' => $this->listingProduct,
                    'filterByIds' => $this->filterByIds,
                ]
            );

        $this->setChild('grid', $gridBlock);

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $helpContent = __('<p>In this Section, you can find all Item Variations and information about them, ' .
            'i.e. price, QTY, status.</p><p>You can use filters across column headers to search ' .
            'and sort the variants.</p>');

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)->setData([
                'content' => $helpContent,
            ]);

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
