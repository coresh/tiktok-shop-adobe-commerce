<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category;

use M2E\TikTokShop\Helper\Module;

class View extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    private \M2E\TikTokShop\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dictionary = $dictionary;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('tikTokShopCategoryView');
        $this->_template = Module::IDENTIFIER . '::tiktokshop/category/view.phtml';

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _prepareLayout()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Info $infoBlock */
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Info::class,
            '',
            ['dictionary' => $this->dictionary]
        );

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Edit::class,
            '',
            ['dictionary' => $this->dictionary]
        );

        $this->setChild('info', $infoBlock);
        $this->setChild('edit', $editBlock);

        return parent::_prepareLayout();
    }

    public function getInfoHtml()
    {
        return $this->getChildHtml('info');
    }

    public function getEditHtml()
    {
        return $this->getChildHtml('edit');
    }
}
