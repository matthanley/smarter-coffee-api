<?php

class SmarterCoffee
{

	protected $address;
	protected $port;

	const ACTION_BREW = "7";
	const ACTION_RESET = "\x10";
	const BUFFER_LENGTH = 10;

	protected $response = [
		"03007e320b13000222" 	=> 'brewing',
		"03017e320b13000222" 	=> 'brew in progress',
		"03017e325313000222" 	=> 'brew in progress',
		"03057e320613000224" 	=> 'carafe not present',
		"03057e320612000222" 	=> 'carafe not present',
		"03067e320700000224" 	=> 'no water',
		"03007e320613000122" 	=> 'reset',
		"default"				=> 'unknown response "%s": check machine',
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
			$out = bin2hex(fgets($sc, self::BUFFER_LENGTH));
			if (array_key_exists($out, $this->response)) {
				$message = $this->response[$out];
			} else {
				$message = sprintf($this->response['default'], $out);
			}
		}
		finally {
			@fclose($sc);
		}
		return $message;
	}

	public static function make($address) {
		$instance = new self($address);
		return $instance->brew();
	}

}
