<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter Authorize Dot Net API Class
 *
 * Permits to make Authorize.Net Integration via API
 *
 * @package	CodeIgniter
 * @category	Libraries
 * @author	Rohit Patil (rohitpatil30) @ Codaemon Softwares, Pune
 * @link        https://developer.authorize.net/api/reference/index.html
 */

//Sandbox url => https://apitest.authorize.net/xml/v1/request.api
//Live url => https://api.authorize.net/xml/v1/request.api

require_once APPPATH.'third_party/AuthDotNet/autoload.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class Authnet_api {

    private $CI;  // CodeIgniter instance
    private $api_login='';  // Merchant API Login Id
    private $transaction_key='';  // Merchant Account Trans key
    private $sandbox=FALSE;  // true means Sandbox & false means Live
    private $merchantAuth=NULL;  // true means Sandbox & false means Live

    public function __construct($params=array()) {
        $this->CI= & get_instance();
        $this->CI->config->load('authnet');
        $authnet_config=$this->CI->config->item('auth_net_details');
        if(isset($authnet_config[ENVIRONMENT]) && !empty($authnet_config[ENVIRONMENT])) {
            $authnet_details=$authnet_config[ENVIRONMENT];
            if(isset($authnet_details['api_login']) && trim($authnet_details['api_login']!='') && isset($authnet_details['transaction_key']) && trim($authnet_details['transaction_key']!='')) {
                $this->api_login=trim($authnet_details['api_login']);
                $this->transaction_key=trim($authnet_details['transaction_key']);
                if(isset($authnet_details['sandbox'])) $this->sandbox=$authnet_details['sandbox'];
                if(isset($authnet_details['timezone']) && trim($authnet_details['timezone'])!='') date_default_timezone_set(trim($authnet_details['timezone']));

                if($this->api_login!='' && $this->transaction_key!='') {
                    $this->merchantAuth = new AnetAPI\MerchantAuthenticationType();
                    $this->merchantAuth->setName($this->api_login);
                    $this->merchantAuth->setTransactionKey($this->transaction_key);
                }
            }
        }
    }

    private function authNetAPICall($controller) {
        if($this->sandbox) $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        else $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        return $response;
    }

    public function createCustomerProfile($cust_data=array(),$cust_email='',$refId='') {
        if($this->merchantAuth!==NULL && $cust_email!='' && !empty($cust_data)) {
            $creditCard = new AnetAPI\CreditCardType();
            if(isset($cust_data['card_number']) && trim($cust_data['card_number'])!='')  $creditCard->setCardNumber(trim($cust_data['card_number']));
            if(isset($cust_data['expiry_date']) && trim($cust_data['expiry_date'])!='')  $creditCard->setExpirationDate(trim($cust_data['expiry_date']));
            if(isset($cust_data['card_code']) && trim($cust_data['card_code'])!='')  $creditCard->setCardCode(trim($cust_data['card_code']));
            $paymentCreditCard = new AnetAPI\PaymentType();
            $paymentCreditCard->setCreditCard($creditCard);

            $billto = new AnetAPI\CustomerAddressType();
            if(isset($cust_data['first_name']) && trim($cust_data['first_name'])!='')  $billto->setFirstName(trim($cust_data['first_name']));
            if(isset($cust_data['last_name']) && trim($cust_data['last_name'])!='')  $billto->setLastName(trim($cust_data['last_name']));
            if(isset($cust_data['phone']) && trim($cust_data['phone'])!='')  $billto->setPhoneNumber(trim($cust_data['phone']));
            if(isset($cust_data['fax']) && trim($cust_data['fax'])!='')  $billto->setfaxNumber(trim($cust_data['fax']));
            if(isset($cust_data['company_name']) && trim($cust_data['company_name'])!='')  $billto->setCompany(trim($cust_data['company_name']));
            if(isset($cust_data['address']) && trim($cust_data['address'])!='')  $billto->setAddress(trim($cust_data['address']));
            if(isset($cust_data['city']) && trim($cust_data['city'])!='')  $billto->setCity(trim($cust_data['city']));
            if(isset($cust_data['zip_code']) && trim($cust_data['zip_code'])!='')  $billto->setZip(trim($cust_data['zip_code']));
            if(isset($cust_data['state']) && trim($cust_data['state'])!='')  $billto->setState(trim($cust_data['state']));
            if(isset($cust_data['country']) && trim($cust_data['country'])!='')  $billto->setCountry(trim($cust_data['country']));

            $customerShippingAddress=array();
            $shippingprofiles[] = $customerShippingAddress;

            $paymentprofile = new AnetAPI\CustomerPaymentProfileType();
            $paymentprofile->setCustomerType('individual');
            $paymentprofile->setBillTo($billto);
            $paymentprofile->setPayment($paymentCreditCard);
            $paymentprofile->setDefaultpaymentProfile(true);
            $paymentprofiles[] = $paymentprofile;

            $customerProfile = new AnetAPI\CustomerProfileType();
            if(isset($cust_data['cust_desc']) && trim($cust_data['cust_desc'])!='')  $customerProfile->setDescription(trim($cust_data['cust_desc']));
            if(isset($cust_data['cust_id']) && trim($cust_data['cust_id'])!='')  $customerProfile->setMerchantCustomerId(trim($cust_data['cust_id']));
            $customerProfile->setEmail($cust_email);
            $customerProfile->setpaymentProfiles($paymentprofiles);
            //$customerProfile->setShipToList($shippingprofiles);

            $request = new AnetAPI\CreateCustomerProfileRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            if($refId!='') $request->setRefId($refId);
            $request->setProfile($customerProfile);
    
            $controller = new AnetController\CreateCustomerProfileController($request);
            $response = $this->authNetAPICall($controller);
            return $response;
        } else return false;
    }

    public function chargeCustomerProfile($custProfileId='',$custPayProfileId='',$amount=0.0,$refId='') {
        if($this->merchantAuth!==NULL && $custProfileId!='' && $custPayProfileId!='' && $amount>0) {
            $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
            $profileToCharge->setCustomerProfileId($custProfileId);
            $paymentProfile = new AnetAPI\PaymentProfileType();
            $paymentProfile->setPaymentProfileId($custPayProfileId);
            $profileToCharge->setPaymentProfile($paymentProfile);

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount($amount);
            $transactionRequestType->setProfile($profileToCharge);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            if($refId!='') $request->setRefId($refId);
            $request->setTransactionRequest($transactionRequestType);
            $controller = new AnetController\CreateTransactionController($request);
            $response = $this->authNetAPICall($controller);
            return $response;
        } else return false;
    }

    public function creditBankAccount($bank_data=array(),$amount=0.0,$refId='') {
        if($this->merchantAuth!==NULL && !empty($bank_data) && $amount>0) {
            $bankAccount = new AnetAPI\BankAccountType();
            if(isset($bank_data['name_on_account']) && trim($bank_data['name_on_account'])!='') $bankAccount->setNameOnAccount(trim($bank_data['name_on_account']));
            if(isset($bank_data['account_number']) && trim($bank_data['account_number'])!='') $bankAccount->setAccountNumber(trim($bank_data['account_number']));
            if(isset($bank_data['account_type']) && trim($bank_data['account_type'])!='') $bankAccount->setAccountType(trim($bank_data['account_type']));
            if(isset($bank_data['echeck_type']) && trim($bank_data['echeck_type'])!='') $bankAccount->setEcheckType(trim($bank_data['echeck_type']));
            if(isset($bank_data['bank_name']) && trim($bank_data['bank_name'])!='') $bankAccount->setBankName(trim($bank_data['bank_name']));
            if(isset($bank_data['bank_rounting_number']) && trim($bank_data['bank_rounting_number'])!='') $bankAccount->setRoutingNumber(trim($bank_data['bank_rounting_number']));
            $paymentBank= new AnetAPI\PaymentType();
            $paymentBank->setBankAccount($bankAccount);

            $order = new AnetAPI\OrderType();
            if(isset($bank_data['invoice_number']) && trim($bank_data['invoice_number'])!='') $order->setInvoiceNumber(trim($bank_data['invoice_number']));
            if(isset($bank_data['invoice_desc']) && trim($bank_data['invoice_desc'])!='') $order->setDescription(trim($bank_data['invoice_desc']));

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("refundTransaction");
            $transactionRequestType->setAmount($amount);
            $transactionRequestType->setPayment($paymentBank);
            $transactionRequestType->setOrder($order);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            if($refId!='') $request->setRefId($refId);
            $request->setTransactionRequest($transactionRequestType);
            $controller = new AnetController\CreateTransactionController($request);
            $response=$this->authNetAPICall($controller);
            return $response;
        } else return false;
    }

	
}
