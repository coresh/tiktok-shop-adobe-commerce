<?php

namespace M2E\TikTokShop\Helper\Data;

class GlobalData
{
    /** @var \Magento\Framework\Registry */
    private $registryModel;

    /**
     * @param \Magento\Framework\Registry $registryModel
     */
    public function __construct(
        \Magento\Framework\Registry $registryModel
    ) {
        $this->registryModel = $registryModel;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key)
    {
        $globalKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;

        return $this->registryModel->registry($globalKey);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($key, $value): void
    {
        $globalKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;
        $this->registryModel->register($globalKey, $value);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function unsetValue($key): void
    {
        $globalKey = \M2E\TikTokShop\Helper\Data::CUSTOM_IDENTIFIER . '_' . $key;
        $this->registryModel->unregister($globalKey);
    }
}
