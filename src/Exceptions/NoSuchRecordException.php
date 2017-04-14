<?php
namespace Experian\Exceptions;

class NoSuchRecordException extends \Exception{
	public function __construct (
		string $message = "NO RECORD FOUND." , 
		int $code = 7 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}