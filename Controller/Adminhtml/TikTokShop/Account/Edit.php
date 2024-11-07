<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;

class Edit extends AbstractAccount
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient
    ) {
        parent::__construct();

        $this->serverClient = $serverClient;
        $this->accountRepository = $accountRepository;
    }

    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $account = null;
        if ($id = $this->getRequest()->getParam('id')) {
            $account = $this->accountRepository->find((int)$id);
        }

        if ($account === null && $id) {
            $this->messageManager->addError(__('Account does not exist.'));

            return $this->_redirect('*/tiktokshop_account');
        }

        if ($account !== null) {
            $this->addLicenseMessage($account);
        }

        $headerTextEdit = __('Edit Account');
        $headerTextAdd = __('Add Account');

        if ($account && $account->getId()) {
            $headerText = $headerTextEdit;
            $headerText .= ' "' . \M2E\TikTokShop\Helper\Data::escapeHtml($account->getTitle()) . '"';
        } else {
            $headerText = $headerTextAdd;
        }

        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend($headerText);

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Tabs $tabsBlock */
        $tabsBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Tabs::class, '', [
                'account' => $account,
            ]);
        $this->addLeft($tabsBlock);

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit $contentBlock */
        $contentBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit::class, '', [
                'account' => $account,
            ]);
        $this->addContent($contentBlock);

        return $this->getResultPage();
    }

    private function addLicenseMessage(\M2E\TikTokShop\Model\Account $account): void
    {
        try {
            $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get\InfoCommand(
                $account->getServerHash(),
            );
            /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get\Status $status */
            $status = $this->serverClient->process($command);
        } catch (\Throwable $e) {
            return;
        }

        if ($status->isActive()) {
            return;
        }

        $this->addExtendedErrorMessage(
            __(
                'Work with this Account is currently unavailable for the following reason: <br/> %error_message',
                ['error_message' => $status->getNote()],
            ),
        );
    }
}
