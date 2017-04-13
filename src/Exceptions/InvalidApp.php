<?php
namespace Experian\Exceptions;

class InvalidAuth extends \Exception{
	public function __construct (
		string $message = "Net Connect application is unavailable." , 
		int $code = 400 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}