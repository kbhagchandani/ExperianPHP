<?php
namespace Experian\Exceptions;

class InvalidAuth extends \Exception{
	public function __construct (
		$message = "Incorrect User ID and/or Password." , 
		$code = 401 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}