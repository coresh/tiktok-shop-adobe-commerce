<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;

class CheckInspection extends AbstractMain
{
    private \M2E\Core\Model\ControlPanel\Inspection\Processor $processor;
    private \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver;

    public function __construct(
        \M2E\Core\Model\ControlPanel\CurrentExtensionResolver $currentExtensionResolver,
        \M2E\Core\Model\ControlPanel\Inspection\Processor $processor
    ) {
        parent::__construct();
        $this->processor = $processor;
        $this->currentExtensionResolver = $currentExtensionResolver;
    }

    public function execute()
    {
        $currentExtension = $this->currentExtensionResolver->get();
        $result = $this->processor->process($currentExtension, $this->getRequest()->getParam('title'));

        $isSuccess = true;
        $metadata = '';
        $message = __('Success');

        if ($result->isSuccess()) {
            $issues = $result->getIssues();

            if (!empty($issues)) {
                $isSuccess = false;
                $lastIssue = end($issues);

                $metadata = $lastIssue->getMetadata();
                $message = $lastIssue->getMessage();
            }
        } else {
            $message = $result->getErrorMessage();
            $isSuccess = false;
        }

        $this->setJsonContent([
            'result' => $isSuccess,
            'metadata' => $metadata,
            'message' => $message,
        ]);

        return $this->getResult();
    }
}
