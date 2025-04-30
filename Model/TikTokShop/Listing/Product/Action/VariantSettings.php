<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class VariantSettings
{
    public const ACTION_ADD = 'add';
    public const ACTION_REVISE = 'revise';
    public const ACTION_STOP = 'stop';
    public const ACTION_SKIP = 'skip';

    private array $variants = [];

    public function add(int $variantId, string $action): void
    {
        $this->validateAction($action);

        $this->variants[$variantId] = $action;
    }

    public function hasVariantId(int $variantId): bool
    {
        return isset($this->variants[$variantId]);
    }

    // ----------------------------------------

    public function hasAddAction(): bool
    {
        return in_array(self::ACTION_ADD, $this->variants, true);
    }

    public function hasReviseAction(): bool
    {
        return in_array(self::ACTION_REVISE, $this->variants, true);
    }

    public function isAllStopAction(): bool
    {
        foreach ($this->variants as $action) {
            if ($action !== self::ACTION_STOP) {
                return false;
            }
        }

        return true;
    }

    // ----------------------------------------

    public function isStopAction(int $variantId): bool
    {
        return isset($this->variants[$variantId])
            && $this->variants[$variantId] === self::ACTION_STOP;
    }

    public function isSkipAction(int $variantId): bool
    {
        return isset($this->variants[$variantId])
            && $this->variants[$variantId] === self::ACTION_SKIP;
    }

    public function isAddAction(int $variantId): bool
    {
        return isset($this->variants[$variantId])
            && $this->variants[$variantId] === self::ACTION_ADD;
    }

    public function isReviseAction(int $variantId): bool
    {
        return isset($this->variants[$variantId])
            && $this->variants[$variantId] === self::ACTION_REVISE;
    }

    // ----------------------------------------

    public function toArray(): array
    {
        $result = [];
        foreach ($this->variants as $variantId => $action) {
            $result[] = [
                'variant_id' => $variantId,
                'action' => $action,
            ];
        }

        return $result;
    }

    public static function createFromArray(array $variantsActionSettings): self
    {
        $variantsData = [];
        foreach ($variantsActionSettings as $variantRowData) {
            if (!isset($variantRowData['variant_id'], $variantRowData['action'])) {
                continue;
            }

            $variantsData[$variantRowData['variant_id']] = $variantRowData['action'];
        }

        $settings = new self();
        $settings->variants = $variantsData;

        return $settings;
    }

    public static function createAddActionStubForSimpleProduct(\M2E\TikTokShop\Model\Product $product): self
    {
        $variantsData = [];
        $processFirst = false;
        foreach ($product->getVariants() as $variant) {
            if (!$processFirst) {
                $variantsData[$variant->getId()] = self::ACTION_ADD;
                $processFirst = true;

                continue;
            }

            $variantsData[$variant->getId()] = self::ACTION_SKIP;
        }

        $settings = new self();
        $settings->variants = $variantsData;

        return $settings;
    }

    public function isEqual(VariantSettings $another): bool
    {
        $currentData = $this->variants;
        $anotherData = $another->variants;

        asort($currentData);
        asort($anotherData);

        return json_encode($currentData) === json_encode($anotherData);
    }

    // ----------------------------------------

    private function validateAction(string $action): void
    {
        if (!in_array($action, [self::ACTION_ADD, self::ACTION_REVISE, self::ACTION_STOP, self::ACTION_SKIP])) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(sprintf('Action %s not valid for variant.', $action));
        }
    }
}
