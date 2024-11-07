<?php

namespace M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Command;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock;

class Group extends AbstractBlock
{
    public array $commands = [];
    protected \M2E\TikTokShop\Helper\View\ControlPanel\Command $controlPanelCommandHelper;
    private string $controllerName;
    private string $route;

    public function __construct(
        string $controllerName,
        string $route,
        \M2E\TikTokShop\Helper\View\ControlPanel\Command $controlPanelCommandHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->controlPanelCommandHelper = $controlPanelCommandHelper;
        $this->controllerName = $controllerName;
        $this->route = $route;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('control_panel/tabs/command/group.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->commands = $this->controlPanelCommandHelper->parseGeneralCommandsData(
            $this->controllerName,
            $this->route,
        );

        return parent::_beforeToHtml();
    }

    public function getCommandLauncherHtml(array $commandRow): string
    {
        $href = $commandRow['url'];

        $target = '';
        $commandRow['new_window'] && $target = 'target="_blank"';

        $onClick = '';
        $commandRow['confirm'] && $onClick = "return confirm('{$commandRow['confirm']}');";
        if (!empty($commandRow['prompt']['text']) && !empty($commandRow['prompt']['var'])) {
            $onClick = <<<JS
var result = prompt('{$commandRow['prompt']['text']}');
if (result) window.location.href = $(this).getAttribute('href') + '?{$commandRow['prompt']['var']}=' + result;
return false;
JS;
        }

        $title = $commandRow['title'];

        return <<<HTML
<a href="{$href}" {$target} onclick="{$onClick}" title="{$commandRow['description']}">{$title}</a>
HTML;
    }
}
