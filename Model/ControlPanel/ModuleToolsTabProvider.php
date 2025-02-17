<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel;

class ModuleToolsTabProvider implements \M2E\Core\Model\ControlPanel\ModuleTools\TabProviderInterface
{
    public function getExtensionModuleName(): string
    {
        return \M2E\TikTokShop\Model\ControlPanel\Extension::NAME;
    }

    public function getTabs(): array
    {
        return [
            new \M2E\Core\Model\ControlPanel\ModuleToolsTab(
                'magento',
                'Magento',
                'controlPanel_tools/magento',
                \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Tools\Magento::class
            ),
            new \M2E\Core\Model\ControlPanel\ModuleToolsTab(
                'integration',
                'Integration',
                'controlPanel_module/integration',
                \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Module\Integration::class
            ),
        ];
    }
}
