<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\Category\Attribute;

class ValidateManager
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\TagManager $tagManager;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\TagManager $tagManager
    ) {
        $this->productRepository = $productRepository;
        $this->tagManager = $tagManager;
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $product
     * @param string[] $errors
     *
     * @return void
     */
    public function markProductAsNotValid(\M2E\TikTokShop\Model\Product $product, array $errors): void
    {
        $product->markCategoryAttributesAsInvalid($errors);
        $this->productRepository->save($product);

        $messages[] = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorMessage(
            'The Item either is Listed, or not Listed yet or not available',
            \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->addErrorTags($product, $messages);
        $this->tagManager->flush();
    }

    public function markProductAsValid(\M2E\TikTokShop\Model\Product $product): void
    {
        $product->markCategoryAttributesAsValid();
        $this->productRepository->save($product);

        $this->tagManager->removeTagByCode(
            $product,
            \M2E\TikTokShop\Model\Tag\ValidatorIssues::ERROR_CATEGORY_ATTRIBUTE_MISSING
        );
        $this->tagManager->flush();
    }
}
