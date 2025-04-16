<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Component;

class TikTokShop
{
    public const MAX_LENGTH_FOR_OPTION_VALUE = 50;
    public const ITEM_SKU_MAX_LENGTH = 50;

    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cachePermanent;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cachePermanent
    ) {
        $this->cachePermanent = $cachePermanent;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public static function prepareOptionsForOrders(array $options): array
    {
        foreach ($options as &$singleOption) {
            if ($singleOption instanceof \Magento\Catalog\Model\Product) {
                $reducedName = trim(
                    \M2E\Core\Helper\Data::reduceWordsInString(
                        $singleOption->getName(),
                        self::MAX_LENGTH_FOR_OPTION_VALUE
                    )
                );
                $singleOption->setData('name', $reducedName);

                continue;
            }

            foreach ($singleOption['values'] as &$singleOptionValue) {
                foreach ($singleOptionValue['labels'] as &$singleOptionLabel) {
                    $singleOptionLabel = trim(
                        \M2E\Core\Helper\Data::reduceWordsInString(
                            $singleOptionLabel,
                            self::MAX_LENGTH_FOR_OPTION_VALUE
                        )
                    );
                }
            }
        }

        if (isset($options['additional']['attributes'])) {
            foreach ($options['additional']['attributes'] as $code => &$title) {
                $title = trim($title);
            }
            unset($title);
        }

        return $options;
    }

    // ----------------------------------------

    /**
     * @return void
     */
    public function clearCache(): void
    {
        $this->cachePermanent->removeTagValues('TikTokShop');
    }
}
