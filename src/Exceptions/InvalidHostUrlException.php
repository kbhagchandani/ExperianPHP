<?php
namespace Experian\Exceptions;

class InvalidHostUrlException extends \Exception{
	public function __construct (
		string $message = "Invalid Net Connect transaction URL." , 
		int $code = 502 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}