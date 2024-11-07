<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Inspection;

use M2E\TikTokShop\Controller\Adminhtml\ControlPanel\AbstractMain;
use M2E\TikTokShop\Model\ControlPanel\Inspection\Repository;
use M2E\TikTokShop\Model\ControlPanel\Inspection\Processor;

class CheckInspection extends AbstractMain
{
    private Processor $processor;
    private Repository $repository;

    //########################################

    public function __construct(
        Repository $repository,
        Processor $processor,
        \M2E\TikTokShop\Model\Module $module
    ) {
        parent::__construct($module);
        $this->repository = $repository;
        $this->processor = $processor;
    }

    public function execute()
    {
        $inspectionTitle = $this->getRequest()->getParam('title');

        $definition = $this->repository->getDefinition($inspectionTitle);
        $result = $this->processor->process($definition);

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
