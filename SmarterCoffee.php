<?php

class SmarterCoffee
{

	protected $address;
	protected $port;

	protected $cups;
	protected $strength;
	protected $grind; // true for grind, false for filter
	protected $carafe;

	const ACTION_BREW = "7";
	const ACTION_RESET = "\x10";
	const ACTION_TOGGLE_GRIND = "\x3c\x7e";
	const ACTION_SET_STRENGTH = "\x35%s\x7e";
	const ACTION_SET_CUPS = "\x36%s\x7e";

	const BUFFER_LENGTH = 10;

	const RESULT_UNKNOWN = 0;
	const RESULT_SUCCESS = 1;
	const RESULT_BREW_IN_PROGRESS = 2;
	const RESULT_CARAFE_NOT_PRESENT = 3;
	const RESULT_NO_WATER = 4;

	public function __construct($address, $port = 2081) {
		$this->address = $address;
		$this->port = $port;
		return $this;
	}

	public function brew() {
		return $this->send(self::ACTION_BREW);
	}

	public function reset() {
		return $this->send(self::ACTION_RESET);
	}

	public function setCups($cups) {
		$cups = max(1, $cups);
		$cups = min(12, $cups);
		return $this->send(
			sprintf(self::ACTION_SET_CUPS, chr($cups))
		);
	}

	public function setStrength($strength) {
		$strength = max(0, $strength);
		$strength = min(2, $strength);
		return $this->send(
			sprintf(self::ACTION_SET_STRENGTH, chr($strength))
		);
	}

	public function setGrind($grind) {
		if (is_null($this->grind)) {
			$this->toggleGrind();
		}
		if ($this->grind != $grind) {
			return $this->toggleGrind();
		}
		return self::RESULT_SUCCESS;
	}

	protected function toggleGrind() {
		return $this->send(self::ACTION_TOGGLE_GRIND);
	}

	protected function send($command) {
		try {
			$sc = fsockopen($this->address, $this->port);
			fwrite($sc, $command);
			$message = $this->parse(fgets($sc, self::BUFFER_LENGTH));
		}
		finally {
			@fclose($sc);
		}
		return $message;
	}

	protected function parse($output) {
		// [0] to [2] are the response code from the last command
		// [3] to [9] are the status codes for the machine
		$output = bin2hex($output);
		$result = substr($output, 0, 6);
		$status = substr($output, 6);
		$this->parseStatus($status);
		return $this->parseResult($result);
	}

	protected function parseResult($result) {
		switch ($result) {
			case "03007e":
				return self::RESULT_SUCCESS;
			case "03017e":
				return self::RESULT_BREW_IN_PROGRESS;
			case "03057e":
				return self::RESULT_CARAFE_NOT_PRESENT;
			case "03067e":
				return self::RESULT_NO_WATER;
			default:
				return self::RESULT_UNKNOWN;
		}
	}

	protected function parseStatus($status) {
		$status = str_split($status, 2);
		$status = array_map(function($elem) {
			return hexdec($elem);
		}, $status);
		$this->carafe = (bool)($status[1] & 1);
		$this->grind = (bool)($status[1] & 2);
		$this->strength = $status[4];
		$this->cups = $status[5] - 64;
		// $status[2] is water level
	}

}
