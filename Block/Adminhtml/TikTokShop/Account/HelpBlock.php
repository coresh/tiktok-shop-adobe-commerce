<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account;

class HelpBlock extends \M2E\TikTokShop\Block\Adminhtml\HelpBlock
{
    public function getContent(): string
    {
        return (string)__(
            '<p>On this Page you can find information about %channel_title Accounts which can be managed via %extension_title.</p><br>
<p>Settings for such configurations as %channel_title Orders along with Magento Order creation conditions,
Unmanaged Listings import including options of Linking them to Magento Products and Moving them
to %extension_title Listings,
etc. can be specified for each Account separately.</p><br>
<p><strong>Note:</strong> %channel_title Account can be deleted only if it is not being used for any of %extension_title Listings.</p>',
            [
                'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
            ]
        );
    }
}
