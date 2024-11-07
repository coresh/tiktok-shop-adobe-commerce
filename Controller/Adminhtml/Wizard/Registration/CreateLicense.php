<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\Registration;

class CreateLicense extends \M2E\TikTokShop\Controller\Adminhtml\Wizard\AbstractRegistration
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\License\Add\Processor $connectionProcessor;
    private \M2E\TikTokShop\Helper\Client $clientHelper;
    private \M2E\TikTokShop\Model\Registration\UserInfo\Repository $registrationUserInfo;
    private \M2E\TikTokShop\Helper\Module\License $licenseHelper;
    private \M2E\TikTokShop\Helper\Data $dataHelper;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Connector\License\Add\Processor $connectionProcessor,
        \M2E\TikTokShop\Helper\Client $clientHelper,
        \M2E\TikTokShop\Helper\Module\License $licenseHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Model\Registration\UserInfo\Repository $manager,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Servicing\Dispatcher $servicing,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder
    ) {
        parent::__construct($magentoHelper, $wizardHelper, $nameBuilder, $licenseHelper);

        $this->connectionProcessor = $connectionProcessor;
        $this->registrationUserInfo = $manager;
        $this->licenseHelper = $licenseHelper;
        $this->dataHelper = $dataHelper;
        $this->clientHelper = $clientHelper;
        $this->exceptionHelper = $exceptionHelper;
        $this->servicing = $servicing;
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
                $licenseData[$key] = \M2E\TikTokShop\Helper\Data::escapeJs(
                    \M2E\TikTokShop\Helper\Data::escapeHtml($tempValue)
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

        $userInfo = new \M2E\TikTokShop\Model\Registration\UserInfo(
            $licenseData['email'],
            $licenseData['firstname'],
            $licenseData['lastname'],
            $licenseData['phone'],
            $licenseData['country'],
            $licenseData['city'],
            $licenseData['postal_code'],
        );

        $this->registrationUserInfo->save($userInfo);

        if ($this->licenseHelper->getKey()) {
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
            $this->licenseHelper->setLicenseKey($response->getKey());
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);

            $message = __(
                'License Creation is failed. Please contact M2E TikTok Shop Connect Support for resolution.'
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
