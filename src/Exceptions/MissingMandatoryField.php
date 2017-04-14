<?php
namespace Experian\Exceptions;

class MissingMandatoryField extends \Exception{
	public function __construct (
		string $fieldName, 
		int $code = 412 ,
		\Throwable $previous = NULL
	){
		$message=sprintf('%s is required.',$fieldName);
		parent::__construct($message,$code,$previous);
	}
}