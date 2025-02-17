<?php

namespace M2E\TikTokShop\Model\Cron;

class OperationHistory extends \M2E\TikTokShop\Model\OperationHistory
{
    /** @var array */
    private $timePoints = [];
    /** @var int */
    private $leftPadding = 0;
    /** @var string */
    private $bufferString = '';

    // ----------------------------------------

    public function addEol()
    {
        $this->appendEol();
        $this->saveBufferString();
    }

    public function appendEol()
    {
        $this->appendText();
    }

    // ---------------------------------------

    public function addLine($char = '-'): void
    {
        $this->appendLine($char);
        $this->saveBufferString();
    }

    public function appendLine($char = '-'): void
    {
        $this->appendText(str_repeat($char, 30));
    }

    // ---------------------------------------

    public function addText($text = null)
    {
        $this->appendText($text);
        $this->saveBufferString();
    }

    public function appendText($text = null)
    {
        $text && $text = str_repeat(' ', $this->leftPadding) . $text;
        $this->bufferString .= (string)$text . PHP_EOL;
    }

    // ---------------------------------------

    public function saveBufferString()
    {
        $profilerData = (string)$this->getContentData('profiler');
        $this->setContentData('profiler', $profilerData . $this->bufferString);
        $this->bufferString = '';
    }

    //########################################

    public function addTimePoint($id, $title)
    {
        foreach ($this->timePoints as &$point) {
            if ($point['id'] == $id) {
                $this->updateTimePoint($id);

                return true;
            }
        }

        $this->timePoints[] = [
            'id' => $id,
            'title' => $title,
            'time' => microtime(true),
        ];

        return true;
    }

    public function updateTimePoint($id)
    {
        foreach ($this->timePoints as $point) {
            if ($point['id'] == $id) {
                $point['time'] = microtime(true);

                return true;
            }
        }

        return false;
    }

    public function saveTimePoint($id, $immediatelySave = true)
    {
        foreach ($this->timePoints as $point) {
            if ($point['id'] == $id) {
                $this->appendText(
                    $point['title'] . ': ' . round(microtime(true) - $point['time'], 2) . ' sec.'
                );

                $immediatelySave && $this->saveBufferString();

                return true;
            }
        }

        return false;
    }

    //########################################

    public function increaseLeftPadding($count = 5)
    {
        $this->leftPadding += (int)$count;
    }

    public function decreaseLeftPadding($count = 5)
    {
        $this->leftPadding -= (int)$count;
        $this->leftPadding < 0 && $this->leftPadding = 0;
    }

    //########################################

    public function getProfilerInfo($nestingLevel = 0)
    {
        if ($this->getObject() === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#', 80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));
        strpos($nick, '_') !== false && $nick = str_replace('SYNCHRONIZATION_', '', $nick);

        $profilerData = preg_replace('/^/m', "{$offset}", $this->getContentData('profiler'));

        $info = <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$profilerData}
INFO;

        if ($fatalInfo = $this->getContentData('fatal_error')) {
            $info .= <<<INFO

{$offset}<span style="color: red; font-weight: bold;">Fatal: {$fatalInfo['message']}</span>
{$offset}<span style="color: red; font-weight: bold;">File: {$fatalInfo['file']}::{$fatalInfo['line']}</span>

INFO;
        }

        if ($exceptions = $this->getContentData('exceptions')) {
            foreach ($exceptions as $exception) {
                $info .= <<<INFO

{$offset}<span style="color: red; font-weight: bold;">Exception: {$exception['message']}</span>
{$offset}<span style="color: red; font-weight: bold;">File: {$exception['file']}::{$exception['line']}</span>

INFO;
            }
        }

        return <<<INFO
{$info}
{$offset}{$separationLine}

INFO;
    }
}
