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
    private static $mode = DEBUG;

    public function run()
    {
        $lines    = $this->getInputLines();
        $n        = (int)$lines[0];
        $iterator = new \LimitIterator(new \ArrayIterator($lines), 1, $n);

        $resultLines = [];
        $calculator  = new Calculator();
        foreach ($iterator as $line) {
            $arguments     = explode(' ', $line);
            $arguments     = array_map('trim', $arguments);
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
        $digits = range(1, 9, 1);
        $root = $this->calculateNumericRoot($a);
        $key = array_search($root, $digits);

        $begin = array_splice($digits, 0, $key);
        $digits = array_merge($digits, $begin);


        bcscale(0);
        $fullCycles = bcdiv(bcadd(bcsub($b, $a), 1), 9);
        $countMap = array_combine($digits, array_fill(1, 9, $fullCycles));
        $restCount = bcadd(bcsub($b, bcadd($a, bcmul($fullCycles, 9))), 1);

        $i = 0;
        foreach($countMap as &$value) {
            if ($i >= $restCount) {
                break;
            }

            $value = bcadd($value, 1);
            ++$i;
        }

        $max = array_reduce($countMap, function($value, $carry) {
            return max($value, $carry);
        }, -1);

        $countMap = array_filter($countMap, function($value) use ($max) {
            return bccomp($value, $max) === 0;
        });

        $countList = array_keys($countMap);
        asort($countList);
        array_unshift($countList, count($countList));

        return implode(' ', $countList);
    }

    protected function calculateNumericRoot($number)
    {
        $number = strval($number);
        while (count(str_split($number)) > 1) {
            $digits = str_split($number);
            $sum    = 0;
            foreach ($digits as $digit) {
                $sum = bcadd($sum, $digit);
            }

            $number = strval($sum);
        }

        return $number;
    }
}

(new Application())->run();