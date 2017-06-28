<?php
namespace Experian\Exceptions;

class InvalidKeyFileException extends \Exception{
	public function __construct (
		$message = "Invalid Key File. It may be corrupted." , 
		$code = 500 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}