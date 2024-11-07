<?php

namespace M2E\TikTokShop\Model\Magento\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Mutable
{
    private \Magento\Framework\App\Config\ScopeCodeResolver $scopeCodeResolver;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Helper\Data\Cache\Runtime $runtimeCache;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Helper\Data\Cache\Runtime $runtimeCache
    ) {
        $this->objectManager = $objectManager;
        $this->runtimeCache = $runtimeCache;
    }

    public function setValue(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->runtimeCache->setValue(
            $this->preparePath($path, $scope, $scopeCode),
            $value,
            ['app_config_overrides']
        );

        return $this;
    }

    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->runtimeCache->getValue(
            $this->preparePath($path, $scope, $scopeCode)
        );
    }

    public function unsetValue(
        $path,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->runtimeCache->removeValue(
            $this->preparePath($path, $scope, $scopeCode)
        );

        return $this;
    }

    //----------------------------------------

    public function clear()
    {
        $this->runtimeCache->removeTagValues('app_config_overrides');

        return $this;
    }

    /*
     * Copied from \Magento\Framework\App\Config.php
     */
    private function preparePath($path, $scope, $scopeCode)
    {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        $configPath = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->getScopeCodeResolver()->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }
        if ($path) {
            $configPath .= '/' . $path;
        }

        return $configPath;
    }

    private function getScopeCodeResolver()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->scopeCodeResolver)) {
            $this->scopeCodeResolver = $this->objectManager->get(
                \Magento\Framework\App\Config\ScopeCodeResolver::class
            );
        }

        return $this->scopeCodeResolver;
    }
}
