<?php

namespace M2E\TikTokShop\Model;

class Wizard extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper;

    protected $steps = [];

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->wizardHelper = $wizardHelper;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Wizard::class);
    }

    //########################################

    public function isActive(): bool
    {
        return true;
    }

    /**
     * @return null
     */
    public function getNick()
    {
        return null;
    }

    //########################################

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    public function getFirstStep()
    {
        return reset($this->steps);
    }

    // ---------------------------------------

    public function getPrevStep()
    {
        $currentStep = $this->wizardHelper->getStep($this->getNick());
        $prevStepIndex = array_search($currentStep, $this->steps) - 1;

        return isset($this->steps[$prevStepIndex]) ? $this->steps[$prevStepIndex] : false;
    }

    public function getNextStep()
    {
        $currentStep = $this->wizardHelper->getStep($this->getNick());
        $nextStepIndex = array_search($currentStep, $this->steps) + 1;

        return isset($this->steps[$nextStepIndex]) ? $this->steps[$nextStepIndex] : false;
    }
}
