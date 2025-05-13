<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Mapping;

class Save extends \M2E\TikTokShop\Controller\Adminhtml\AbstractMapping
{
    private \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService;

    public function __construct(
        \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService
    ) {
        parent::__construct();

        $this->generalService = $generalService;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $attributes = [];
        if (!empty($post['general_attributes'])) {
            foreach ($post['general_attributes'] as $channelCode => $generalAttribute) {
                $attributes[] = new \M2E\TikTokShop\Model\AttributeMapping\General\Pair(
                    \M2E\TikTokShop\Model\AttributeMapping\GeneralService::MAPPING_TYPE,
                    (string)$generalAttribute['title'],
                    (string)$channelCode,
                    (string)$generalAttribute['magento_code']
                );
            }
            $this->generalService->update($attributes);
        }

        $this->setJsonContent(
            [
                'success' => true,
            ]
        );

        return $this->getResult();
    }
}
