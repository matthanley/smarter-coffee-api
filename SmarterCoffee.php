<?php

class SmarterCoffee
{

	protected $address;
	protected $port;

	const ACTION_BREW = "7";
	const ACTION_RESET = "\x10";
	const BUFFER_LENGTH = 10;

	protected $response = [
		"0300" 	=> 'brewing',
		"0301" 	=> 'brew in progress',
		"0305" 	=> 'carafe not present',
		"0306" 	=> 'no water',
		//"0300" 	=> 'reset',
		"0"	=> 'unknown response "%s": check machine',
	];

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
		$output = bin2hex($output);
		foreach ($this->response as $flag => $response) {
			if (strpos($output, $flag) === 0) {
				return sprintf($response, $output);
			}
		}
	}

	public static function make($address) {
		$instance = new self($address);
		return $instance->brew();
	}

}
