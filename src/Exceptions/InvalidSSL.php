<?php
namespace Experian\Exceptions;

class InvalidSSL extends \Exception{
	public function __construct (
		string $message = "Invalid SSL for URL." , 
		int $code = 502 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}