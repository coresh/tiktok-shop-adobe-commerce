<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View;

class Info extends \M2E\TikTokShop\Block\Adminhtml\Widget\Info
{
    private \M2E\TikTokShop\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        \Magento\Framework\Math\Random $random,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($random, $context, $data);

        $this->dictionary = $dictionary;
    }

    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Shop'),
                    'value' => $this->dictionary->getShop()->getShopName(),
                ],
                [
                    'label' => __('Category'),
                    'value' => $this->dictionary->getPathWithCategoryId(),
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /*
     * To get "Category" block in center of screen
     */
    public function getInfoPartWidth($index)
    {
        if ($index === 0) {
            return '33%';
        }

        return '66%';
    }

    public function getInfoPartAlign($index)
    {
        return 'left';
    }
}
