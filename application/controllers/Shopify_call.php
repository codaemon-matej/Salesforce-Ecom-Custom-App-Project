<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @Shopify Controller for its Integration
 * Rohit=>Making Shopify Integrations
 */

class Shopify_call extends CI_Controller {
    
    private $curr_datetime;

    function __construct(){
        parent::__construct();
        //check_admin_login();
        $this->curr_datetime=str_replace('@','T',date('Y-m-d@H:i:s-00:00'));
        $this->load->model("shopify_model");
        $this->load->model("useropt_model");
        $this->load->library("Shopifyclient");
    }

    //Rohit=> Add the Product's category to Shopify Collection via API
    public function add_collection($collect_id='') {
        $shopify_id='';
        if($collect_id!='') {
            $tmp_data=$this->shopify_model->get_collection($collect_id);
            if(!empty($tmp_data)) {
                $data=array();
                if(isset($tmp_data['name']) && $tmp_data['name']!='') $data['title']=$tmp_data['name'];

                if(!empty($data)) {
                    if(isset($tmp_data['status']) && $tmp_data['status']!='1') $data['published']=false;
                    else {
                        $data['published']=true;
                        $data['published_at']=$this->curr_datetime;
                    }
                    $shopify_collect_id=isset($tmp_data['shopify_collect_id']) ? $tmp_data['shopify_collect_id'] : '';
                    $shopify_api_flag=isset($tmp_data['shopify_api_flag']) ? $tmp_data['shopify_api_flag'] : '';
                    if($shopify_collect_id!='') {
                        $duplicate_flag=true;
                        $data['id']=$shopify_collect_id;
                        $data['updated_at']=$this->curr_datetime;
                        $function='custom_collections/'.$shopify_collect_id;
                        $method='PUT';
                    } else {
                        $duplicate_flag=false;
                        $data['created_at']=$this->curr_datetime;
                        $function='custom_collections';
                        $method='POST';
                    }
                    $collect_data['custom_collection']=$data;
                    $res=$this->shopifyclient->call_shopify_api($function,$method,$collect_data);
                    $result=json_decode($res);
                    $shopify_id=(isset($result->custom_collection->id) && $result->custom_collection->id!='') ? $result->custom_collection->id : '';
                    $upd_data=array();
                    if($shopify_id!='') {
                        if($duplicate_flag==false) $upd_data['shopify_collect_id']=$shopify_id;
                        $upd_data['shopify_api_flag']=1;
                    } else {
                        $upd_data['shopify_api_flag']=2;
                    }
                    $this->shopify_model->update_collection($collect_id,$upd_data);
                }
            }
        }
        return $shopify_id;
    }

    //Rohit=> Add the Product to Shopify Products via API
    public function add_product($prod_id= '') {
        $shopify_id='';
        if($prod_id!='') {
            $tmp_data=$this->shopify_model->get_product($prod_id);
            if(!empty($tmp_data)) {
                $data=array();
                if(isset($tmp_data['name']) && $tmp_data['name']!='') $data['title']=$tmp_data['name'];
                if(isset($tmp_data['description']) && $tmp_data['description']!='') $data['body_html']=$tmp_data['description'];
                if(isset($tmp_data['username']) && $tmp_data['username']!='') $data['vendor']=$tmp_data['username'];
                if(isset($tmp_data['image']) && $tmp_data['image']!='') {
                    $img_src=base_url().$tmp_data['image'];
                    $data['images']=array(array('src'=>$img_src));
                }
                if(isset($tmp_data['price']) && $tmp_data['price']!='') {
                    $data['variants']=array(array('price'=>$tmp_data['price']));
                }

                if(!empty($data)) {
                    $data['published_scope']='web';
                    if(isset($tmp_data['status']) && $tmp_data['status']!='1') $data['published']=false;
                    else {
                        $data['published']=true;
                        $data['published_at']=$this->curr_datetime;
                    }
                    $shopify_collect_id=isset($tmp_data['shopify_collect_id']) ? $tmp_data['shopify_collect_id'] : '';
                    $shopify_cat_api_flag=isset($tmp_data['cat_api_flag']) ? $tmp_data['cat_api_flag'] : '';
                    $prod_cat_id=isset($tmp_data['category_id']) ? $tmp_data['category_id'] : '';
                    $prd_collect_link_id=isset($tmp_data['prod_collect_link_id']) ? $tmp_data['prod_collect_link_id'] : '';
                    if($prod_cat_id!='' && ($shopify_collect_id=='' || $shopify_cat_api_flag!='1')) {
                        $shopify_collect_id=$this->add_collection($prod_cat_id);
                    }
                    $shopify_prod_id=isset($tmp_data['shopify_prod_id']) ? $tmp_data['shopify_prod_id'] : '';
                    $shopify_api_flag=isset($tmp_data['shopify_api_flag']) ? $tmp_data['shopify_api_flag'] : '';
                    if($shopify_prod_id!='') {
                        $duplicate_flag=true;
                        $data['id']=$shopify_prod_id;
                        $data['updated_at']=$this->curr_datetime;
                        $function='products/'.$shopify_prod_id;
                        $method='PUT';
                    } else {
                        $duplicate_flag=false;
                        $data['created_at']=$this->curr_datetime;
                        $function='products';
                        $method='POST';
                    }
                    $prd_data['product']=$data;
                    $res=$this->shopifyclient->call_shopify_api($function,$method,$prd_data);
                    $result=json_decode($res);
                    $shopify_id=(isset($result->product->id) && $result->product->id!='') ? $result->product->id : '';
                    if(!empty($result)){
                        foreach($result as $row) {
                         foreach($row->variants as $k) {
                           $varient_id = isset($k->id)?$k->id:'';
                       }
                   }
               }else
               {
                     $varient_id = '';
               }
                   
                    $upd_data=array();
                    if($shopify_id!='') {
                        $upd_data['shopify_prod_id']=$shopify_id;
                        $prd_collect_link_id=$this->assign_product_to_collection($prd_collect_link_id,$shopify_id,$shopify_collect_id);
                        if($prd_collect_link_id!='') {
                            $upd_data['prod_collect_link_id']=$prd_collect_link_id;
                            $upd_data['shopify_varient_id']=$varient_id;
                            $upd_data['shopify_api_flag']=1;
                        } else {
                            $upd_data['shopify_api_flag']=3;
                        }
                    } else {
                        $upd_data['shopify_api_flag']=2;
                    }
                    $this->shopify_model->update_product($prod_id,$upd_data);
                }
            }
        }
        return $shopify_id;
    }
    
    //Rohit=> Assigning the Product to Shopify Collection via API
    private function assign_product_to_collection($prod_collect_link_id='',$prod_id='',$collect_id='') {
        $shopify_id='';
        if($prod_id!='' && $collect_id!='') {
            $proceed=true;
            if($prod_collect_link_id!='') {
                $function='collects/'.$prod_collect_link_id;
                $method='GET';
                $res=$this->shopifyclient->call_shopify_api($function,$method,$prd_collect_data);
                $result=json_decode($res);
                $shopify_id=(isset($result->collect->id) && $result->collect->id!='') ? $result->collect->id : '';
                $spf_prod_id=(isset($result->collect->product_id) && $result->collect->product_id!='') ? $result->collect->product_id : '';
                $spf_collect_id=(isset($result->collect->collection_id) && $result->collect->collection_id!='') ? $result->collect->collection_id : '';
                if($prod_collect_link_id==$shopify_id && $prod_id==$spf_prod_id && $collect_id==$spf_collect_id) {
                    $proceed=false;
                } else {
                    $this->remove_product_from_collection($shopify_id);
                    if($prod_collect_link_id!=$shopify_id) $this->remove_product_from_collection($prod_collect_link_id);
                }
            }
            if($proceed) {
                $function='collects';
                $method='POST';
                $prd_collect_data['collect']=array('product_id'=>$prod_id,'collection_id'=>$collect_id);
                $res=$this->shopifyclient->call_shopify_api($function,$method,$prd_collect_data);
                $result=json_decode($res);
                $shopify_id=(isset($result->collect->id) && $result->collect->id!='') ? $result->collect->id : '';
        }
        return $shopify_id;
    }

    //Rohit=> Removing the Assigned Product form Shopify Collection via API
    private function remove_product_from_collection($prod_collect_link_id='') {
        $shopify_id='';
        if($prod_collect_link_id!='') {
            $function='collects/'.$prod_collect_link_id;
            $method='DELETE';
            $prd_collect_data=array();
            $res=$this->shopifyclient->call_shopify_api($function,$method,$prd_collect_data);
            $result=json_decode($res);
            return true;
        } else return false;
    }


    public function create_order($ord_id = '')
    {
     $shopify_id='';
     if($ord_id!='') {
        $tmp_data=$this->shopify_model->get_order($ord_id);
        $order_details = $this->shopify_model->get_order_details($ord_id);
        $counter = count($order_details);
        if(!empty($tmp_data)) {
	    $user_id=isset($tmp_data['ordered_by']) ? $tmp_data['ordered_by'] : 0;
	    $user_first_name=$user_last_name=$user_email='';
	    if($user_id>0) {
		$user_data=$this->shopify_model->get_user_details($user_id);
		$user_first_name=isset($user_data['f_name']) ? $user_data['f_name'] : '';
		$user_last_name=isset($user_data['l_name']) ? $user_data['l_name'] : '';
		$user_email=isset($user_data['email']) ? $user_data['email'] : '';
	    }
            $data=array();
            if(isset($tmp_data['grand_total']) && $tmp_data['grand_total']!='') $data['total_price']=$tmp_data['grand_total'];
            if(isset($tmp_data['id']) && $tmp_data['id']!='') $data['order_number']=$tmp_data['id'];

            $data['fulfillment_status']=isset($tmp_data['order_status']) ? $tmp_data['order_status'] : '';
            $data['send_receipt'] = true;
            $data['send_fulfillment_receipt'] = false;
            
            $keys = array_keys($order_details);
            for($i = 0; $i < count($order_details); $i++) {

                foreach($order_details[$keys[$i]] as $key => $details) {

                    $data['line_items'][$i] = 

                    array(
                        "title"         => isset($order_details[$keys[$i]]['name']) ? $order_details[$keys[$i]]['name'] : '',
                        "name"          => isset($order_details[$keys[$i]]['name']) ? $order_details[$keys[$i]]['name'] : '',
                        "variant_id"    => isset($order_details[$keys[$i]]['shopify_varient_id']) ? $order_details[$keys[$i]]['shopify_varient_id'] : '',
                        "quantity"      => isset($order_details[$keys[$i]]['quantity']) ? $order_details[$keys[$i]]['quantity'] : '',
                        "price"         => isset($order_details[$keys[$i]]['price']) ? $order_details[$keys[$i]]['price'] : '',


                    );
                }
                
            }
            $data['subtotal_price'] = $tmp_data['grand_total'];
            $data['total_discounts'] = '0.0';

            $data['customer']       = array(
                "first_name"    => $user_first_name,
                "last_name"     => $user_last_name,
                "email"         => $user_email
            );

            $data["billing_address"] = array(
                "first_name"    => isset($tmp_data['first_name'])?$tmp_data['first_name']:'',
                "last_name"     => isset($tmp_data['last_name'])?$tmp_data['last_name']:'',
                "address1"      => isset($tmp_data['mailingStreet'])?($tmp_data['mailingStreet']):'',
                "phone"         => isset($tmp_data['phone'])?($tmp_data['phone']):'',
                "city"          => isset($tmp_data['mailingCity'])?($tmp_data['mailingCity']):'',
                "province"      => isset($tmp_data['mailingState'])?($tmp_data['mailingState']):'',
                "country"       => 'United States',
                "zip"           => isset($tmp_data['mailingPostalCode'])?($tmp_data['mailingPostalCode']):''
            );

            $data["shipping_address"] = array(
                "first_name"    => isset($tmp_data['first_name'])?$tmp_data['first_name']:'',
                "last_name"     => isset($tmp_data['last_name'])?$tmp_data['last_name']:'',
                "address1"      => isset($tmp_data['mailingStreet'])?($tmp_data['mailingStreet']):'',
                "phone"         => isset($tmp_data['phone'])?($tmp_data['phone']):'',
                "city"          => isset($tmp_data['mailingCity'])?($tmp_data['mailingCity']):'',
                "province"      => isset($tmp_data['mailingState'])?($tmp_data['mailingState']):'',
                "country"       => 'United States',
                "zip"           => isset($tmp_data['mailingPostalCode'])?($tmp_data['mailingPostalCode']):'',
               // "country_code" => isset($tmp_data['mailingCountry'])?($tmp_data['mailingCountry']):'',
            );
            $data['note']   = 'You will receive your order in next 5 - 7 business days.';

            $shopify_order_id=isset($tmp_data['shopify_order_id']) ? $tmp_data['shopify_order_id'] : '';
            $shopify_api_flag=isset($tmp_data['shopify_api_flag']) ? $tmp_data['shopify_api_flag'] : '';

            if($shopify_order_id!='') {
                $duplicate_flag=true;
                $data['id']=$shopify_order_id;
                $data['updated_at']=$this->curr_datetime;
                $function='orders/'.$shopify_order_id;
                $method='PUT';
            } else {
                $duplicate_flag=false;
                $data['created_at']=$this->curr_datetime;
                $function='orders';
                $method='POST';
            }
            $ord_data['order']=$data;
            $res=$this->shopifyclient->call_shopify_api($function,$method,$ord_data);
            $result=json_decode($res);
            $shopify_id=(isset($result->order->id) && $result->order->id!='') ? $result->order->id : '';
            $upd_data=array();
            if($shopify_id!='') {
                $upd_data['shopify_order_id']=$shopify_id;
                $upd_data['shopify_api_flag']=1;
            } else {
                $upd_data['shopify_api_flag']=2;
            }
            $this->shopify_model->update_order($ord_id,$upd_data);
	}
      }
      return  $shopify_id;
    }

}
