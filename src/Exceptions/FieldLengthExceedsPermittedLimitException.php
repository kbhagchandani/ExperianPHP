<?php
namespace Experian\Exceptions;

class FieldLengthExceedsPermittedLimitException extends \Exception{
	public function __construct (
		string $fieldName,
		string $expectedLength
	) {
		$message=sprintf('%s can have at most %d characters.',$fieldName,$expectedLength);
		parent::__construct($message,412,NULL);
	}
}