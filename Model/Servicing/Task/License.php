<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Servicing\Task;

class License implements \M2E\TikTokShop\Model\Servicing\TaskInterface
{
    public const NAME = 'license';

    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        $this->licenseService = $licenseService;
    }

    // ----------------------------------------

    public function getServerTaskName(): string
    {
        return self::NAME;
    }

    // ----------------------------------------

    public function isAllowed(): bool
    {
        return true;
    }

    // ----------------------------------------

    public function getRequestData(): array
    {
        return [];
    }

    // ----------------------------------------

    public function processResponseData(array $data): void
    {
        $license = $this->licenseService->get();

        if (isset($data['info']) && is_array($data['info'])) {
            $license = $this->updateInfoData($license, $data['info']);
        }

        if (isset($data['validation']) && is_array($data['validation'])) {
            $license = $this->updateValidationMainData($license, $data['validation']);

            if (isset($data['validation']['validation']) && is_array($data['validation']['validation'])) {
                $license = $this->updateValidationValidData($license, $data['validation']['validation']);
            }
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $license = $this->updateConnectionData($license, $data['connection']);
        }

        $this->licenseService->update($license);
    }

    // ----------------------------------------

    private function updateInfoData(
        \M2E\Core\Model\License $license,
        array $infoData
    ): \M2E\Core\Model\License {
        if (isset($infoData['email'])) {
            $info = $license->getInfo()->withEmail($infoData['email']);

            return $license->withInfo($info);
        }

        return $license;
    }

    private function updateValidationMainData(
        \M2E\Core\Model\License $license,
        array $validationData
    ): \M2E\Core\Model\License {
        if (isset($validationData['domain'])) {
            $domain = $license->getInfo()->getDomainIdentifier()->withValidValue($validationData['domain']);
            $info = $license->getInfo()->withDomainIdentifier($domain);
            $license = $license->withInfo($info);
        }

        if (isset($validationData['ip'])) {
            $ip = $license->getInfo()->getIpIdentifier()->withValidValue($validationData['ip']);
            $info = $license->getInfo()->withIpIdentifier($ip);
            $license = $license->withInfo($info);
        }

        return $license;
    }

    private function updateValidationValidData(
        \M2E\Core\Model\License $license,
        array $isValidData
    ): \M2E\Core\Model\License {
        if (isset($isValidData['domain'])) {
            $domain = $license->getInfo()->getDomainIdentifier()->withValid((bool)$isValidData['domain']);
            $info = $license->getInfo()->withDomainIdentifier($domain);
            $license = $license->withInfo($info);
        }

        if (isset($isValidData['ip'])) {
            $ip = $license->getInfo()->getIpIdentifier()->withValid((bool)$isValidData['ip']);
            $info = $license->getInfo()->withIpIdentifier($ip);
            $license = $license->withInfo($info);
        }

        return $license;
    }

    private function updateConnectionData(\M2E\Core\Model\License $license, array $data): \M2E\Core\Model\License
    {
        if (isset($data['domain'])) {
            $domain = $license->getInfo()->getDomainIdentifier()->withRealValue($data['domain']);
            $info = $license->getInfo()->withDomainIdentifier($domain);
            $license = $license->withInfo($info);
        }

        if (isset($data['ip'])) {
            $ip = $license->getInfo()->getIpIdentifier()->withRealValue($data['ip']);
            $info = $license->getInfo()->withIpIdentifier($ip);
            $license = $license->withInfo($info);
        }

        return $license;
    }
}
