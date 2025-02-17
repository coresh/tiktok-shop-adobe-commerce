<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Compliance\Edit\Form\ComplianceResponsiblePerson;

use M2E\TikTokShop\Model\Account\Repository as AccountRepository;
use M2E\TikTokShop\Model\Template\Compliance\ComplianceService;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class FormElement extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    private ComplianceService $complianceService;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        AccountRepository $accountRepository,
        ComplianceService $complianceService,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        $this->accountRepository = $accountRepository;
        $this->complianceService = $complianceService;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    protected function getResponsiblePersons(): array
    {
        $accountId = $this->getAccountId();
        $persons = [];

        if (empty($accountId)) {
            return $persons;
        }

        $account = $this->accountRepository->get($accountId);

        foreach ($this->complianceService->getAllResponsiblePersons($account, false)->getAll() as $responsiblePerson) {
            $persons[] = [
                'id' => $responsiblePerson->id,
                'title' => sprintf('%s (%s)', $responsiblePerson->name, $responsiblePerson->email),
            ];
        }

        return $persons;
    }

    public function getSavedResponsiblePersons(): array
    {
        $persons = json_decode($this->getData('saved_responsible_person_ids'));
        if (empty($persons)) {
            return [''];
        }

        return $persons;
    }

    public function getAccountId(): ?int
    {
        $accountId = $this->getData('account_id');
        if (empty($accountId)) {
            return null;
        }

        return (int)$accountId;
    }

    public function renderAttributesDropdown(int $index, string $selectedValue)
    {
        $selectedValue = trim(str_replace('"', '', $selectedValue));

        $values = [];

        foreach ($this->getResponsiblePersons() as $attribute) {
            $values[] = [
                'value' => $attribute['id'],
                'label' => $attribute['title'],
            ];
        }

        foreach ($values as &$value) {
            if ($value['value'] === $selectedValue) {
                $value['attrs']['selected'] = 'selected';
            }
        }

        $select = $this->_factoryElement->create(
            \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Select::class,
            [
                'data' => [
                    'name' => "compliance[responsible_person_ids][$index]",
                    'values' => $values,
                ],
            ]
        );

        $select->setId('responsible_person_id_' . $index);
        $select->setForm($this->getForm());

        return $select->toHtml();
    }

    public function renderRemoveRowButton(int $index): string
    {
        $style = '';
        if ($index < 1) {
            $style = 'style="display:none"';
        }

        return sprintf(
            '<button type="button" class="action-primary %s" %s><span>%s</span></button>',
            'remove_row',
            $style,
            __('Remove')
        );
    }

    public function renderAddRowButton(): string
    {
        return sprintf(
            '<button type="button" class="action-primary %s"><span>%s</span></button>',
            'add_row',
            __('Add More')
        );
    }

    public function renderResponsiblePersonLinksHtml(bool $isNew, int $index): string
    {
        $label = $isNew ? (string)__('Add New') : __('View / Edit');
        $param = $isNew ? '1' : '0';

        return sprintf(
            '<a href="#" class="manufacturer_details" data-is-new="%s" data-index="%s">%s</a>',
            $param,
            $index,
            $label
        );
    }

    public function renderRefreshButtonHtml(int $index): string
    {
        if ($index > 0) {
            return '';
        }

        return sprintf(
            '<button type="button" class="refresh_status refresh_responsible_persons primary">%s</button>',
            __('Refresh')
        );
    }

    /**
     * @param string[] $actions
     *
     * @return string
     */
    public function createButtonsBlock(array $actions): string
    {
        $formattedActions = [];
        /** @var string $action */
        foreach ($actions as $action) {
            if (empty($action)) {
                continue;
            }

            $formattedActions[] = sprintf('<span class="action">%s</span>', $action);
        }

        return implode(' ', $formattedActions);
    }
}
