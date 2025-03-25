<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ManufacturerConfiguration;

class GetResponsiblePersonPopupHtml extends AbstractManufacturerConfiguration
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
        $responsiblePersonId = $this->getRequest()->getParam('responsible_person_id');
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $account = $this->accountRepository->get($accountId);

        $responsiblePerson = null;

        if (!empty($responsiblePersonId)) {
            $responsiblePerson = $this->complianceService->findResponsiblePersonById($account, $responsiblePersonId);
        }

        $popup = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\ResponsiblePersonPopup::class,
            '',
            ['account' => $account, 'responsiblePerson' => $responsiblePerson],
        );

        $this->setAjaxContent($popup->toHtml());

        return $this->getResult();
    }
}
