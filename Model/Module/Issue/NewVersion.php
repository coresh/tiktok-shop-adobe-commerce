<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Module\Issue;

use M2E\TikTokShop\Model\Issue\DataObject as Issue;

class NewVersion implements \M2E\TikTokShop\Model\Issue\LocatorInterface
{
    private \M2E\TikTokShop\Model\Issue\DataObjectFactory $issueFactory;
    private \M2E\TikTokShop\Model\Module $module;

    public function __construct(
        \M2E\TikTokShop\Model\Issue\DataObjectFactory $issueFactory,
        \M2E\TikTokShop\Model\Module $module
    ) {
        $this->issueFactory = $issueFactory;
        $this->module = $module;
    }

    public function getIssues(): array
    {
        if (!$this->isNeedProcess()) {
            return [];
        }

        return [$this->getIssue()];
    }

    /**
     * @return bool
     */
    private function isNeedProcess(): bool
    {
        if (!$this->module->hasLatestVersion()) {
            return false;
        }

        $publicVersion = $this->module->getPublicVersion();
        $latestVersion = $this->module->getLatestVersion();

        if (version_compare($latestVersion, $publicVersion, '>')) {
            return true;
        }

        return false;
    }

    private function getIssue(): Issue
    {
        $title = $this->module->getName();

        $text = (string)__(
            "A new version of M2E TikTok Shop Connect is now available! Upgrade now to access the latest features and improvements."
        );

        return $this->issueFactory->createNoticeDataObject($title, $text, null);
    }
}
