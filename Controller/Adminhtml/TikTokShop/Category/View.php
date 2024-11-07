<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class View extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\ViewFactory $viewFactory;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\ViewFactory $viewFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository
    ) {
        parent::__construct();

        $this->viewFactory = $viewFactory;
        $this->dictionaryRepository = $dictionaryRepository;
    }

    public function execute()
    {
        /**
         * tabs widget makes an redundant ajax call for tab content by clicking on it even when tab is just a link
         */
        if ($this->isAjax()) {
            return;
        }

        $dictionaryId = $this->getRequest()->getParam('dictionary_id');
        $dictionary = $this->dictionaryRepository->find((int)$dictionaryId);

        if ($dictionary === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Category not found');
        }

        $block = $this->viewFactory->create($this->getLayout(), $dictionary);
        $this->addContent($block);
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Edit Category'));

        return $this->getResult();
    }
}
