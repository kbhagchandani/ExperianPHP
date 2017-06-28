<?php
namespace Experian\Exceptions;

class CombinedLengthExceedsPermittedLimitException extends \Exception{
	public function __construct (
		string $fieldName,
		string $expectedLength
	) {
		$message=sprintf('Entire %s can have at most %d characters.',$fieldName,$expectedLength);
		parent::__construct($message,412,NULL);
	}
}