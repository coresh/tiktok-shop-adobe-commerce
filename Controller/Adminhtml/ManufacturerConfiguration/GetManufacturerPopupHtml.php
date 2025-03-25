<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class GetManufacturerPopupHtml extends AbstractManufacturerConfiguration
{
    private \M2E\TikTokShop\Model\Template\Compliance\ComplianceService $complianceService;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Template\Compliance\ComplianceService $complianceService,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\TikTokShop\Template\Manager $templateManager
    ) {
        parent::__construct($templateManager);

        $this->accountRepository = $accountRepository;
        $this->complianceService = $complianceService;
    }

    public function execute()
    {
        $manufacturerId = $this->getRequest()->getParam('manufacturer_id');
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $account = $this->accountRepository->get($accountId);

        $manufacturer = null;

        if (!empty($manufacturerId)) {
            $manufacturer = $this->complianceService->findManufacturerById($account, $manufacturerId);
        }

        $manufacturerPopup = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\ManufacturerPopup::class,
            '',
            ['account' => $account, 'manufacturer' => $manufacturer],
        );

        $this->setAjaxContent($manufacturerPopup->toHtml());

        return $this->getResult();
    }
}
