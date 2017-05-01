<?php
namespace Experian\Exceptions;

class NoSuchRecordException extends \Exception{
	public function __construct (
		$message = "NO RECORD FOUND." , 
		$code = 7 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}