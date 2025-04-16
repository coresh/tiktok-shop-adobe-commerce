<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\Registration;

class CreateLicense extends \M2E\TikTokShop\Controller\Adminhtml\Wizard\AbstractRegistration
{
    private \M2E\Core\Model\RegistrationService $registrationService;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\License\Add\Processor $connectionProcessor;
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\RegistrationService $registrationService,
        \M2E\TikTokShop\Model\TikTokShop\Connector\License\Add\Processor $connectionProcessor,
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService
        );
        $this->registrationService = $registrationService;
        $this->connectionProcessor = $connectionProcessor;
        $this->clientHelper = $clientHelper;
        $this->exceptionHelper = $exceptionHelper;
        $this->servicing = $servicing;
        $this->licenseService = $licenseService;
    }

    public function execute()
    {
        $requiredKeys = [
            'email',
            'firstname',
            'lastname',
            'phone',
            'country',
            'city',
            'postal_code',
        ];

        $licenseData = [];
        foreach ($requiredKeys as $key) {
            if ($tempValue = $this->getRequest()->getParam($key)) {
                $licenseData[$key] = \M2E\Core\Helper\Data::escapeJs(
                    \M2E\Core\Helper\Data::escapeHtml($tempValue)
                );
                continue;
            }

            $response = [
                'status' => false,
                'message' => __('You should fill all required fields.'),
            ];
            $this->setJsonContent($response);

            return $this->getResult();
        }

        $userInfo = new \M2E\Core\Model\Registration\User(
            $licenseData['email'],
            $licenseData['firstname'],
            $licenseData['lastname'],
            $licenseData['phone'],
            $licenseData['country'],
            $licenseData['city'],
            $licenseData['postal_code'],
        );

        $this->registrationService->saveUser($userInfo);

        if ($this->licenseService->has()) {
            $this->setJsonContent(['status' => true]);

            return $this->getResult();
        }

        try {
            $request = new \M2E\TikTokShop\Model\TikTokShop\Connector\License\Add\Request(
                $this->clientHelper->getDomain(),
                $this->clientHelper->getBaseDirectory(),
                $userInfo->getEmail(),
                $userInfo->getFirstname(),
                $userInfo->getLastname(),
                $userInfo->getPhone(),
                $userInfo->getCountry(),
                $userInfo->getCity(),
                $userInfo->getPostalCode()
            );
            $response = $this->connectionProcessor->process($request);
            $this->licenseService->create($response->getKey());
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);

            $message = __(
                'License Creation is failed. Please contact %extension_title Support for resolution.',
                [
                    'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                ]
            );

            $this->setJsonContent([
                'status' => false,
                'message' => $message,
            ]);

            return $this->getResult();
        }

        try {
            $this->servicing->processTask(
                \M2E\TikTokShop\Model\Servicing\Task\License::NAME
            );
        } catch (\Throwable $e) {
        }

        $this->setJsonContent(['status' => true]);

        return $this->getResult();
    }
}
