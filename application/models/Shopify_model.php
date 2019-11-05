<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Rohit=> Model file for Integration of Shopify API
 */

class Shopify_model extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    //Rohit => fetching All the Products
    public function get_product($prod_id='') {
        $prd_data=array();
        if($prod_id!='') {
            $this->db->select('p.*,v.username,c.name as cat_name,c.shopify_collect_id,c.shopify_api_flag as cat_api_flag');
            $this->db->from('products as p');
            $this->db->join('vendor as v','v.id=p.vendor_id');
            $this->db->join('category as c','c.id=p.category_id');
            $this->db->where('p.id',$prod_id);
            $this->db->where('p.status',1);
            $prd_data=$this->db->get()->row_array();
        }
        return $prd_data;
    }

    //Rohit=> Fetching All the Products Category
    public function get_collection($collect_id='') {
        $collect_data=array();
        if($collect_id!='') {
            $this->db->select('name,shopify_collect_id,shopify_api_flag');
            $this->db->from('category');
            $this->db->where('id',$collect_id);
            $this->db->where('status',1);
            $collect_data=$this->db->get()->row_array();
        }
        return $collect_data;
    }

    //Rohit=> Updating the Shopify Collection Id to associated category
    public function update_collection($collect_id='',$data=array()) {
        if($collect_id!='' && !empty($data)) {
            $this->db->where('id',$collect_id);
            $this->db->update('category',$data);
            return true;
        } else return false;
    }

    //Rohit=> Updating the Shopify Product Id to associated Product
    public function update_product($prod_id='',$data=array()) {
        if($prod_id!='' && !empty($data)) {
            $this->db->where('id',$prod_id);
            $this->db->update('products',$data);
            return true;
        } else return false;
    }

    //Rohit=> Get The User Details
    public function get_user_details($user_id='') {
        $user_data=array();
        if($user_id!='') {
            $this->db->select('f_name,l_name,email');
            $this->db->from('user_details');
            $this->db->where('user_id',$user_id);
            $this->db->where('status',1);
	    $this->db->order_by('id','DESC');
            $user_data=$this->db->get()->row_array();
        }
        return $user_data;
    }

    public function get_order_details($ord_id= '')
    {
        $order_data = array();
        if($ord_id!='')
        {
            $this->db->select('od.*,p.name,p.price,p.shopify_varient_id');
            $this->db->from('order_details as od');
            $this->db->join('products as p','od.product_id = p.id');
            $this->db->where('od.order_no',$ord_id);
            $this->db->where('status',1);
            $order_data=$this->db->get()->result_array();
        }
        return $order_data;
    }

    public function get_order($ord_id= '')
    {
        $order_data = array();
        if($ord_id!='')
        {
            $this->db->select('od.*,o.*');
            $this->db->from('orders as o');
            $this->db->join('order_details as od','od.order_no = o.id');
            $this->db->where('o.id',$ord_id);
            $this->db->where('status',1);
            $order_data=$this->db->get()->row_array();
        }
        return $order_data;
    }
    

    public function update_order($ord_id='',$data=array()) {
        if($ord_id!='' && !empty($data)) {
            $this->db->where('id',$ord_id);
            $this->db->update('orders',$data);
            return true;
        } else return false;
    }
     
}
