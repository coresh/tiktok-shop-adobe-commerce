<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class EditCategory extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\ViewFactory */
    private $viewFactory;
    /** @var \M2E\TikTokShop\Model\Category\Dictionary\Manager */
    private $dictionaryManager;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\ViewFactory $viewFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Manager $dictionaryManager
    ) {
        parent::__construct();

        $this->viewFactory = $viewFactory;
        $this->dictionaryManager = $dictionaryManager;
    }

    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $shopId = $this->getRequest()->getParam('shop_id');
        $dictionary = $this->dictionaryManager->getOrCreateDictionary((int)$shopId, $categoryId);

        $block = $this->viewFactory->create($this->getLayout(), $dictionary);
        $this->addContent($block);
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Edit Category'));

        return $this->getResult();
    }
}
