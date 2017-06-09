<?php
namespace Experian\Exceptions;

class KeyFileWriteError extends \Exception{
	public function __construct (
		$message = "Experian key cannot be written to the specified path in the config file.", 
		$code = 500 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}