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
		if($baseObj->addOns->hasRequests()){
			$preparedData['CreditProfile']=$preparedData['CreditProfile']+$baseObj->addOns->getAddOnData();
			if($baseObj->addOns->hasOutputTags()){
				$preparedData['CreditProfile']['OutputType']['XML']=$preparedData['CreditProfile']['OutputType']['XML']+$baseObj->addOns->getOutputTags();
			}	
		}
		return $preparedData;
	}

	public static function extractReport(Response $response){
		$products=$response->getProducts();
		if(isset($products['CreditProfile'])){
			$report=$products['CreditProfile'];
			if(isset($report['InformationalMessage']['MessageNumber']) && $report['InformationalMessage']['MessageNumber']=='07'){
				throw new NoSuchRecordException;
			}
			return $report;
		} else {
			return $report;
		}
	}

	public static function mapDescription($report){
		if(isset($report['RiskModel'])) {
			require_once(__DIR__."/CodeMaps/ModelCodeFactors/index.php");
			if(isset($report['RiskModel'][0])){
				$riskModels=count($report['RiskModel']);
				for($i=0;$i<$riskModels;$i++){
					self::mapValues($report['RiskModel'][$i],$modelMap,'RiskModel');
				}
			} else {
				self::mapValues($report['RiskModel'],$modelMap,'RiskModel');
			}
		}
		if(isset($report['TradeLine'])) {
			require_once(__DIR__."/CodeMaps/AccountConditions.php");
			require_once(__DIR__."/CodeMaps/PaymentStatus.php");
			require_once(__DIR__."/CodeMaps/AccountPurpose.php");
			$codeMaps=['accountConditions'=>$accountConditions,'paymentStatus'=>$paymentStatus,'accountPurpose'=>$accountPurpose];
			if(isset($report['TradeLine'][0])){
				$tradeLines=count($report['TradeLine']);
				for($i=0;$i<$tradeLines;$i++){
					self::mapValues($report['TradeLine'][$i],$codeMaps,'TradeLine');
				}
			} else {
				self::mapValues($report['TradeLine'],$codeMaps,'TradeLine');
			}
		}
		if(isset($report['Inquiry'])) {
			require_once(__DIR__."/CodeMaps/KindOfBusiness.php");
			require_once(__DIR__."/CodeMaps/AccountPurpose.php");
			$codeMaps=['kindOfBusiness'=>$kindOfBusiness,'accountPurpose'=>$accountPurpose];
			if(isset($report['Inquiry'][0])){
				$inquiries=count($report['Inquiry']);
				for($i=0;$i<$inquiries;$i++){
					self::mapValues($report['Inquiry'][$i],$codeMaps,'Inquiry');
				}
			} else {
				self::mapValues($report['Inquiry'],$codeMaps,'Inquiry');
			}
		}
		if(isset($report['FraudServices'])) {
			require_once(__DIR__."/CodeMaps/SIC.php");
			require_once(__DIR__."/CodeMaps/FraudShieldIndicators.php");
			$codeMaps=['sic'=>$sic,'fraudShieldIndicators'=>$fraudShieldIndicators];
			if(isset($report['FraudServices'][0])){
				$inquiries=count($report['FraudServices']);
				for($i=0;$i<$inquiries;$i++){
					self::mapValues($report['FraudServices'][$i],$codeMaps,'FraudServices');
				}
			} else {
				self::mapValues($report['FraudServices'],$codeMaps,'FraudServices');
			}
		}
		return $report;
	}

	private static function mapValues(&$target,$source,$type){
		switch($type){
			case 'RiskModel':
				if($target['ScoreFactorCodeOne'] ?? false){
					$modelCode=$source[$target['ModelIndicator']['code']];
					require(__DIR__."/CodeMaps/ModelCodeFactors/{$modelCode}.php");
					$target['ScoreFactors']=[];
					foreach(['ScoreFactorCodeOne','ScoreFactorCodeTwo','ScoreFactorCodeThree','ScoreFactorCodeFour'] as $index=>$codeFactor){
						$code=sprintf('%02s',$target[$codeFactor]);
						unset($target[$codeFactor]);
					}
				}
			break;
			case 'TradeLine':
				$target['EnhancedPaymentData']['AccountCondition']['description']=$source['accountConditions'][sprintf('%02s',$target['EnhancedPaymentData']['AccountCondition']['code'])];
				$target['EnhancedPaymentData']['PaymentStatus']['description']=$source['paymentStatus'][sprintf('%02s',$target['EnhancedPaymentData']['AccountCondition']['code'])][sprintf('%02s',$target['EnhancedPaymentData']['PaymentStatus']['code'])];
				$target['EnhancedPaymentData']['AccountType']['description']=$source['accountPurpose'][sprintf('%02s',$target['EnhancedPaymentData']['AccountType']['code'])];
			break;
			case 'Inquiry':
				$target['KOB']['description']=$source['kindOfBusiness'][sprintf('%02s',$target['KOB']['code'])];
				$target['Type']['description']=$source['accountPurpose'][sprintf('%02s',$target['Type']['code'])];
			break;
			case 'FraudServices':
				$target['SIC']['description']=$source['sic'][$target['SIC']['code']];
				foreach($target['Indicator'] as $indicator){
					$target['Indicators'][$indicator]=$source['fraudShieldIndicators'][sprintf('%02s',$indicator)];
				}
				unset($target['Indicator']);
			break;
		}
	}
}