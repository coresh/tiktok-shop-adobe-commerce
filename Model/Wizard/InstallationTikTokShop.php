<?php

namespace M2E\TikTokShop\Model\Wizard;

use M2E\TikTokShop\Model\Wizard;

class InstallationTikTokShop extends Wizard
{
    /** @var string[] */
    protected $steps = [
        'registration',
        'account',
        'settings',
        'listingTutorial',
    ];

    /**
     * @return string
     */
    public function getNick()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::WIZARD_INSTALLATION_NICK;
    }
}
