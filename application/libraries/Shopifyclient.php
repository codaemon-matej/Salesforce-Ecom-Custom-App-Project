<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @Shopify Integration API Library File
 * Rohit=>Making Shopify Integrations using API Connections & Calls
 */

class Shopifyclient {

    private $CI;
    private $assigned_details_flag=false;
    private $apikey='';
    private $passwd='';
    private $store='';

    //Rohit=> Assigning Environment-wise Shopify credentials
    function __construct($params=array()) {
        $this->CI= & get_instance();
        $this->CI->config->load('shopify');
        $shopify_config=$this->CI->config->item('shopify_details');
        if(isset($shopify_config[ENVIRONMENT]) && !empty($shopify_config[ENVIRONMENT])) {
            $shopify_details=$shopify_config[ENVIRONMENT];
            if(isset($shopify_details['apikey']) && trim($shopify_details['apikey']!='') && isset($shopify_details['passwd']) && trim($shopify_details['passwd']!='') && isset($shopify_details['store']) && trim($shopify_details['store']!='')) {
                $this->assigned_details_flag=true;
                $this->apikey=trim($shopify_details['apikey']);
                $this->passwd=trim($shopify_details['passwd']);
                $this->store=trim($shopify_details['store']);
            }
        }
    }

    //Rohit=> Call & Sending the Shopify Data via API
    public function call_shopify_api($function='',$method='',$data=array()) {
        $response='Initial';
        if($this->apikey!='' && $this->passwd!='' && $this->store!='' && $function!='' && $method!='') {
            $api_link="https://".$this->apikey.":".$this->passwd."@".$this->store.".myshopify.com/admin/".$function.".json";
            $ch=curl_init($api_link);
            curl_setopt($ch,CURLOPT_HEADER,false);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type:application/json'));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_VERBOSE,false);
            curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
            if(!empty($data)) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($ch);
            curl_close($ch);
        }
        return $response;
    }

}
