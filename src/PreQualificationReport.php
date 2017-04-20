<?php

namespace Experian;

use Experian\Exceptions\NoSuchRecordException;
use Experian\Validation;

class PreQualificationReport {

	private static function isValid($applicationData){
		$validationSchema=[
			'PrimaryApplicant'=>[
				'Required'=>true,
				'ChildNodes'=>[
					'Name'=>[
						'Required'=>true,
						'CombinedChildTextLengthLimit'=>57,
						'ChildNodes'=>[
							'Surname'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>65
							],
							'First'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>32
							],
							'Middle'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>32
							],
							'Gen'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>4
							]
						]
					],
					'SSN'=>[
						'Required'=>false,
						'Type'=>'int',
						'MaxLength'=>11,
						'MinLength'=>9,
					],
					'CurrentAddress'=>[
						'Required'=>true,
						'CombinedChildTextLengthLimit'=>118,
						'ChildNodes'=>[
							'Street'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>68
							],
							'City'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>38
							],
							'State'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>2
							],
							'Zip'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>10
							]
						]
					],
					'PreviousAddress'=>[
						'Required'=>false,
						'CombinedChildTextLengthLimit'=>118,
						'ChildNodes'=>[
							'Street'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>68
							],
							'City'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>38
							],
							'State'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>2
							],
							'Zip'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>10
							]
						]
					],
					'DriverLicense'=>[
						'Required'=>false,
						'ChildNodes'=>[
							'State'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>2
							],
							'Number'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>21
							]
						]
					],
					'Employment'=>[
						'Required'=>false,
						'ChildNodes'=>[
							'Company'=>[
								'Required'=>true,
								'Type'=>'string',
								'MaxLength'=>23
							],
							'Address'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>25
							],
							'City'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>11
							],
							'State'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>2
							],
							'Zip'=>[
								'Required'=>false,
								'Type'=>'string',
								'MaxLength'=>10
							]
						]
					],
					'Age'=>[
						'Required'=>false,
						'Type'=>'int',
						'MaxLength'=>3
					],
					'DOB'=>[
						'Required'=>false,
						'Type'=>'string',
						'MaxLength'=>8,
						'MinLength'=>8,
					],
					'YOB'=>[
						'Required'=>false,
						'Type'=>'int',
						'MaxLength'=>4,
						'MinLength'=>4,
					],
					'FileUnfreezePIN'=>[
						'Required'=>false,
						'Type'=>'int',
						'MaxLength'=>15
					]
				]
			]
		];
		$validationSchema['SecondaryApplicant']=$validationSchema['PrimaryApplicant'];
		$validationSchema['SecondaryApplicant']['Required']=false;
		Validation::validate($validationSchema,$applicationData);
	}

	public static function prepareRequestData($baseObj){
		$applicationData=$baseObj->getUserData();
		self::isValid($applicationData);

		$metaData=$baseObj->request->getARFRequestParameters(['vendor','subscriber']);
		
		$preparedData=[
			'CreditProfile'=>[
				'Subscriber'=>$metaData['Subscriber'],
				'AccountType'=>[
					'Type'=>'3F'
				]
			]
		];

		$preparedData['CreditProfile']=$preparedData['CreditProfile']+$applicationData;

		$preparedData['CreditProfile']=$preparedData['CreditProfile']+
					[
						'Options'=>[
								'ReferenceNumber'=>'00234',
								'EndUser'=>'All Star Mortgage'
							],
						'Vendor'=>$metaData['Vendor'],
						'OutputType'=>[
							'XML'=>[
								'ARFVersion'=>'07',
								'Segment130'=>'Y'
							]
						]
					];
		
		return $preparedData;
	}

	public static function extractReport(Response $response){
		$products=$response->getProducts();
		if(isset($products['CreditProfile'])){
			$report=$products['CreditProfile'];
			if(isset($report['InformationalMessage']['MessageNumber']) && $report['InformationalMessage']['MessageNumber']=='07'){
				throw new NoSuchRecordException;
			}
		} else {
			return $report;
		}
	}
}