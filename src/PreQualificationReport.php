<?php

namespace Experian;

class PreQualificationReport {

	private static function isValid($applicationData){
		
	}

	public static function prepareRequestData($applicationData){
		self::isValid($applicationData);
		$applicationData['AccountType']=[
			'Type'=>'3F'
		];

		$applicationData['Options']=[
			'ReferenceNumber'=>'00234',
			'EndUser'=>'All Star Mortgage'
		];

		$applicationData['OutputType']=[
			'XML'=>[
				'ARFVersion'=>'07',
				'Segment130'=>'Y'
			]
		];
		
		return ['CreditProfile'=>$applicationData];
	}
}