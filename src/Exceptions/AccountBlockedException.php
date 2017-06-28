<?php
namespace Experian\Exceptions;

class AccountBlockedException extends \Exception{
	public function __construct (
		$message = "You must update your Experian Password. Also you might need to get your account unlocked." , 
		$code = 600 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}