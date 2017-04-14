<?php

namespace Experian;

use Experian\Exceptions\InvalidDataType;
use Experian\Exceptions\CombinedLengthExceedsPermittedLimit;
use Experian\Exceptions\FieldLengthExceedsPermittedLimit;
use Experian\Exceptions\MissingMandatoryField;

class Validation {

	public static function validate($schema,$data){
		$combinedTextLength=0;
		foreach($schema as $node => $validationProperty) {
			if(isset($data[$node]) && !empty($data[$node])) {
				if(isset($validationProperty['ChildNodes'])) {
					if(!is_array($data[$node])) {
						throw new InvalidDataType($node,'Associative Array');
					}
					$childNodesCombinedTextLength=self::validate($validationProperty['ChildNodes'],$data[$node]);
					if(isset($validationProperty['CombinedChildTextLengthLimit']) && $validationProperty['CombinedChildTextLengthLimit']<$childNodesCombinedTextLength) {
						throw new CombinedLengthExceedsPermittedLimit($node,$validationProperty['CombinedChildTextLengthLimit']);
					}
				} else {
					$length=strlen($data[$node]);
					if(isset($validationProperty['MaxLength']) && $validationProperty['MaxLength']<$length){
						throw new FieldLengthExceedsPermittedLimit($node,$validationProperty['MaxLength']);	
					}
					$combinedTextLength+=$length;
				}
			} else if(isset($validationProperty['Required']) && $validationProperty['Required']) {
				throw new MissingMandatoryField($node);
			}
		}
		return $combinedTextLength;
	}

}