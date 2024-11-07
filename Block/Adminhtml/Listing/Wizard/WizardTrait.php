<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard;

trait WizardTrait
{
    private function prepareButtons(
        array $continueButtonData,
        \M2E\TikTokShop\Model\Listing\Wizard\Manager $wizardManager
    ): void {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($wizardManager->hasPreviousStep()) {
            $url = $this->getUrl('*/listing_wizard/back', ['id' => $wizardManager->getWizardId()]);
            $this->addButton('back', [
                'label' => __('Back'),
                'onclick' => sprintf("setLocation('%s')", $url),
                'class' => 'back',
            ]);
        }

        // ---------------------------------------

        $url = $this->getUrl('*/listing_wizard/cancel', ['id' => $wizardManager->getWizardId()]);
        $confirm =
            '<strong>' . __('Are you sure?') . '</strong><br><br>'
            . __('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->addButton(
            'exit_to_listing',
            [
                'label' => __('Cancel'),
                'onclick' => sprintf("confirmSetLocation('%s', '%s');", $confirm, $url),
                'class' => 'action-primary',
            ],
        );

        $this->addButton('next', $continueButtonData);
    }
}
