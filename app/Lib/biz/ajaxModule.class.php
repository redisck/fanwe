<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class ajaxModule extends BizBaseModule{
	//检查商户帐号唯一
	public function check_field_unique(){
		$field_name = strim($_REQUEST['field_name']);
		$field_data = strim($_REQUEST['field_data']);
		$data = array();
		$data['error'] = 0;
		$data['msg'] = '';
		$account_name = strim($_REQUEST['account_name']);
		$result_data = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."supplier_submit WHERE ".$field_name."='".$field_data."'");
		if($result_data>0){ //已经存在数据
			$data['error'] = 1;
			$data['msg'] = "数据已经存在!";
		}
		ajax_return($data);
	}
	
	public function check_account_name(){
		$account_name = strim($_REQUEST['account_name']);
		$data = array();
		$data['error'] = 0;
		$data['msg'] = '';
		if($GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."supplier_submit WHERE account_name ='".$account_name."'")>0 || $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."supplier_account WHERE account_name ='".$account_name."'")>0 ){
			$data['error'] = 1;
			$data['msg'] = "数据已经存在!";
		}
		ajax_return($data);
	}
	
	

	
	public function check_account_mobile(){
	    $account_mobile = strim($_REQUEST['account_mobile']);
	    if(!check_mobile($account_mobile)){
	        $result['error'] = 1;
	        $result['msg'] = "手机号格式错误";
	        ajax_return($result);
	    }
	    if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account where mobile='".$account_mobile."'")){
	        $result['error'] = 1;
	        $result['msg'] = "手机号已经存在";
	        ajax_return($result);
	    }
	    $result['error'] = 0;
	    $result['msg'] = "";
	    ajax_return($result);
	}
	
	/**
	 * 发送手机验证码
	 */
	public function send_sms_code()
	{
		$verify_code = strim($_REQUEST['verify_code']);
		$mobile_phone = strim($_REQUEST['mobile']);
		if($mobile_phone=="")
		{
			$data['status'] = false;
			$data['info'] = "请输入手机号";
			$data['field'] = "user_mobile";
			ajax_return($data);
		}
		if(!check_mobile($mobile_phone))
		{
			$data['status'] = false;
			$data['info'] = "手机号格式不正确";
			$data['field'] = "user_mobile";
			ajax_return($data);
		}
	
	
		if(intval($_REQUEST['unique'])==1)
		{
			if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_submit where account_mobile = '".$mobile_phone."'"))>0)
			{
				$data['status'] = false;
				$data['info'] = "手机号已被注册";
				$data['field'] = "account_mobile";
				ajax_return($data);
			}
		}
	
	
		$sms_ipcount = load_sms_ipcount();
		if($sms_ipcount>1)
		{
			//需要图形验证码
			if(es_session::get("verify")!=md5($verify_code))
			{
				$data['status'] = false;
				$data['info'] = "验证码错误";
				$data['field'] = "verify_code";
				ajax_return($data);
			}
		}
	
		if(!check_ipop_limit(CLIENT_IP, "send_sms_code",SMS_TIMESPAN))
		{
			showErr("请勿频繁发送短信",1);
		}
	
	
	
		//删除失效验证码
		$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
		$GLOBALS['db']->query($sql);
	
		$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
		if($mobile_data)
		{
			//重新发送未失效的验证码
			$code = $mobile_data['code'];
			$mobile_data['add_time'] = NOW_TIME;
			$GLOBALS['db']->query("update ".DB_PREFIX."sms_mobile_verify set add_time = '".$mobile_data['add_time']."',send_count = send_count + 1 where mobile_phone = '".$mobile_phone."'");
		}
		else
		{
			$code = rand(100000,999999);
			$mobile_data['mobile_phone'] = $mobile_phone;
			$mobile_data['add_time'] = NOW_TIME;
			$mobile_data['code'] = $code;
			$mobile_data['ip'] = CLIENT_IP;
			$GLOBALS['db']->autoExecute(DB_PREFIX."sms_mobile_verify",$mobile_data,"INSERT","","SILENT");
				
		}
		send_verify_sms($mobile_phone,$code);
		es_session::delete("verify"); //删除图形验证码
		$data['status'] = true;
		$data['info'] = "发送成功";
		$data['lesstime'] = SMS_TIMESPAN -(NOW_TIME - $mobile_data['add_time']);  //剩余时间
		$data['sms_ipcount'] = load_sms_ipcount();
		ajax_return($data);
	
	
	}
	
	
    /**
     * 加载商品分类
     */
    public function load_goods_type(){
        global_run();
        $sql = "select * from ".DB_PREFIX."goods_type";
        if($GLOBALS['account_info']){//登录时候
            $sql.= " where supplier_id=0 or supplier_id=". $GLOBALS['account_info']['supplier_id'];
        }
        $data = $GLOBALS['db']->getAll($sql);
        $html = '<select class="ui-select filter_select medium" name="deal_goods_type" ><option value="0">==请选择类型==</option>';
        foreach ($data as $k=>$v){
            $html.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
        }
        $html .= "</select>";
        echo $html;
    }
    
    /**
     * 加载商品属性
     */
    public function load_attr_html(){
        global_run();
        
        $deal_goods_type = intval($_REQUEST['deal_goods_type']);
        $id = intval($_REQUEST['id']);
        $edit_type = intval($_REQUEST['edit_type']); //1管理员发布 2商户发布 
        
        $is_data = false;
        if($edit_type == 1 && $GLOBALS['db']->getOne("select deal_goods_type from ".DB_PREFIX."deal where id = ".$id)==$deal_goods_type){
            $is_data = true;
        }elseif($edit_type==2 && $GLOBALS['db']->getOne("select deal_goods_type from ".DB_PREFIX."deal_submit where id = ".$id)==$deal_goods_type){
            $is_data = true;
        }
        
        if($id>0 && $is_data)
        {
            $goods_type_attr = null;
            if ($edit_type == 1){
                
                $goods_type_attr = $GLOBALS['db']->getAll("select a.name as attr_name,a.is_checked as is_checked,a.price as price,a.add_balance_price,b.* from ".DB_PREFIX."deal_attr as a left join ".DB_PREFIX."goods_type_attr as b on a.goods_type_attr_id = b.id where a.deal_id=".$id." order by a.id asc");
            }else{
                //商品分类属性
                $goods_type_attr_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."goods_type_attr where goods_type_id = ".$deal_goods_type);
                foreach($goods_type_attr_data as $k=>$v){
                    $f_goods_type_attr[$v['id']] = $v;
                }
                //团购已经选择的分类属性值
                $deal_attr_data = unserialize($GLOBALS['db']->getOne("select cache_deal_attr from ".DB_PREFIX."deal_submit where id=".$id));
                
                
                
                foreach($deal_attr_data as $k=>$v){
                    $temp_data = array();
                    $temp_data['attr_name'] = $v['name'];
                    $temp_data['is_checked'] = $v['is_checked'];
                    $temp_data['price'] = $v['price'];
                    $temp_data['add_balance_price'] = $v['add_balance_price'];
                    $temp_data['id'] = $v['goods_type_attr_id'];
                    $temp_data['name'] = $f_goods_type_attr[$v['goods_type_attr_id']]['name'];
                    $temp_data['input_type'] = 0;
                    $temp_data['preset_value'] = '';
                    $temp_data['goods_type_id'] = $v['goods_type_attr_id'];
                    $temp_data['supplier_id'] = $GLOBALS['account_info']['supplier_id'];
                
                
                    $goods_type_attr[] = $temp_data;
                }
            }
           

            $goods_type_attr_id = 0;
            if($goods_type_attr)
            {
                foreach($goods_type_attr as $k=>$v)
                {
                    $goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
                    if($goods_type_attr_id!=$v['id'])
                    {
                        $goods_type_attr[$k]['is_first'] = 1;
                    }
                    else
                    {
                        $goods_type_attr[$k]['is_first'] = 0;
                    }
                    $goods_type_attr_id = $v['id'];
                }
            }
            else
            {
                $goods_type_attr = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."goods_type_attr where goods_type_id=".$deal_goods_type);
                foreach($goods_type_attr as $k=>$v)
                {
                    $goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
                    $goods_type_attr[$k]['is_first'] = 1;
                }
            }
        }
        else
        {
            $goods_type_attr =$GLOBALS['db']->getAll("select * from ".DB_PREFIX."goods_type_attr where goods_type_id=".$deal_goods_type);
            foreach($goods_type_attr as $k=>$v)
            {
                $goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
                $goods_type_attr[$k]['is_first'] = 1;
            }
        }
        
        //是否开启自动审核
        $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$GLOBALS['account_info']['supplier_id'])));
        
        $GLOBALS['tmpl']->assign("goods_type_attr",$goods_type_attr);
        echo $GLOBALS['tmpl']->fetch("pages/project/load_attr_html.html");
    }
    
    
public function load_delivery_form()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    	
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    	
    	if(!check_module_auth("goodso"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    	
    	$supplier_id = intval($s_account_info['supplier_id']);
    	require_once APP_ROOT_PATH."system/model/deal_order.php";
    	$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
    	
    	
    	$id = intval($_REQUEST['id']); //发货商品的ID    	
    	$item = $GLOBALS['db']->getRow("select doi.* from ".$order_item_table_name." as doi left join ".DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id where doi.id = ".$id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
    	if($item)
    	{

    		$location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where id in (".implode(",",$s_account_info['location_ids']).")");
    		$express_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."express where is_effect = 1");
    		$GLOBALS['tmpl']->assign("item",$item);
    		$GLOBALS['tmpl']->assign("express_list",$express_list);
    		$GLOBALS['tmpl']->assign("location_list",$location_list);
    		$data['html'] = $GLOBALS['tmpl']->fetch("inc/delivery_form.html");
    		$data['status'] = 1;
    		ajax_return($data);
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "非法的数据";
    		ajax_return($data);
    	}
    	
    }
    
    public function do_verify_delivery()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    	 
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    	 
    	if(!check_module_auth("goodso"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    	
    	
    	$id = intval($_REQUEST['id']);
    	$supplier_id = intval($s_account_info['supplier_id']);
    		
    	$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".DB_PREFIX."deal_location_link as l on l.deal_id = n.deal_id where n.order_item_id = ".$id." and n.is_arrival = 1 and  l.location_id in (".implode(",",$s_account_info['location_ids']).")  order by n.delivery_time desc");
		
		if($delivery_notice&&NOW_TIME-$delivery_notice['delivery_time']>24*3600*ORDER_DELIVERY_EXPIRE)
    	{
    		require_once APP_ROOT_PATH."system/model/deal_order.php";
    		$res = confirm_delivery($delivery_notice['notice_sn'],$id);
    		if($res)
    		{
    			$data['status'] = true;
    			$data['info'] = "超期收货成功";
    			ajax_return($data);
    		}
    		else
    		{
    			$data['status'] = 0;
    			$data['info'] = "收货失败";
    			ajax_return($data);
    		}
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "订单不符合超期收货的条件";
    		ajax_return($data);
    	}
    }
    
    public function load_filter_box(){
        global_run();
        $edit_type = intval($_REQUEST['edit_type']); //1管理员发布 2商户发布
        
        $shop_cate_id = intval($_REQUEST['shop_cate_id']);
        $id = intval($_REQUEST['id']);
        $ids = $this->get_parent_ids($shop_cate_id);

        $filter_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."filter_group where cate_id in (".implode(",", $ids).")");

		if($edit_type == 1){ //管理员添加数据
    		
    		foreach($filter_group as $k=>$v)
    		{
    		    
    			$filter_group[$k]['value'] = $GLOBALS['db']->getOne("select filter from ".DB_PREFIX."deal_filter where filter_group_id = ".$v['id']." and deal_id=".$id);
    		}
		
		}elseif ($edit_type == 2){//商户提交数据

		    $cache_deal_filter = $GLOBALS['db']->getOne("select cache_deal_filter from ".DB_PREFIX."deal_submit where id = ".$id);
		    $cache_deal_filter = unserialize($cache_deal_filter);
		    foreach($filter_group as $k=>$v)
		    {
		        $filter_group[$k]['value'] = $cache_deal_filter[$v['id']]['filter'];
		    }  
		}
        $GLOBALS['tmpl']->assign("filter_group",$filter_group);
        echo $GLOBALS['tmpl']->fetch("pages/project/filter_box.html");
    }
    
    //获取当前分类的所有父分类包含本分类的ID
    private $cate_ids = array();
    private function get_parent_ids($shop_cate_id)
    {
        $pid = $shop_cate_id;
        do{
            $pid = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."shop_cate where id=".$pid);
            if($pid>0)
                $this->cate_ids[] = $pid;
        }while($pid!=0);
    
        $this->cate_ids[] = $shop_cate_id;
    
        return $this->cate_ids;
    }
    
    
    
    public function do_refund_item()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    
    	if(!check_module_auth("goodso"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}    	 
    	 
    	$id = intval($_REQUEST['id']);
    	$supplier_id = intval($s_account_info['supplier_id']);
    	$allow_refund = $GLOBALS['db']->getOne("select allow_refund from ".DB_PREFIX."supplier where id = ".$supplier_id);
    	if(!$allow_refund)
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    	
    	require_once APP_ROOT_PATH."system/model/deal_order.php";
    	$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
    
    	$order_item = $GLOBALS['db']->getRow("select * from ".$order_item_table_name." where refund_status = 1 and id = ".$id." and supplier_id = ".$supplier_id);
    	if($order_item)
    	{
    		refund_item($order_item['id']);
    		$data['status'] = true;
    		$data['info'] = "退款操作成功";
    		ajax_return($data);
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "非法的数据";
    		ajax_return($data);
    	}
    	
    }
    
    
    public function do_refuse_item()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    
    	if(!check_module_auth("goodso"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    
    	$id = intval($_REQUEST['id']);
    	$supplier_id = intval($s_account_info['supplier_id']);
    	$allow_refund = $GLOBALS['db']->getOne("select allow_refund from ".DB_PREFIX."supplier where id = ".$supplier_id);
    	if(!$allow_refund)
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    	 
    	require_once APP_ROOT_PATH."system/model/deal_order.php";
    	$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
    
    	$order_item = $GLOBALS['db']->getRow("select * from ".$order_item_table_name." where refund_status = 1 and id = ".$id." and supplier_id = ".$supplier_id);
    	if($order_item)
    	{
    		refuse_item($order_item['id']);
    		$data['status'] = true;
    		$data['info'] = "拒绝退款操作成功";
    		ajax_return($data);
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "非法的数据";
    		ajax_return($data);
    	}
    	 
    }

    public function do_refund_coupon()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    
    	if(!check_module_auth("dealo"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    
    	$id = intval($_REQUEST['id']);
    	$supplier_id = intval($s_account_info['supplier_id']);
    	$allow_refund = $GLOBALS['db']->getOne("select allow_refund from ".DB_PREFIX."supplier where id = ".$supplier_id);
    	if(!$allow_refund)
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    	 
    	require_once APP_ROOT_PATH."system/model/deal_order.php";
    	$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
    
    	$order_item = $GLOBALS['db']->getRow("select * from ".$order_item_table_name." where refund_status = 1 and id = ".$id." and supplier_id = ".$supplier_id);
    	if($order_item)
    	{
    		$coupon_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_deal_id = ".$order_item['id']);
    		foreach($coupon_list as $c)
    		{
    			refund_coupon($c['id']);
    		}
    		$data['status'] = true;
    		$data['info'] = "退款操作成功";
    		ajax_return($data);
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "非法的数据";
    		ajax_return($data);
    	}
    	 
    }
    
    public function do_refuse_coupon()
    {
    	global_run();
    	$s_account_info = $GLOBALS['account_info'];
    
    	if(intval($s_account_info['id'])==0)
    	{
    		$data['status']=1000;
    		ajax_return($data);
    	}
    
    	if(!check_module_auth("dealo"))
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    
    	$id = intval($_REQUEST['id']);
    	$supplier_id = intval($s_account_info['supplier_id']);
    	$allow_refund = $GLOBALS['db']->getOne("select allow_refund from ".DB_PREFIX."supplier where id = ".$supplier_id);
    	if(!$allow_refund)
    	{
    		$data['status'] = 0;
    		$data['info'] = "权限不足";
    		ajax_return($data);
    	}
    
    	require_once APP_ROOT_PATH."system/model/deal_order.php";
    	$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
    
    	$order_item = $GLOBALS['db']->getRow("select * from ".$order_item_table_name." where refund_status = 1 and id = ".$id." and supplier_id = ".$supplier_id);
    	if($order_item)
    	{
    		$coupon_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_deal_id = ".$order_item['id']);
    		foreach($coupon_list as $c)
    		{
    			refuse_coupon($c['id']);
    		}
    		$data['status'] = true;
    		$data['info'] = "拒绝退款操作成功";
    		ajax_return($data);
    	}
    	else
    	{
    		$data['status'] = 0;
    		$data['info'] = "非法的数据";
    		ajax_return($data);
    	}
    
    }

    /**
     * 设置关联商品
     */
    public function add_related_deal()
    {

//    	$deal_id=intval($_REQUEST['deal_id']);
//    	$edit_type=intval($_REQUEST['edit_type']);
//        if($deal_id>0){
//        	$related_deal_id=$GLOBALS['db']->getOne("select relate_ids from ".DB_PREFIX."relate_goods where good_id = ".$deal_id);
//        	if($edit_type==2){
//        		$related_deal_id=$GLOBALS['db']->getOne("select cache_relate from ".DB_PREFIX."deal_submit where id = ".$deal_id);
//        		$related_deal_id= unserialize($related_deal_id);
//        		$related_deal_id= $related_deal_id['relate_ids'];
//        		
//        	}
//        	$related_deal=$GLOBALS['db']->getAll("select id,name,icon from ".DB_PREFIX."deal where id in(".$related_deal_id.")");
//        	$GLOBALS['tmpl']->assign("related_deal",$related_deal);
//        	$GLOBALS['tmpl']->assign("related_deal_id",$related_deal_id);
//        }
        
    	$data['html'] = $GLOBALS['tmpl']->fetch("pages/project/add_related_deal.html");
        ajax_return($data);
    }    

      /**
     * 加载可关联商品
     */
	public function load_relate()
	{
		global_run();
		$account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        
		require_once APP_ROOT_PATH . 'app/Lib/page.php';
		$deal_id = intval($_REQUEST['deal_id']);
		$is_shop = intval($_REQUEST['is_shop']);
		$related_deal = strim($_REQUEST['related_deal']);
		if($related_deal=='')$related_deal=0;
		$page = intval($_REQUEST['p']);
		$keyword = strim($_REQUEST['keyword']);
		$condition=' 1=1';
		if($keyword!='')$condition= " name like '%".$keyword."%' ";
		
		//print_r($_REQUEST);exit;
		$page = intval($_REQUEST['p']);
		$page_size = 8;

		if($page<=0)$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		//echo "select id,name,icon from ".DB_PREFIX."deal where id <> ".$deal_id." and ".$condition." and is_shop=".$is_shop." and id not in(".$related_deal.") and  supplier_id=".$supplier_id." and is_effect = 1 and is_delete=0  and buy_type <> 1 order by sort desc limit ".$limit;
		$list = $GLOBALS['db']->getAll("select id,name,icon from ".DB_PREFIX."deal where id <> ".$deal_id." and ".$condition." and is_shop=".$is_shop." and id not in(".$related_deal.") and  supplier_id=".$supplier_id." and is_effect = 1 and is_delete=0  and buy_type <> 1 order by sort desc limit ".$limit);
		$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal where id <> ".$deal_id." and ".$condition." and is_shop=".$is_shop." and id not in(".$related_deal.") and supplier_id=".$supplier_id." and is_effect = 1 and is_delete=0  and buy_type <> 1 ");
				
		$GLOBALS['tmpl']->assign("list",$list);
		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
    	$data['html'] = $GLOBALS['tmpl']->fetch("pages/project/load_relate.html");
        ajax_return($data);
	}
    
}