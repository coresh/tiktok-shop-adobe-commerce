<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

use M2E\TikTokShop\Controller\Adminhtml\Context;

class SettingsContinue extends Installation
{
    private \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration $configuration;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Account\Update $accountUpdate;
    private \M2E\TikTokShop\Model\Warehouse\ShippingMappingUpdater $shippingMappingUpdater;

    public function __construct(
        \M2E\TikTokShop\Model\Warehouse\ShippingMappingUpdater $shippingMappingUpdater,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Helper\Component\TikTokShop\Configuration $configuration,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\TikTokShop\Helper\Module\License $licenseHelper
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseHelper,
        );
        $this->configuration = $configuration;
        $this->accountRepository = $accountRepository;
        $this->shippingMappingUpdater = $shippingMappingUpdater;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        $this->configuration->setConfigValues($params);
        $this->saveShippingMapping($params['shipping_provider_mapping'] ?? []);
        $this->activateAccount((int)$params['account_settings']['account_id']);

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    private function saveShippingMapping(array $data): void
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $warehouseId => $warehouseData) {
            $this->shippingMappingUpdater->update((string)$warehouseId, $warehouseData);
        }
    }

    private function activateAccount(int $accountId): void
    {
        $account = $this->accountRepository->get($accountId);
        $account->activate();

        $this->accountRepository->save($account);
    }
}
