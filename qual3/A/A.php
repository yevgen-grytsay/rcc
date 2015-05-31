<?php
/**
 * Created by PhpStorm.
 * User: Eugene
 * Date: 31.05.2015
 * Time: 9:52
 */

namespace y2015\qual3\A;

define('DEBUG', 0);
define('PRODUCTION', 1);

class Application
{
	private static $mode = PRODUCTION;

	public function run()
	{
		$lines = $this->getInputLines();
		$n = (int)$lines[0];
		$iterator = new \LimitIterator(new \ArrayIterator($lines), 1, $n);

		$resultLines = [];
		$calculator = new Calculator();
		foreach($iterator as $line) {
			$arguments = explode(' ', $line);
			$arguments = array_map('intval', $arguments);
			$resultLines[] = call_user_func_array([$calculator, 'calculate'], $arguments);
		}

		echo implode(PHP_EOL, $resultLines);
	}

	private function getInputLines()
	{
		$lines = [];

		if(self::isProductionMode()) {
			$lines = file("php://stdin");
		}
		else if(self::isDebugMode()) {
			$filename = 'input.txt';
			$lines = file($filename);
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
	/**
	 * @param $one
	 * @param $two
	 * @param $price
	 *
	 * @return string
	 */
	public function calculate($one, $two, $price)
	{
		$isPar = $price%2 == 0;
		if (!$isPar && $one == 0) {
			return 'NO';
		}

		$twosNeeded = floor($price / 2);

		/**
		 * Cover by twos
		 */
		$twosDec = min($twosNeeded, $two);
		$price -= $twosDec * 2;

		if ($price == 0) {
			return 'YES';
		}

		/**
		 * Cover by ones
		 */
		if ($price > $one) {
			return 'NO';
		}
		else {
			return 'YES';
		}
	}
}

(new Application())->run();