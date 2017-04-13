<?php
namespace Experian\Exceptions;

class Unauthorized extends \Exception{
	public function __construct (
		string $message = "You may not have access to Net Connect product or it may be locked due to too many password violations." , 
		int $code = 403 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}