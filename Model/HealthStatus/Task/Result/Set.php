<?php

namespace M2E\TikTokShop\Model\HealthStatus\Task\Result;

use M2E\TikTokShop\Model\HealthStatus\Task\Result;

class Set extends \M2E\TikTokShop\Model\AbstractModel
{
    /** @var Result[] */
    private $results = [];
    private $keys = [];

    private $worstState = Result::STATE_SUCCESS;

    /**
     * @param Result $result
     */
    public function add(Result $result): void
    {
        $key = $result->getTaskHash();
        $this->results[$key] = $result;

        $this->keys['tab'][$this->getTabKey($result)][] = $key;
        $this->keys['fieldset'][$this->getFieldSetKey($result)][] = $key;
        $this->keys['type'][$result->getTaskType()][] = $key;

        if ($result->getTaskResult() > $this->worstState) {
            $this->worstState = $result->getTaskResult();
        }
    }

    /**
     * @param Result[] $results
     */
    public function fill(array $results): void
    {
        $this->clear();

        foreach ($results as $result) {
            $this->add($result);
        }
    }

    public function clear(): void
    {
        $this->results = [];
        $this->keys = [];

        $this->worstState = Result::STATE_SUCCESS;
    }

    /**
     * @param string $taskType
     * @param bool $skipHiddenResults
     *
     * @return Result[]
     */
    public function getByType($taskType, bool $skipHiddenResults = true)
    {
        $affectedKeys = $this->keys['type'][$taskType] ?? [];

        return $this->getByKeys($affectedKeys, $skipHiddenResults);
    }

    /**
     * @param string $tabKey
     * @param bool $skipHiddenResults
     *
     * @return Result[]
     */
    public function getByTab($tabKey, bool $skipHiddenResults = true)
    {
        $affectedKeys = $this->keys['tab'][$tabKey] ?? [];

        return $this->getByKeys($affectedKeys, $skipHiddenResults);
    }

    /**
     * @param string $fieldSetKey
     * @param bool $skipHiddenResults
     *
     * @return Result[]
     */
    public function getByFieldSet($fieldSetKey, bool $skipHiddenResults = true)
    {
        $affectedKeys = $this->keys['fieldset'][$fieldSetKey] ?? [];

        return $this->getByKeys($affectedKeys, $skipHiddenResults);
    }

    /**
     * @param array|NULL $affectedKeys
     * @param bool $skipHiddenResults
     *
     * @return Result[]
     */
    public function getByKeys($affectedKeys = null, bool $skipHiddenResults = true)
    {
        $affectedKeys === null && $affectedKeys = $this->getAllKeys();

        $results = [];
        foreach ($affectedKeys as $affectedKey) {
            if (!isset($this->results[$affectedKey])) {
                continue;
            }

            $temp = $this->results[$affectedKey];
            if ($skipHiddenResults && $temp->isSuccess() && !$temp->isTaskMustBeShowIfSuccess()) {
                continue;
            }

            $results[$affectedKey] = $temp;
        }

        return $results;
    }

    public function getAllKeys(): array
    {
        if (!isset($this->keys['type'])) {
            return [];
        }

        $keys = [];
        foreach ($this->keys['type'] as $type => $typeKeys) {
            $keys = array_merge($keys, $typeKeys);
        }

        return $keys;
    }

    public function getTabKey(Result $result): string
    {
        return strtolower(preg_replace('/[^A-za-z0-9_]/', '', $result->getTabName()));
    }

    public function getFieldSetKey(Result $result): string
    {
        return strtolower(preg_replace('/[^A-za-z0-9_]/', '', $result->getTabName() . $result->getFieldSetName()));
    }

    public function getWorstState()
    {
        return $this->worstState;
    }

    public function isCritical(): bool
    {
        return $this->getWorstState() == Result::STATE_CRITICAL;
    }

    public function isWaring(): bool
    {
        return $this->getWorstState() == Result::STATE_WARNING;
    }

    public function isNotice(): bool
    {
        return $this->getWorstState() == Result::STATE_NOTICE;
    }

    public function isSuccess(): bool
    {
        return $this->getWorstState() == Result::STATE_SUCCESS;
    }
}
