<?php

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractWizard extends AbstractMain
{
    private \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;
    private \Magento\Framework\Code\NameBuilder $nameBuilder;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct();
        $this->nameBuilder = $nameBuilder;
        $this->magentoHelper = $magentoHelper;
        $this->wizardHelper = $wizardHelper;
        $this->licenseService = $licenseService;
    }

    //########################################

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::tts');
    }

    //########################################

    abstract protected function getNick();

    abstract protected function getMenuRootNodeNick();

    abstract protected function getMenuRootNodeLabel();

    //########################################

    protected function completeAction()
    {
        $this->setStatus(\M2E\TikTokShop\Helper\Module\Wizard::STATUS_COMPLETED);

        $this->_redirect('*/*/index');
    }

    protected function congratulationAction()
    {
        if (!$this->isFinished()) {
            return $this->_redirect('*/*/index');
        }

        $this->magentoHelper->clearMenuCache();

        $this->addContent($this->createCongratulationBlock());

        return $this->getResult();
    }

    protected function createCongratulationBlock()
    {
        return $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Wizard\Congratulation::class);
    }

    protected function indexAction()
    {
        if ($this->isNotStarted() || $this->isActive()) {
            $this->installationAction();

            return;
        }

        return $this->congratulationAction();
    }

    protected function installationAction()
    {
        if ($this->isFinished()) {
            return $this->congratulationAction();
        }

        if ($this->isNotStarted()) {
            $this->setStatus(\M2E\TikTokShop\Helper\Module\Wizard::STATUS_ACTIVE);
        }

        if (!$this->getCurrentStep() || !in_array($this->getCurrentStep(), $this->getSteps())) {
            $this->setStep($this->getFirstStep());
        }

        $this->_forward($this->getCurrentStep());
    }

    protected function registrationAction(\M2E\Core\Model\RegistrationService $registrationService)
    {
        if (
            $registrationService->findUser() !== null
            && $this->licenseService->has()
        ) {
            $this->setStep($this->getNextStep());

            return $this->renderSimpleStep();
        }

        return $this->renderSimpleStep();
    }

    //########################################

    protected function getWizardHelper(): \M2E\TikTokShop\Helper\Module\Wizard
    {
        return $this->wizardHelper;
    }

    // ---------------------------------------

    protected function setStatus($status)
    {
        $this->getWizardHelper()->setStatus($this->getNick(), $status);

        return $this;
    }

    protected function getStatus()
    {
        return $this->getWizardHelper()->getStatus($this->getNick());
    }

    // ---------------------------------------

    protected function setStep($step)
    {
        $this->getWizardHelper()->setStep($this->getNick(), $step);

        return $this;
    }

    protected function getSteps()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getSteps();
    }

    protected function getFirstStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getFirstStep();
    }

    protected function getPrevStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getPrevStep();
    }

    protected function getCurrentStep()
    {
        return $this->getWizardHelper()->getStep($this->getNick());
    }

    protected function getNextStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getNextStep();
    }

    // ---------------------------------------

    protected function isNotStarted()
    {
        return $this->getWizardHelper()->isNotStarted($this->getNick());
    }

    protected function isActive()
    {
        return $this->getWizardHelper()->isActive($this->getNick());
    }

    public function isCompleted()
    {
        return $this->getWizardHelper()->isCompleted($this->getNick());
    }

    public function isSkipped()
    {
        return $this->getWizardHelper()->isSkipped($this->getNick());
    }

    protected function isFinished()
    {
        return $this->getWizardHelper()->isFinished($this->getNick());
    }

    //########################################

    public function setStepAction()
    {
        $step = $this->getRequest()->getParam('step');

        if ($step === null) {
            $this->setJsonContent([
                'type' => 'error',
                'message' => __('Step is invalid'),
            ]);

            return $this->getResult();
        }

        $this->setStep($step);

        $this->setJsonContent([
            'type' => 'success',
        ]);

        return $this->getResult();
    }

    public function setStatusAction()
    {
        $status = $this->getRequest()->getParam('status');

        if ($status === null) {
            $this->setJsonContent([
                'type' => 'error',
                'message' => __('Status is invalid'),
            ]);

            return $this->getResult();
        }

        $this->setStatus($status);

        $this->setJsonContent([
            'type' => 'success',
        ]);

        return $this->getResult();
    }

    //########################################

    protected function renderSimpleStep()
    {
        $this->addContent(
            $this->getLayout()->createBlock(
                $this->nameBuilder->buildClassName([
                    '\M2E\TikTokShop\Block\Adminhtml\Wizard',
                    $this->getNick(),
                    'Installation',
                    $this->getCurrentStep(),
                ])
            )->setData([
                'nick' => $this->getNick(),
            ])
        );

        return $this->getResult();
    }

    //########################################
}
