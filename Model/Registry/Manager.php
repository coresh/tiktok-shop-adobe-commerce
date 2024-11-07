<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Registry;

class Manager
{
    private \M2E\TikTokShop\Model\RegistryFactory $registryFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Registry $registryResource;

    public function __construct(
        \M2E\TikTokShop\Model\RegistryFactory $registryFactory,
        \M2E\TikTokShop\Model\ResourceModel\Registry $registryResource
    ) {
        $this->registryFactory = $registryFactory;
        $this->registryResource = $registryResource;
    }

    // ----------------------------------------

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     * @throws \JsonException
     */
    public function setValue(string $key, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }

        $registryModel = $this->loadByKey($key);
        $registryModel->setValue($value);
        $registryModel->save();
    }

    /**
     * @param string $key
     *
     * @return array|mixed|null
     */
    public function getValue(string $key)
    {
        return $this->loadByKey($key)->getValue();
    }

    /**
     * @param $key
     *
     * @return array|bool|null
     */
    public function getValueFromJson($key)
    {
        $registryModel = $this->loadByKey($key);
        if (!$registryModel->getId()) {
            return [];
        }

        return json_decode($registryModel->getValue(), true);
    }

    /**
     * @param $key
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteValue($key): void
    {
        $this->registryResource->deleteByKey($key);
    }

    // ----------------------------------------

    /**
     * @param string $key
     *
     * @return \M2E\TikTokShop\Model\Registry
     */
    private function loadByKey(string $key): \M2E\TikTokShop\Model\Registry
    {
        $registryModel = $this->registryFactory->create();
        $this->registryResource->load(
            $registryModel,
            $key,
            \M2E\TikTokShop\Model\ResourceModel\Registry::COLUMN_KEY
        );

        if (!$registryModel->getId()) {
            $registryModel->setKey($key);
        }

        return $registryModel;
    }

    // ----------------------------------------
}
