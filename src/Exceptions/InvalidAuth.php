<?php
namespace Experian\Exceptions;

class InvalidAuth extends \Exception{
	public function __construct (
		string $message = "Incorrect User ID and/or Password." , 
		int $code = 401 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}