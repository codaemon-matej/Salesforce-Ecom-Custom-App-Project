<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Payment Library
 * @author Rohit Patil (rohitpatil30) @ Codaemon Softwares, Pune
 */

class Paycall {
    
    private $CI;  // CodeIgniter instance

    function __construct() {
        $this->CI= & get_instance();
        $this->CI->load->library('authnet_api');
        $this->CI->load->model('companies_model');
    }

    //https://developer.authorize.net/api/reference/index.html#customer-profiles-create-customer-profile
    public function AN_CustomerProfile($cust_data=array(),$cust_email='') {
        $result=array('status'=>false,'error_code'=>'','an_cust_profile_id'=>'','an_cust_payment_profile_id'=>'','msg'=>'');
        if(!empty($cust_data) && $cust_email!='') {
            $user_id=isset($cust_data['user_id']) ? $cust_data['user_id'] : 0;
            $company_id=isset($cust_data['company_id']) ? $cust_data['company_id'] : 0;
            $company_name=isset($cust_data['company_name']) ? $cust_data['company_name'] : '';
            $card_number=isset($cust_data['card_number']) ? $cust_data['card_number'] : '';
            $expiry_date=isset($cust_data['expiry_date']) ? date('Y-m',strtotime($cust_data['expiry_date'])) : '';
            $card_expiry_flag=(strtotime($expiry_date.'-'.date('d'))>strtotime(date('Y-m-d'))) ? true : false;
            $card_code=isset($cust_data['card_code']) ? $cust_data['card_code'] : '';
            $first_name=isset($cust_data['first_name']) ? $cust_data['first_name'] : '';
            if($user_id>0 && $company_id>0 && $company_name!='' && $card_number!='' && $expiry_date!='' && $card_expiry_flag && $card_code!='' && $first_name!='') {
                $sf_company_id=isset($cust_data['sf_company_id']) ? $cust_data['sf_company_id'] : '';
                $last_name=isset($cust_data['last_name']) ? $cust_data['last_name'] : '';
                $phone=isset($cust_data['phone']) ? $cust_data['phone'] : '';
                $address=isset($cust_data['address']) ? $cust_data['address'] : '';
                $city=isset($cust_data['city']) ? $cust_data['city'] : '';
                $zip_code=isset($cust_data['zip_code']) ? $cust_data['zip_code'] : '';
                $state=isset($cust_data['state']) ? $cust_data['state'] : '';
                $country=isset($cust_data['country']) ? $cust_data['country'] : '';

                $cust_pay_data=array();
                $cust_pay_data['company_name']=$company_name;
                $cust_pay_data['card_number']=$card_number;
                $cust_pay_data['expiry_date']=$expiry_date;
                $cust_pay_data['card_code']=$card_code;
                $cust_pay_data['first_name']=$first_name;
                $cust_pay_data['last_name']=$last_name;
                $cust_pay_data['phone']=$phone;
                $cust_pay_data['address']=$address;
                $cust_pay_data['city']=$city;
                $cust_pay_data['zip_code']=$zip_code;
                $cust_pay_data['state']=$state;
                $cust_pay_data['country']=$country;
                $cust_pay_data['cust_desc']=COMPPAYPROFILE.'_'.$company_id.'_'.$sf_company_id.'_By-'.$user_id.'_'.date('Y-m-d_H:i:s');
                $cust_pay_data['cust_id']=$company_id;
                $refId='CPP_'.time();
                $response=$this->CI->authnet_api->createCustomerProfile($cust_pay_data,$cust_email,$refId);
                if($response!=NULL && $response->getMessages()->getResultCode()=="Ok") {
                    $an_cust_profile_id=$response->getCustomerProfileId();
                    $paymentProfiles=$response->getCustomerPaymentProfileIdList();
                    $an_cust_payment_profile_id=$paymentProfiles[0];
                    $result['status']=true;
                    $result['an_cust_profile_id']=$an_cust_profile_id;
                    $result['an_cust_payment_profile_id']=$an_cust_payment_profile_id;
                    //$result['msg']='Payment Profile Created Successfully..!';
                    $result['msg']=$response->getMessages()->getMessage()[0]->getText();

                    $comp_pay_data=array();
                    $comp_pay_data['an_cust_profile_id']=$an_cust_profile_id;
                    $comp_pay_data['an_cust_payment_profile_id']=$an_cust_payment_profile_id;
                    $comp_pay_data['first_name']=$first_name;
                    $comp_pay_data['last_name']=$last_name;
                    $comp_pay_data['email']=$cust_email;
                    $comp_pay_data['contact_no']=$phone;
                    $comp_pay_data['card_last_num']='XXXX'.substr($card_number,-4);
                    $comp_pay_data['card_expiry']=$expiry_date;
                    $comp_pay_data['address']=$address;
                    $comp_pay_data['city']=$city;
                    $comp_pay_data['pincode']=$zip_code;
                    $comp_pay_data['state']=$state;
                    $comp_pay_data['country']=$country;
                    $comp_pay_data['updated_by']=$user_id;
                    $comp_payment_id=$this->CI->companies_model->update_company_payment_data($company_id,0,$comp_pay_data);

                    if($comp_payment_id>0) {
                        $updt_comp_data=array();
                        $updt_comp_data['comp_payment_detail_id']=$comp_payment_id;
                        $updt_comp_data['comp_payment_flag']=1;
                        $this->CI->companies_model->update_company_data($company_id,$updt_comp_data);
                    }
                } else {
                    $errorMessages=$response->getMessages()->getMessage();
                    $result['status']=false;
                    $result['error_code']=$errorMessages[0]->getCode();
                    $result['msg']=$errorMessages[0]->getText();
                }
            } else {
                $result['status']=false;
                if($user_id<1) $msg="Error: User Not Found..!";
                else if($company_id<1) $msg="Error: Company Not Found..!";
                else if($company_name=='') $msg="Error: Please Enter Company Name..!";
                else if($card_number=='') $msg="Error: Please Enter Card Number..!";
                else if($expiry_date=='') $msg="Error: Please Enter Card Expiry..!";
                else if(!$card_expiry_flag) $msg="Error: Please Enter Proper Card Expiry..!";
                else if($card_code=='') $msg="Error: Please Enter Card CVV/Code..!";
                else if($first_name=='') $msg="Error: Please Enter Card Holder Name..!";
                else $msg='Error: Please Try Again Later..!';
                $result['msg']=$msg;
            }
        } else {
            $result['status']=false;
            if($cust_email=='') $msg='Error: Please Enter Email-Id..!';
            else if(empty($cust_data)) $msg='Error: Data Not Found..!';
            else $msg='Error: Please Try Again Later..!';
            $result['msg']=$msg;
        }
        return $result;
    }

    //https://developer.authorize.net/api/reference/index.html#payment-transactions-charge-a-customer-profile
    public function AN_CustomerPayment($company_id=0,$amount=0,$user_id=0) {
        $result=array('status'=>false,'error_code'=>'','an_cust_profile_id'=>'','an_cust_payment_profile_id'=>'','an_trans_id'=>'','an_trans_code'=>'','msg'=>'');
        if($company_id>0 && $amount>0) {
            $comp_pay_profile_data=$this->CI->companies_model->get_company_payment_data($company_id);
            $custProfileId=isset($comp_pay_profile_data['an_cust_profile_id']) ? $comp_pay_profile_data['an_cust_profile_id'] : '';
            $custPayProfileId=isset($comp_pay_profile_data['an_cust_payment_profile_id']) ? $comp_pay_profile_data['an_cust_payment_profile_id'] : 0;
            $result['an_cust_profile_id']=$custProfileId;
            $result['an_cust_payment_profile_id']=$custPayProfileId;
            if($custProfileId!='' && $custPayProfileId!='') {
                $refId='CPT_'.time();
                $response=$this->CI->authnet_api->chargeCustomerProfile($custProfileId,$custPayProfileId,$amount,$refId);
                $trans_response=$response->getTransactionResponse();
                $an_trans_id=$an_trans_code=$an_trans_authcode=$error_code=$msg=$pay_status='';
                if($response!=NULL && $response->getMessages()->getResultCode()=="Ok" && $trans_response!=NULL && $trans_response->getMessages()!=NULL) {
                    $an_trans_code=$trans_response->getResponseCode();
                    $an_trans_authcode=$trans_response->getAuthCode();
                    $an_trans_id=$trans_response->getTransId();
                    $msg=$trans_response->getMessages()[0]->getDescription();
                    $result['status']=true;
                    $result['an_trans_id']=$an_trans_id;
                    $result['an_trans_code']=$an_trans_code;
                    $result['an_trans_authcode']=$an_trans_authcode;
                    $result['msg']=$msg;
                    $pay_status='Success';
                } else {
                    if($trans_response!=NULL && $trans_response->getErrors()!=NULL) {
                        $an_trans_code=$trans_response->getResponseCode();
                        $error_code=$trans_response->getErrors()[0]->getErrorCode();
                        $msg=$trans_response->getErrors()[0]->getErrorText();
                    } else {
                        $error_code=$response->getMessages()->getMessage()[0]->getCode();
                        $msg=$response->getMessages()->getMessage()[0]->getText();
                    }
                    $result['status']=false;
                    $result['error_code']=$error_code;
                    $result['an_trans_code']=$an_trans_code;
                    $result['msg']=$msg;
                    $pay_status='Fail';
                }
                $comp_pay_data=array();
                $comp_pay_data['company_id']=$company_id;
                $comp_pay_data['user_id']=$user_id;
                $comp_pay_data['order_id']='';
                $comp_pay_data['payment_amount']=$amount;
                $comp_pay_data['cust_profile_id']=$custProfileId;
                $comp_pay_data['cust_payment_profile_id']=$custPayProfileId;
                $comp_pay_data['payment_trans_id']=$an_trans_id;
                $comp_pay_data['payment_response_code']=$an_trans_code;
                $comp_pay_data['payment_auth_code']=$an_trans_authcode;
                $comp_pay_data['payment_error_code']=$error_code;
                $comp_pay_data['payment_result']=$msg;
                $comp_pay_data['payment_status']=$pay_status;
                $comp_payment_id=$this->CI->companies_model->track_order_payment($comp_pay_data);
                $result['order_pay_id']=$comp_payment_id;
            } else {
                $result['status']=false;
                $msg='Error: Customer Payment Profile not Found, Please Try Again Later..!';
                $result['msg']=$msg;
            }
        } else {
            $result['status']=false;
            if($amount<=0) $msg='Error: Please Enter Proper Amount..!';
            else if($company_id<=0) $msg='Error: Company Not Found..!';
            else $msg='Error: Please Try Again Later..!';
            $result['msg']=$msg;
        }
        return $result;
    }

    //https://developer.authorize.net/api/reference/#payment-transactions-credit-a-bank-account
    public function AN_creditBankAccount($paidto_user_data=array(),$amount=0,$user_id=0) {
        $result=array('status'=>false,'error_code'=>'','user_pay_detail_id'=>'','user_pay_id'=>'','an_trans_id'=>'','an_trans_code'=>'','msg'=>'');
        $paidto_user_id=isset($paidto_user_data['paidto_user_id']) ? $paidto_user_data['paidto_user_id'] : 0;
        $paidto_user_type=isset($paidto_user_data['paidto_user_type']) ? $paidto_user_data['paidto_user_type'] : '';
        if($paidto_user_id>0 && $amount>0) {
            $comments=isset($paidto_user_data['comments']) ? $paidto_user_data['comments'] : '';
            $user_pay_profile_data=$this->CI->companies_model->get_user_bank_details($paidto_user_id,$paidto_user_type);
            if(!empty($user_pay_profile_data)) {
                $user_pay_detail_id=isset($user_pay_profile_data['id']) ? $user_pay_profile_data['id'] : '';
                $name_on_account=isset($user_pay_profile_data['name_on_account']) ? $user_pay_profile_data['name_on_account'] : '';
                $account_number=isset($user_pay_profile_data['account_no']) ? $user_pay_profile_data['account_no'] : 0;
                $account_type=isset($user_pay_profile_data['account_type']) ? $user_pay_profile_data['account_type'] : '';
                $echeck_type=isset($user_pay_profile_data['echeck_type']) ? $user_pay_profile_data['echeck_type'] : '';
                $bank_name=isset($user_pay_profile_data['bank_name']) ? $user_pay_profile_data['bank_name'] : '';
                $bank_rounting_number=isset($user_pay_profile_data['bank_routing_no']) ? $user_pay_profile_data['bank_routing_no'] : '';
                $result['user_pay_detail_id']=$user_pay_detail_id;
                if($name_on_account!='' && $account_number!='' && $account_type!='' && $echeck_type!='' && $bank_name!='' && $bank_rounting_number!='') {
                    $invoice_number=time();
                    $invoice_desc=VENDBANKPAY.$comments;
                    $bank_data=array();
                    $bank_data['name_on_account']=$name_on_account;
                    $bank_data['account_number']=$account_number;
                    $bank_data['account_type']=$account_type;
                    $bank_data['echeck_type']=$echeck_type;
                    $bank_data['bank_name']=$bank_name;
                    $bank_data['bank_rounting_number']=$bank_rounting_number;
                    $bank_data['invoice_number']=$invoice_number;
                    $bank_data['invoice_desc']=$invoice_desc;
                    $refId='CBA_'.time();
                    $response=$this->CI->authnet_api->creditBankAccount($bank_data,$amount,$refId);
                    $trans_response=$response->getTransactionResponse();
                    $an_trans_id=$an_trans_code=$an_trans_authcode=$error_code=$msg=$pay_status='';
                    if($response!=NULL && $response->getMessages()->getResultCode()=="Ok" && $trans_response!=NULL && $trans_response->getMessages()!=NULL) {
                        $an_trans_code=$trans_response->getResponseCode();
                        $an_trans_authcode=$trans_response->getAuthCode();
                        $an_trans_id=$trans_response->getTransId();
                        //$trans_response->getMessages()[0]->getCode();
                        $msg=$trans_response->getMessages()[0]->getDescription();
                        $result['status']=true;
                        $result['an_trans_id']=$an_trans_id;
                        $result['an_trans_code']=$an_trans_code;
                        $result['an_trans_authcode']=$an_trans_authcode;
                        $result['msg']=$msg;
                        $pay_status='Success';
                    } else {
                        if($trans_response!=NULL && $trans_response->getErrors()!=NULL) {
                            $an_trans_code=$trans_response->getResponseCode();
                            $error_code=$trans_response->getErrors()[0]->getErrorCode();
                            $msg=$trans_response->getErrors()[0]->getErrorText();
                        } else {
                            $error_code=$response->getMessages()->getMessage()[0]->getCode();
                            $msg=$response->getMessages()->getMessage()[0]->getText();
                        }
                        $result['status']=false;
                        $result['error_code']=$error_code;
                        $result['an_trans_code']=$an_trans_code;
                        $result['msg']=$msg;
                        $pay_status='Fail';
                    }

                    $user_pay_data=array();
                    $user_pay_data['paidto_user_id']=$paidto_user_id;
                    $user_pay_data['user_type']=$paidto_user_type;
                    $user_pay_data['payment_amount']=$amount;
                    $user_pay_data['invoice_no']=$invoice_number;
                    $user_pay_data['invoice_desc']=$invoice_desc;
                    $user_pay_data['user_pay_detail_id']=$user_pay_detail_id;
                    $user_pay_data['payment_trans_id']=$an_trans_id;
                    $user_pay_data['payment_response_code']=$an_trans_code;
                    $user_pay_data['payment_auth_code']=$an_trans_authcode;
                    $user_pay_data['payment_error_code']=$error_code;
                    $user_pay_data['payment_result']=$msg;
                    $user_pay_data['payment_status']=$pay_status;
                    $user_pay_data['paidby_user_id']=$user_id;
                    $user_payment_id=$this->CI->companies_model->track_user_payment($user_pay_data);
                    $result['user_pay_id']=$user_payment_id;
                } else {
                    $result['status']=false;
                    if($name_on_account=='') $msg='Error: Invalid Paid-To User Name on Account..!';
                    else if($account_number=='')  $msg='Error: Invalid Paid-To User Bank Account Number..!';
                    else if($account_type=='')  $msg='Error: Invalid Paid-To User Bank Account Type..!';
                    else if($echeck_type=='')  $msg='Error: Invalid Paid-To User e-Check Type..!';
                    else if($bank_name=='')  $msg='Error: Invalid Paid-To User Bank Name..!';
                    else if($bank_rounting_number=='')  $msg='Error: Invalid Paid-To User Bank Routing Number..!';
                    else $msg='Error: Invalid Paid-To User Bank Details, Please Try Again Later..!';
                    $result['msg']=$msg;
                }
            } else {
                $result['status']=false;
                $msg='Error: Paid-To User Bank Details not Found, Please Try Again Later..!';
                $result['msg']=$msg;
            }
        } else {
            $result['status']=false;
            if($amount<=0) $msg='Error: Please Enter Proper Amount..!';
            else if($paidto_user_id<=0) $msg='Error: Paid-To User Not Found..!';
            else $msg='Error: Please Try Again Later..!';
            $result['msg']=$msg;
        }
        return $result;
    }


}
