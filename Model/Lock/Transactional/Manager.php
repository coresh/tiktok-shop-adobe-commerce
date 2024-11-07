<?php

namespace M2E\TikTokShop\Model\Lock\Transactional;

/**
 * Class \M2E\TikTokShop\Model\Lock\Transactional\Manager
 */
class Manager extends \M2E\TikTokShop\Model\AbstractModel
{
    private $nick = 'undefined';

    private $isTableLocked = false;
    private $isTransactionStarted = false;

    /** @var \Magento\Framework\App\ResourceConnection */
    private $resourceConnection;

    /** @var \M2E\TikTokShop\Model\ActiveRecord\Factory */
    private $activeRecordFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        $nick,
        array $data = []
    ) {
        parent::__construct($data);
        $this->resourceConnection = $resourceConnection;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->nick = $nick;
    }

    public function __destruct()
    {
        $this->unlock();
    }

    //########################################

    public function lock()
    {
        if ($this->getExclusiveLock()) {
            return;
        }

        $this->createExclusiveLock();
        $this->getExclusiveLock();
    }

    public function unlock()
    {
        $this->isTableLocked && $this->unlockTable();
        $this->isTransactionStarted && $this->commitTransaction();
    }

    //########################################

    private function getExclusiveLock()
    {
        $this->startTransaction();

        $connection = $this->resourceConnection->getConnection();
        $lockId = (int)$connection->select()
                                  ->from($this->getTableName(), ['id'])
                                  ->where('nick = ?', $this->nick)
                                  ->forUpdate()
                                  ->query()->fetchColumn();

        if ($lockId) {
            return true;
        }

        $this->commitTransaction();

        return false;
    }

    private function createExclusiveLock()
    {
        $this->lockTable();

        $lock = $this->activeRecordFactory->getObjectLoaded(
            'Lock\Transactional',
            $this->nick,
            'nick',
            false
        );

        if ($lock === null) {
            $lock = $this->activeRecordFactory->getObject('Lock\Transactional');
            $lock->setData([
                'nick' => $this->nick,
            ]);
            $lock->save();
        }

        $this->unlockTable();
    }

    //########################################

    private function startTransaction()
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        $this->isTransactionStarted = true;
    }

    private function commitTransaction()
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->commit();

        $this->isTransactionStarted = false;
    }

    // ----------------------------------------

    private function lockTable()
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->query("LOCK TABLES `{$this->getTableName()}` WRITE");

        $this->isTableLocked = true;
    }

    private function unlockTable()
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->query('UNLOCK TABLES');

        $this->isTableLocked = false;
    }

    private function getTableName()
    {
        return $this->activeRecordFactory->getObject('Lock\Transactional')->getResource()->getMainTable();
    }

    //########################################

    public function getNick()
    {
        return $this->nick;
    }

    //########################################
}
