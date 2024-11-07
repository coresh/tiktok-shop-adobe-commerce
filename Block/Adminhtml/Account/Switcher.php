<?php

namespace M2E\TikTokShop\Block\Adminhtml\Account;

class Switcher extends \M2E\TikTokShop\Block\Adminhtml\Switcher
{
    /** @var string */
    protected $paramName = 'account';
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->accountRepository = $accountRepository;
    }

    public function getLabel()
    {
        return (string)__('Account');
    }

    protected function loadItems()
    {
        $accounts = $this->accountRepository->getAll(true);
        if (empty($accounts)) {
            $this->items = [];

            return;
        }

        if (count($accounts) < 2) {
            $this->hasDefaultOption = false;
            $this->setIsDisabled();
        }

        $items = [];
        foreach ($accounts as $account) {
            $accountTitle = $this->filterManager->truncate(
                $account->getTitle(),
                ['length' => 15]
            );

            $items['accounts']['value'][] = [
                'value' => $account->getId(),
                'label' => $accountTitle,
            ];
        }

        $this->items = $items;
    }

    private function setIsDisabled(): void
    {
        $this->setData('is_disabled', true);
    }
}
