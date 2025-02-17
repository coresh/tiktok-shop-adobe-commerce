<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Tools\TikTokShop;

use M2E\Core\Model\ControlPanel\Inspection\FixerInterface;
use M2E\Core\Model\ControlPanel\Inspection\InspectorInterface;
use M2E\TikTokShop\Controller\Adminhtml\Context;
use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractCommand;
use Magento\Framework\Component\ComponentRegistrar;
use M2E\TikTokShop\Helper\Module;

class Install extends AbstractCommand
{
    protected \Magento\Framework\Filesystem\Driver\File $filesystemDriver;
    protected \Magento\Framework\Filesystem $fileSystem;
    protected \Magento\Framework\Filesystem\File\ReadFactory $fileReaderFactory;
    protected ComponentRegistrar $componentRegistrar;
    protected \M2E\Core\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory;
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private \M2E\Core\Model\ControlPanel\InspectionTaskCollection $taskCollection;
    private \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver;

    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReaderFactory,
        ComponentRegistrar $componentRegistrar,
        \M2E\TikTokShop\Helper\View\ControlPanel $controlPanelHelper,
        Context $context,
        \M2E\Core\Model\ControlPanel\Inspection\HandlerFactory $handlerFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\Core\Model\ControlPanel\InspectionTaskCollection $taskCollection,
        \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver
    ) {
        parent::__construct($controlPanelHelper, $context);

        $this->filesystemDriver = $filesystemDriver;
        $this->fileSystem = $filesystem;
        $this->fileReaderFactory = $fileReaderFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->handlerFactory = $handlerFactory;
        $this->serverClient = $serverClient;
        $this->taskCollection = $taskCollection;
        $this->currentExtensionResolver = $currentExtensionResolver;
    }

    public function fixColumnAction()
    {
        $repairInfo = $this->getRequest()->getPost('repair_info');

        if (empty($repairInfo)) {
            return;
        }

        foreach ($repairInfo as $item) {
            $columnsInfo[] = (array)\M2E\Core\Helper\Json::decode($item);
        }

        $currentExtension = $this->currentExtensionResolver->get();
        $definition = $this->taskCollection->findTaskForExtension(
            $currentExtension->getModuleName(),
            'TablesStructureValidity'
        );
        if ($definition === null) {
            return;
        }

        /** @var FixerInterface&InspectorInterface $inspector */
        $inspector = $this->handlerFactory->create($definition);

        foreach ($columnsInfo as $columnInfo) {
            $inspector->fix($columnInfo);
        }
    }

    public function filesDiffAction(): string
    {
        $filePath = base64_decode($this->getRequest()->getParam('filePath', ''));
        $originalPath = base64_decode($this->getRequest()->getParam('originalPath', ''));

        $basePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, Module::IDENTIFIER);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $filePath;

        $params = [
            'content' => '',
            'path' => $originalPath ? $originalPath : $filePath,
        ];

        if ($this->filesystemDriver->isExists($fullPath)) {
            /** @var \Magento\Framework\Filesystem\File\Read $fileReader */
            $fileReader = $this->fileReaderFactory->create($fullPath, $this->filesystemDriver);
            $params['content'] = $fileReader->readAll();
        }

        $command = new \M2E\Core\Model\Server\Connector\System\FilesGetDiffCommand(
            $params['content'],
            $params['path']
        );
        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->serverClient->process($command);

        $responseData = $response->getResponseData();

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Difference
    <span style="color: #808080; font-size: 15px;">({$filePath})</span>
</h2>
<br/>
HTML;

        if (isset($responseData['html'])) {
            $html .= $responseData['html'];
        } else {
            $html .= '<h1>&nbsp;&nbsp;No file on server</h1>';
        }

        return $html;
    }

    /**
     * @title "Static Content Deploy"
     * @description "Static Content Deploy"
     */
    public function staticContentDeployAction(): string
    {
        $magentoRoot = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
                                        ->getAbsolutePath();

        return '<pre>' . call_user_func(
            'shell_exec',
            'php ' . $magentoRoot . DIRECTORY_SEPARATOR . 'bin/magento setup:static-content:deploy'
        );
    }

    /**
     * @title "Run Magento Compilation"
     * @description "Run Magento Compilation"
     */
    public function runCompilationAction(): string
    {
        $magentoRoot = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
                                        ->getAbsolutePath();

        return '<pre>' . call_user_func(
            'shell_exec',
            'php ' . $magentoRoot . DIRECTORY_SEPARATOR . 'bin/magento setup:di:compile'
        );
    }

    private function getEmptyResultsHtml($messageText): string
    {
        $backUrl = $this->controlPanelHelper->getPageOverviewTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }
}
