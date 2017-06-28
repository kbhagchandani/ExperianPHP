<?php
namespace Experian\Exceptions;

class InvalidAuthException extends \Exception{
	public function __construct (
		$message = "Incorrect User ID and/or Password." , 
		$code = 401 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}