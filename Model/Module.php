<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class Module
{
    private const IDENTIFIER = 'M2E_TikTokShop';

    private \Magento\Framework\Module\PackageInfo $packageInfo;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \Magento\Framework\Module\ModuleResource $moduleResource;
    private \M2E\TikTokShop\Model\Registry\Manager $registryManager;

    public function __construct(
        \Magento\Framework\Module\PackageInfo $packageInfo,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ModuleResource $moduleResource,
        \M2E\TikTokShop\Model\Registry\Manager $registryManager
    ) {
        $this->packageInfo = $packageInfo;
        $this->moduleList = $moduleList;
        $this->moduleResource = $moduleResource;
        $this->registryManager = $registryManager;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'TikTokShop-m2';
    }

    /**
     * @return string
     */
    public function getPublicVersion(): string
    {
        return $this->packageInfo->getVersion(self::IDENTIFIER);
    }

    public function getSetupVersion()
    {
        return $this->moduleList->getOne(self::IDENTIFIER)['setup_version'];
    }

    public function getSchemaVersion()
    {
        return $this->moduleResource->getDbVersion(self::IDENTIFIER);
    }

    public function getDataVersion()
    {
        return $this->moduleResource->getDataVersion(self::IDENTIFIER);
    }

    public function hasLatestVersion(): bool
    {
        return (bool)$this->getLatestVersion();
    }

    public function setLatestVersion(string $version): void
    {
        $this->registryManager->setValue(
            '/module/latest_version/',
            $version
        );
    }

    public function getLatestVersion(): ?string
    {
        return $this->registryManager->getValue('/module/latest_version/');
    }
}
