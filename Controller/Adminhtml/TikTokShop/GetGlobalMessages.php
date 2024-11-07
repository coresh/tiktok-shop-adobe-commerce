<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

class GetGlobalMessages extends AbstractMain
{
    public function execute()
    {
        if ($this->getCustomViewHelper()->isInstallationWizardFinished()) {
            $this->addLicenseNotifications();
        }

        $this->addCronErrorMessage();
        $this->getCustomViewControllerHelper()->addMessages();

        $messages = $this->getMessageManager()->getMessages(
            true,
            \M2E\TikTokShop\Controller\Adminhtml\AbstractBase::GLOBAL_MESSAGES_GROUP,
        )->getItems();

        foreach ($messages as &$message) {
            $message = [$message->getType() => $message->getText()];
        }

        $this->setJsonContent($messages);

        return $this->getResult();
    }
}
