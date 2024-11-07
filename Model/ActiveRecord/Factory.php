<?php

namespace M2E\TikTokShop\Model\ActiveRecord;

/**
 * Class \M2E\TikTokShop\Model\ActiveRecord\Factory
 */
class Factory
{
    /** @var \M2E\TikTokShop\Helper\Factory */
    protected $helperFactory;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    //########################################

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helperFactory = $helperFactory;
        $this->objectManager = $objectManager;
    }

    //########################################

    /**
     * @param string $modelName
     *
     * @return \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getObject($modelName)
    {
        // fix for Magento2 sniffs that forcing to use ::class
        $modelName = str_replace('_', '\\', $modelName);

        $model = $this->objectManager->create('\M2E\TikTokShop\Model\\' . $modelName);

        if (!$model instanceof \M2E\TikTokShop\Model\ActiveRecord\AbstractModel) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                __('%1 doesn\'t extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel', $modelName)
            );
        }

        return $model;
    }

    /**
     * @param string $modelName
     * @param mixed $value
     * @param null|string $field
     * @param boolean $throwException
     *
     * @return \M2E\TikTokShop\Model\ActiveRecord\AbstractModel|NULL
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        try {
            return $this->getObject($modelName)->load($value, $field);
        } catch (\M2E\TikTokShop\Model\Exception\Logic $e) {
            if ($throwException) {
                throw $e;
            }

            return null;
        }
    }
}
