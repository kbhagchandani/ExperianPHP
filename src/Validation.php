<?php

namespace Experian;

use Experian\Exceptions\InvalidDataType;
use Experian\Exceptions\CombinedLengthExceedsPermittedLimit;
use Experian\Exceptions\FieldLengthExceedsPermittedLimit;
use Experian\Exceptions\MissingMandatoryField;
use Experian\Exceptions\InvalidHostUrl;

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

	public static function isValidExperianURL($url){
		$urlComponents=parse_url($url);
		$host=explode('.',$urlComponents["host"]);
		if(count($host)!=3 || "$host[1].$host[2]"!=="experian.com" || $urlComponents["scheme"]!=="https")
			throw new InvalidHostUrl();
		if(!isset($urlComponents["port"]))
			$urlComponents["port"]=443;
		$g = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
		$r = stream_socket_client("ssl://{$urlComponents["host"]}:{$urlComponents["port"]}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $g);
		$cont = stream_context_get_params($r);
		$certInfo=openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);

		if($certInfo['subject']['CN']!==$urlComponents["host"])
			throw new InvalidSSL();
		if($certInfo['validTo_time_t']<time())
			throw new InvalidSSL();

		return true;
	}
}