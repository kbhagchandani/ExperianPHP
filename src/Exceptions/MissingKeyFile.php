<?php
namespace Experian\Exceptions;

class MissingKeyFile extends \Exception{
	public function __construct (
		$message = "Missing Key File. Please check if it exists, or you have provided valid path." , 
		$code = 404 ,
		\Throwable $previous = NULL
	){
		parent::__construct($message,$code,$previous);
	}
}