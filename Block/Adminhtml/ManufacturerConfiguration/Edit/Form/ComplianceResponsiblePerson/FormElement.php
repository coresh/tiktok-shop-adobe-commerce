<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Edit\Form\ComplianceResponsiblePerson;

class FormElement extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getSavedResponsiblePersons(): array
    {
        $persons = $this->getData('saved_responsible_person_ids');
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

    public function renderAttributesDropdown(int $index)
    {
        $select = $this->_factoryElement->create(
            \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Select::class,
            [
                'data' => [
                    'name' => "responsible_person_ids[$index]",
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
