<?php
namespace Experian;

class AddOns {

	private $data=[];
	private $outputTags=[];

	private $riskModels = [
		'VantageScore',
		'VantageScore3',
		'NationalRisk',
		'FICOInstall2',
		'FICO8',
		'BankruptcyPLUS',
		'IncomeInsight',
		'CollectScore'
	];

	public function hasRequests(){
		return count($this->data)>0;
	}

	public function hasOutputTags(){
		return count($this->outputTags)>0;
	}

	public function markNewConsumer($mark=true){
		$this->setAddOnRequest('NewConsumer',$mark);
	}

	public function enableDirectCheck($mark=true){
		$this->setAddOnRequest('DirectCheck',$mark);
	}

	public function enableFraudShield($mark=true){
		$this->setAddOnRequest('FraudShield',$mark);
	}

	public function enableAutoProfileSummary($mark=true){
		$this->setAddOnRequest('AutoProfileSummary',$mark);
	}

	public function enableProfileSummary($mark=true){
		$this->setAddOnRequest('ProfileSummary',$mark);
	}

	public function enableRiskModel($modelName,$mark=true){
		if(!in_array($modelName,$this->riskModels))
			throw new \Exception('Unknown Risk Model',404);
		$this->setAddOnRequest($modelName,$mark,'RiskModels');
	}

	/**
	 *	@param riskModels
	 *	@param noticeType 		 	Possible values are := Mortgage,General
	 *	@param expandedHistory
	 */
	public function enableCreditScoreExceptionNotice($riskModels,$noticeType,$expandedHistory=true){
		$this->data['CreditScoreExceptionNotice'] =	[
					'NoticeType' => $noticeType,
					'RiskModels' => [],
					'ExpandedHistory' => $expandedHistory?'Y':'N'
				];
		foreach($riskModels as $riskModel){
			$this->enableRiskModel($riskModel);
			$this->data['CreditScoreExceptionNotice']['RiskModels'][]=$riskModel;
		}
	}

	public function getAllDemoGraphics($mark=true){
		$this->setAddOnRequest('DemographicsAll',$mark,'DemographicBand');
		$this->outputTags['Demographics'] = 'Y';
	}

	public function setAddOnRequest($reqName,$value,$parentProperty=false){
		if($parentProperty) {
			if ($value){
				if(!isset($this->data[$parentProperty]))
					$this->data[$parentProperty]=[];
				$this->data[$parentProperty][$reqName]='Y';
			} else {
				if(count($this->data[$parentProperty])<=1){
					unset($this->data[$parentProperty]);
				} else {
					unset($this->data[$parentProperty][$reqName]);
				}
			}
		} else if($value){
			$this->data[$reqName]='Y';
		} else {
			unset($this->data[$reqName]);
		}
	}

	public function getAddOnData(){
		return ['AddOns'=> $this->data];
	}

	public function getOutputTags(){
		return $this->outputTags;	
	}

	/**
	 *	Need to work upon
	 */
	// 'CustomRRDashKeyword' => ''			//	RR- DXP1 for Production,RR- XXP1 for UAT
}