<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

trait ValidatorTrait
{
    /** @var \M2E\TikTokShop\Model\Response\Message[] */
    private array $messages = [];

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    // ----------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Product $product
     * @param \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
     * @param \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator\ValidatorInterface[] $validators
     *
     * @return void
     */
    private function validateProductBy(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        array $validators
    ): void {
        foreach ($validators as $validator) {
            $error = $validator->validate($product, $configurator);
            if ($error !== null) {
                $this->addErrorMessage($error);
            }
        }
    }

    // ----------------------------------------

    private function hasErrorMessages(): bool
    {
        foreach ($this->getMessages() as $message) {
            if ($message->isError()) {
                return true;
            }
        }

        return false;
    }

    private function addErrorMessage(string $message): void
    {
        $this->messages[] = \M2E\TikTokShop\Model\Response\Message::createError($message);
    }
}
