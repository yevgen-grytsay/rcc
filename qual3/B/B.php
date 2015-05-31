<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 31.05.2015
 * Time: 9:52
 */

namespace y2015\qual3\B;

define('DEBUG', 0);
define('PRODUCTION', 1);

class Application
{
    private static $mode = PRODUCTION;

    public function run()
    {
        $lines    = $this->getInputLines();
        $n        = (int)$lines[0];
        $iterator = new \LimitIterator(new \ArrayIterator($lines), 1, $n);

        $resultLines = [];
        $calculator  = new Calculator();
        foreach ($iterator as $line) {
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
    public function calculate($a, $b)
    {
        $rootList = [];
        for ($i = $a; $i <= $b; $i++) {
            $root                       = $this->calculateNumericRoot($i);
            $rootList["{$a}-{$b}:{$i}"] = $root;
        }

        $countMap = [];
        foreach ($rootList as $root) {
            if (!array_key_exists($root, $countMap)) {
                $countMap[$root] = 1;
            } else {
                ++$countMap[$root];
            }
        }

        arsort($countMap);

        $resultArray = [];

        list($root, $prevCount) = each($countMap);
        unset($countMap[$root]);

        $resultArray[] = $root;
        foreach ($countMap as $root => $count) {
            if ($count == $prevCount) {
                $resultArray[] = $root;
            } else {
                break;
            }
        }

        asort($resultArray);
        array_unshift($resultArray, count($resultArray));

        return implode(' ', $resultArray);
    }

    protected function calculateNumericRoot($number)
    {
        $number = strval($number);
        while (count(str_split($number)) > 1) {
            $digits = str_split($number);
            $sum    = 0;
            foreach ($digits as $digit) {
                $sum += intval($digit);
            }

            $number = strval($sum);
        }

        return $number;
    }
}

(new Application())->run();