<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage;

class TagManager
{
    private \M2E\TikTokShop\Model\TagFactory $baseTagFactory;
    private \M2E\TikTokShop\Model\TikTokShop\TagFactory $ttsTagFactory;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\TikTokShop\Model\Tag\ValidatorIssues $validatorIssues;

    public function __construct(
        \M2E\TikTokShop\Model\TagFactory $baseTagFactory,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $ttsTagFactory,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\Tag\ValidatorIssues $validatorIssues
    ) {
        $this->baseTagFactory = $baseTagFactory;
        $this->ttsTagFactory = $ttsTagFactory;
        $this->tagBuffer = $tagBuffer;
        $this->validatorIssues = $validatorIssues;
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $product
     * @param \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage[] $messages
     */
    public function addErrorTags(\M2E\TikTokShop\Model\Product $product, array $messages): void
    {
        if (empty($messages)) {
            return;
        }

        $tags = [];

        $userErrors = array_filter($messages, function ($message) {
            return $message->getCode() !== \M2E\TikTokShop\Model\Tag\ValidatorIssues::NOT_USER_ERROR;
        });

        if (!empty($userErrors)) {
            $tags[] = $this->baseTagFactory->createWithHasErrorCode();

            foreach ($userErrors as $userError) {
                $error = $this->validatorIssues->mapByCode($userError->getCode());
                if ($error === null) {
                    continue;
                }

                $tags[] = $this->ttsTagFactory->createByErrorCode(
                    $error->getCode(),
                    $error->getText()
                );
            }

            $this->tagBuffer->addTags($product, $tags);
        }
    }
}
