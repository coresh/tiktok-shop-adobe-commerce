<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Template;

class CheckMessages extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Template\SellingFormat\Repository $sellingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->sellingRepository = $sellingRepository;
        $this->storeManager = $storeManager;
        $this->shopRepository = $shopRepository;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $nick = $this->getRequest()->getParam('nick');
        $data = $this->getRequest()->getParam($nick);

        $template = null;
        $templateData = $data ?? [];

        if ($nick == \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            $template = $this->sellingRepository->find($id);
        }

        if ($template !== null && $template->getId()) {
            $templateData = $template->getData();
        }

        if ($template === null || empty($templateData)) {
            $this->setJsonContent(['messages' => '']);

            return $this->getResult();
        }

        $shop = $this->shopRepository->get((int)$this->getRequest()->getParam('shop_id'));
        $store = $this->storeManager->getStore((int)$this->getRequest()->getParam('store_id'));

        /** @var \M2E\TikTokShop\Block\Adminhtml\Template\SellingFormat\Messages $messagesBlock */
        $messagesBlock = $this->getLayout()
                              ->createBlock(
                                  \M2E\TikTokShop\Block\Adminhtml\Template\SellingFormat\Messages::class,
                                  '',
                                  [
                                      'shop'  => $shop,
                                      'store' => $store
                                  ]
                              );

        $this->setJsonContent(['messages' => $messagesBlock->getMessagesHtml()]);

        return $this->getResult();
    }
}
