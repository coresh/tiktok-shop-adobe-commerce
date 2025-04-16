<?php

namespace M2E\TikTokShop\Helper\View;

class TikTokShop
{
    public const NICK = 'tiktokshop';

    public const WIZARD_INSTALLATION_NICK = 'installationTikTokShop';
    public const MENU_ROOT_NODE_NICK = 'M2E_TikTokShop::tts';

    /** @var \M2E\TikTokShop\Helper\Module\Wizard */
    private $wizard;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Wizard $wizard
    ) {
        $this->wizard = $wizard;
    }

    // ----------------------------------------

    /**
     * @return string
     */
    public static function getWizardInstallationNick(): string
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    /**
     * @return bool
     */
    public function isInstallationWizardFinished(): bool
    {
        return $this->wizard->isFinished(
            self::getWizardInstallationNick()
        );
    }
}
