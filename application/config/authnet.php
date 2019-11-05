<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Configuration File for Authorize Dot Net Payment Integration
 * Rohit=> Assigning Authorize Dot Net details
 */

$config['auth_net_details']=array(
    'production'=>array(
        'api_login'=>'',
        'transaction_key'=>'',
        'sandbox'=>FALSE,
        'timezone'=>''
    ),
    'testing'=>array(
        'api_login'=>'XXXX',
        'transaction_key'=>'XXXX',
        'sandbox'=>TRUE,
        'timezone'=>''
    ),
    'development'=>array(
        'api_login'=>'XXXX',
        'transaction_key'=>'XXXX',
        'sandbox'=>TRUE,
        'timezone'=>''
    ),
    'devel'=>array(
        'api_login'=>'XXXX',
        'transaction_key'=>'XXXX',
        'sandbox'=>TRUE,
        'timezone'=>''
    ),
    'local'=>array(
        'api_login'=>'',
        'transaction_key'=>'',
        'sandbox'=>TRUE,
        'timezone'=>''
    )
);
