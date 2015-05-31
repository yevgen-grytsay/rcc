<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 31.05.2015
 * Time: 9:52
 */

namespace y2015\qual3\C;

define('DEBUG', 0);
define('PRODUCTION', 1);

class Application
{
    private static $mode = DEBUG;

    public function run()
    {
        $lines    = $this->getInputLines();
        $n        = (int)$lines[0];
        $iterator = new \LimitIterator(new \ArrayIterator($lines), 1, $n);

        $resultLines = [];
        foreach ($iterator as $line) {
            $calculator    = new Calculator();
            $arguments     = explode(' ', $line);
            $arguments     = array_map('intval', $arguments);
            $resultLines[] = call_user_func_array([$calculator, 'calculate'], $arguments);
        }

        echo implode(PHP_EOL, $resultLines);
    }

    private function getInputLines()
    {
        $lines = [];

        if (self::isProductionMode()) {
            $lines = file("php://stdin");
        } else {
            if (self::isDebugMode()) {
                $filename = 'input.txt';
                $lines    = file($filename);
            }
        }

        return $lines;
    }

    private static function isDebugMode()
    {
        return self::$mode === DEBUG;
    }

    private static function isProductionMode()
    {
        return self::$mode === PRODUCTION;
    }
}

class Calculator
{
    protected $time = 0;

    /**
     * @var Snail
     */
    protected $snail_1;

    /**
     * @var Snail
     */
    protected $snail_2;


    public function calculate($up_1, $down_1, $up_2, $down_2, $z)
    {
        $snail_1 = $this->snail_1 = new Snail(0, $up_1, $down_1);
        $snail_2 = $this->snail_2 = new Snail(0, $up_2, $down_2);

        $result = 0;

        $periods = floor($z / 12);

        $periodList = [];
        if ($periods > 0) {
            $periodList = array_fill(0, $periods, 12);
        }
        if ($periods * 12 < $z) {
            $periodList[] = $z - ($periods * 12);
        }

        while (count($periodList) > 0) {
            $periodLength = array_shift($periodList);

            $isDay = $this->isDay();

            $countTime = $snail_1->position > $snail_2->position
                || (
                    $snail_1->position == $snail_2->position
                    && $isDay
                    && $snail_1->upSpeed > $snail_2->upSpeed
                )
                || (
                    $snail_1->position == $snail_2->position
                    && !$isDay
                    && $snail_1->downSpeed < $snail_2->downSpeed
                );

            if ($isDay) {
                $t = ($snail_1->position - $snail_2->position) / ($snail_2->upSpeed - $snail_1->upSpeed);
            } else {
                $t = ($snail_1->position - $snail_2->position) / ($snail_2->downSpeed - $snail_1->downSpeed);
            }
            $t = abs($t);

            /**
             * Улитки не успеют встретиться до окончания периода.
             */
            if ($t > $periodLength) {
                $t = 0;
            }

            if ($countTime) {
                if ($t > 0) {
                    $result += $t;
                } else {
                    $result += $periodLength;
                }
            }

            if ($t > 0) {
                array_unshift($periodList, $periodLength - $t);
                $this->forwardTime($t);
            } else {
                $this->forwardTime($periodLength);
            }

        }

        return $result;
    }

    public function time()
    {
        return $this->time;
    }

    public function forwardTime($hours)
    {
        if ($this->isDay()) {
            $this->snail_1->forwardTimeDay($hours);
            $this->snail_2->forwardTimeDay($hours);
        } else {
            $this->snail_1->forwardTimeNight($hours);
            $this->snail_2->forwardTimeNight($hours);
        }

        $this->time += $hours;
    }


    protected function isDay()
    {
        return ($this->time() / 12) % 2 == 0;
    }
}

class Snail
{
    public $upSpeed;
    public $downSpeed;
    public $position;

    public function __construct($pos, $upSpeed, $downSpeed)
    {
        $this->position  = $pos;
        $this->upSpeed   = $upSpeed;
        $this->downSpeed = $downSpeed;
    }

    public function forwardTimeDay($hours)
    {
        $this->position += $this->upSpeed * $hours;
    }

    public function forwardTimeNight($hours)
    {
        $this->position -= $this->downSpeed * $hours;
    }

}


(new Application())->run();