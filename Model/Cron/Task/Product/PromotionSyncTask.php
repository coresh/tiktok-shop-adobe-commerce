<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Product;

class PromotionSyncTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'promotion/sync';

    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Promotion\Synchronization $promotionsSynchronization;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Promotion\Synchronization $promotionsSynchronization
    ) {
        $this->accountRepository = $accountRepository;
        $this->promotionsSynchronization = $promotionsSynchronization;
    }

    /**
     * @param \M2E\TikTokShop\Model\Cron\TaskContext $context
     *
     * @return void
     */
    public function process($context): void
    {
        $context->getSynchronizationLog()->setTask(\M2E\TikTokShop\Model\Synchronization\Log::TASK_OTHER);
        $context->getSynchronizationLog()->setInitiator(\M2E\Core\Helper\Data::INITIATOR_EXTENSION);

        $permittedAccounts = $this->accountRepository->getAll();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            foreach ($account->getShops() as $shop) {
                try {
                    $this->promotionsSynchronization->process($account, $shop);
                } catch (\Throwable $exception) {
                    $message = (string)__(
                        'The "Synchronize Promotions" Action for Account "%1" was completed with error.',
                        $account->getTitle()
                    );

                    $context->getExceptionHandler()->processTaskAccountException($message, __FILE__, __LINE__);
                    $context->getExceptionHandler()->processTaskException($exception);
                }
            }
        }
    }
}
