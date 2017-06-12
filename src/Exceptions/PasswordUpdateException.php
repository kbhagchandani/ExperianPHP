<?php
namespace Experian\Exceptions;

class PasswordUpdateException extends \Exception{
	public function __construct (
		string $message = "Experian NetConnect Password must be updated every 80 days." , 
		int $code = 400 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}