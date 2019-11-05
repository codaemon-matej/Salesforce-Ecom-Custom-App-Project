<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter CommonHelper Class
 *
 * All Operations associated with Project & Commonly Used
 *
 * @package	CodeIgniter
 * @category	Helper
 * @author	Rohit Patil (rohitpatil30) @ Codaemon Softwares, Pune
 */

//Rohit=> Checking of Backend-Admin Login & its Session
if(!function_exists('check_admin_login')) {
    function check_admin_login() {
        $CI = & get_instance();
        header("Cache-Control: max-age=300, must-revalidate");
        $admin_login_status=$CI->session->userdata['userdata']['logged_in'];
        if ($admin_login_status != true) {
            redirect(base_url().'admin');
            exit();
        }
    }
}

//Rohit=> Checking of Frontend-User Login & its Session
if(!function_exists('check_user_login')) {
    function check_user_login() {
        $CI = & get_instance();
        $user_login_status = $CI->session->userdata(APP_SESSION);
        if(isset($user_login_status[APP_LOGIN_STATUS])) {
            if($user_login_status[APP_LOGIN_STATUS] === FALSE) {
                $url=isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                if($url!='') setcookie("returnurl",$url,time()+500,"/");
                redirect(base_url()."userlogin/login");
            }
        } else {
            $url=isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if($url!='') setcookie("returnurl",$url,time()+500,"/");
            redirect(base_url()."userlogin/login");
        }
    }
}

//Rohit=> Sending an Email Functions
if(!function_exists('send_email')) {
    function send_email($emailto='',$subject='',$msg='',$from_email='',$sender_name='') {
        $return=false;
	if($emailto!='' && $subject!='' && $msg!='') {
            $CI = & get_instance();
            $CI->config->load('email');
            $email_config=$CI->config->item('email_details');
            if(isset($email_config[ENVIRONMENT]) && !empty($email_config[ENVIRONMENT])) {
                $email_details=$email_config[ENVIRONMENT];
                if(isset($email_details['smtp_user']) && trim($email_details['smtp_user']!='') && isset($email_details['smtp_pass']) && trim($email_details['smtp_pass']!='')) {
                    $smtp_host=isset($email_details['smtp_host']) ? trim($email_details['smtp_host']) : '';
                    $smtp_user=trim($email_details['smtp_user']);
                    $smtp_pass=trim($email_details['smtp_pass']);
                    $smtp_port=isset($email_details['smtp_port']) ? trim($email_details['smtp_port']) : 465;
                    $bcc_email=isset($email_details['bcc_email']) ? $email_details['bcc_email'] : array();
                    $CI->load->library('email');
                    $CI->email->initialize(array(
                    'protocol' => 'smtp',
                    'smtp_host' => $smtp_host,
                    'smtp_user' => $smtp_user,
                    'smtp_pass' => $smtp_pass,
                    'smtp_port' => $smtp_port,
                    'crlf' => "\r\n",
                    'newline' => "\r\n",
                    'mailtype' => 'html',
                    'charset' => 'iso-8859-1',
                    'wordwrap' => TRUE
                    ));
                    if($from_email=='') $from_email=SENDER_EMAIL;
                    if($sender_name=='') $sender_name=SENDER_NAME;
                    $CI->email->from($from_email,$sender_name);
                    $CI->email->to($emailto);
                    if(!empty($bcc_email)) $CI->email->bcc($bcc_email);
                    $CI->email->subject($subject);
                    $CI->email->message($msg);
                    $return=$CI->email->send();
                }
            }
	}
        return $return;
    }
}

//Rohit=> Getting the Client IP Address
if(!function_exists('get_client_ip')) {
    function get_client_ip() {
        $ip_addr='UNKNOWN';
        if(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='') $ip_addr=$_SERVER['HTTP_CLIENT_IP'];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='') $ip_addr=$_SERVER['HTTP_X_FORWARDED_FOR'];
        if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED']!='') $ip_addr=$_SERVER['HTTP_X_FORWARDED'];
        if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR']!='') $ip_addr=$_SERVER['HTTP_FORWARDED_FOR'];
        if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']!='') $ip_addr=$_SERVER['HTTP_FORWARDED'];
        if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']!='') $ip_addr=$_SERVER['REMOTE_ADDR'];
        return $ip_addr;
    }
}

//Rohit=> Forms Pagination configuration
if(!function_exists('geneate_pagination')) {
    function geneate_pagination($page_data = array()) {
        $class_name = isset($page_data['class_name']) ? $page_data['class_name'] : '';
        $func_name = isset($page_data['func_name']) ? $page_data['func_name'] : '';
        $total_rows = isset($page_data['total_rows']) ? $page_data['total_rows'] : 0;
        $per_page = isset($page_data['per_page']) ? $page_data['per_page'] : 10;
        $uri_segment = isset($page_data['uri_segment']) ? $page_data['uri_segment'] : 3;

        $config = array();
        $config['base_url'] = base_url().$class_name."/".$func_name;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        //$config["uri_segment"] = $uri_segment;
        $config["num_links"] = 2;
        $config['use_page_numbers'] = TRUE;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;
        $config['next_link'] = 'Next &rarr;';
        $config['next_tag_open'] = '<li class="next">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '&larr; Previous';
        $config['prev_tag_open'] = '<li class="previous">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '</span></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $CI = & get_instance();
        $CI->load->library('pagination');
        $CI->pagination->initialize($config);
        return $CI->pagination->create_links();
    }
}

//Rohit=> sForce Query Execution with Token
if(!function_exists('sforce_qry_execute')) {
    function sforce_qry_execute($query='',$access_token='',$instance_url='') {
        $result = array();
        if($query!='') {
            $CI = & get_instance();
            $user_session = $CI->session->userdata(APP_SESSION);
            if($access_token=='') $access_token = isset($user_session['access_token']) ? $user_session['access_token'] : '';
            if($instance_url=='') $instance_url = isset($user_session['instance_url']) ? $user_session['instance_url'] : '';
            if($access_token!='' && $instance_url!='') {
                $tmp_query_url = isset($user_session['sforce_qry_url']) ? $user_session['sforce_qry_url'] : '';
                if($tmp_query_url!='') $query_url = $tmp_query_url;
                else $query_url = SFORCE_QRY_URL;
                $url = $instance_url.$query_url."?q=".urlencode($query);
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token"));
                $json_response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($json_response, true);
            }
        }
        return $result;
    }
}

//Rohit=> sForce Query Execution with Token
if(!function_exists('sforce_obj_execute')) {
    function sforce_obj_execute($table='',$sf_data=array(),$access_token='',$instance_url='') {
        $result = array();
        if($table!='' && !empty($sf_data)) {
            $CI = & get_instance();
            $user_session = $CI->session->userdata(APP_SESSION);
            if($access_token=='') $access_token = isset($user_session['access_token']) ? $user_session['access_token'] : '';
            if($instance_url=='') $instance_url = isset($user_session['instance_url']) ? $user_session['instance_url'] : '';
            if($access_token!='' && $instance_url!='') {
                $tmp_obj_url = isset($user_session['sforce_obj_url']) ? $user_session['sforce_obj_url'] : '';
                if($tmp_obj_url!='') $query_url = $tmp_obj_url;
                else $query_url = SFORCE_OBJ_URL;
                $url = $instance_url.$query_url.$table;
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token","Content-type: application/json"));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($sf_data));
                $json_response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($json_response, true);
            }
        }
        return $result;
    }
}

//Rohit=> Generate the Random String
if(!function_exists('random_string')) {
    function random_string($type='',$length=5) {
        if ($type == 'numeric')
            $string = "0123456789";
        else if ($type == 'alphabet')
            $string = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
        else
            $string = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";
        $return = array();
        $stringLength = strlen($string) - 1;
        for($i = 0; $i < $length; $i++) {
            $n = rand(0, $stringLength);
            $return[] = $string[$n];
        }
        return implode($return);
    }
}

//Rohit=> Get Company's Budget & Settings
if(!function_exists('get_company_budget')) {
   function get_company_budget($company_id=0,$user_id=0) {
       $comp_budget_data=array();
       if($company_id>0) {
           $CI= & get_instance();
           $CI->load->database();
           $CI->db->select('max_gift_limit,used_gift_limit');
           $CI->db->from('global_settings');
           $CI->db->where('company_id',$company_id);
           //if($user_id>0) $CI->db->where('user_id',$user_id);
           $CI->db->where('status',1);
           $CI->db->order_by('id','DESC');
           $comp_budget_data=$CI->db->get()->row_array();
       }
       return $comp_budget_data;
   }
}

//Rohit=> Fetch the Email Template
if(!function_exists('get_email_template')) {
    function get_email_template($template_name='') {
        $email_template_data=array();
        if($template_name!='') {
            $CI= & get_instance();
            $CI->load->database();
            $CI->db->select('*');
            $CI->db->from('email_template');
            $CI->db->where('name',trim($template_name));
            $CI->db->where('status',1);
            $CI->db->order_by('id','DESC');
            $email_template_data=$CI->db->get()->row_array();
        }
        return $email_template_data;
    }
}

//Rohit=> Track the User Activity Log
if(!function_exists('logged_user_activity')) {
    function logged_user_activity($log_data=array()) {
        $status=false;
        if(!empty($log_data)) {
            $CI= & get_instance();
            $CI->load->database();
            $log_data['function_name']=$CI->router->fetch_class()."/".$CI->router->fetch_method();
            $log_data['activity_datetime']=date('Y-m-d H:i:s');
            $log_data['ip_address']=get_client_ip();
            $status=$CI->db->insert('user_activity_log',$log_data);
            //$status=$CI->db->insert_id();
        }
        return $status;
    }
}

//Rohit=> Fetch the Product Details
if(!function_exists('productDetails')) {
    function productDetails($product_id=0) {
        $product_data=array();
        if($product_id>0) {
            $CI= & get_instance();
            $CI->load->database();
            $CI->db->select('p.*,c.name as category_name,v.username as vendor_username,v.email as vendor_email');
            $CI->db->from('products as p');
            $CI->db->join('category as c','c.id=p.category_id');
            $CI->db->join('vendor as v','v.id=p.vendor_id');
            $CI->db->where('p.id',$product_id);
            $product_data=$CI->db->get()->row_array();
        }
        return $product_data;
    }
}


