<?php
/**
 *	Based on CIS Technical Documents File: Appendix 03-21-2016.pdf
 *	Section: H. ECOA Codes
 *	Page No: 87
 */
$ecoa=[
	'0'=>["shortDescription"=>"Undesignated"	,"description"=>"Reported by Experian only."],
	'1'=>["shortDescription"=>"Individual"		,"description"=>"This individual has contractual responsibility for this account and is primarily responsible for its payment. (Termination code H to be used only in cases of mortgage loans being assumed by others.)"],
	'2'=>["shortDescription"=>"Joint Account"	,"description"=>"Contractual Responsibility -This individual is expressly obligated to repay all debts arising on this account by reason of having signed an agreement to that effect. There are other people associated with this account who may or may not have contractual responsibility."],
	'3'=>["shortDescription"=>"Authorized User"	,"description"=>"This individual is an authorized user of this account; another individual has contractual responsibility."],
	'4'=>["shortDescription"=>"Joint Account" 	,"description"=>"This individual participates in this account. The association cannot be distinguished between Joint Account - Contractual Responsibility or Authorized User."],
	'5'=>["shortDescription"=>"Cosigner"		,"description"=>"This individual has guaranteed this account and assumes responsibility should signer default. This code is only used in conjunction with Code 7 - Signer."],
	'6'=>["shortDescription"=>"On Behalf Of"	,"description"=>"This individual has signed an application for the purpose of securing credit for another individual, other than spouse."],
	'7'=>["shortDescription"=>"Signer"			,"description"=>"This individual is responsible for this account, which is guaranteed by a Cosigner. This code is to be used in lieu of codes 2 and 3 when there is a Code 5 - Signer."],
	'A'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 0 (Undesignated) by the credit grantor. Reported by Experian only."],
	'B'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 2 (Joint Account - Contractual Responsibility) by the credit grantor."],
	'C'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 3 (Authorized User) by the credit grantor."],
	'D'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 4 (Joint Account) by the credit grantor."],
	'E'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 5 (Co-Signer) by the credit grantor."],
	'F'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 6 (On Behalf Of) by the credit grantor."],
	'G'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 7 (Signer) by the credit grantor."],
	'H'=>["shortDescription"=>"Terminated" 		,"description"=>"Former account association was reported as a 1 (Individual) by the credit grantor. Used only in cases of mortgage loans being assumed by others."],
	'X'=>["shortDescription"=>"Deceased"		,"description"=>"This individual has been reported as deceased. There may or may not be other people associated with this account."]
];