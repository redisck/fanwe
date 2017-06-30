<?php
/**
 * 商户中心团购管理
 * @author Administrator
 *
 */
require APP_ROOT_PATH . 'app/Lib/page.php';

class deliveryModule extends BizBaseModule
{

    function __construct()
    {
        parent::__construct();
        global_run();
        $this->check_auth();
    }

    public function index()
    {
         /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
 
        /*列出所有配送方式*/
        $sql="select * from ".DB_PREFIX."delivery where is_effect=1 ";
        $list = $GLOBALS['db']->getAll($sql);
        foreach($list as  $k => $v){
        	$list[$k]['edit_url']=url("biz", "delivery#edit", array("id"=>$v['id']));
        }
        $GLOBALS['tmpl']->assign("list",$list);
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "配送方式列表");
        $GLOBALS['tmpl']->display("pages/delivery/index.html");
    }

    public function edit()
    {
    	 /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    /* 数据 */
    	$id = intval($_REQUEST['id']);
    	$delivery_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "delivery where id=" . $id." and is_effect=1"); 
    	$weight_name=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."weight_unit where id=".$delivery_info['weight_id']);
    	$delivery_info['weight_name']=strim($weight_name);
    	//开始输出配送地区列表
    	$regions_list =$GLOBALS['db']->getAll("select * from " . DB_PREFIX . "delivery_fee where supplier_id=".$supplier_id." and  delivery_id=" . $id);
    	
    	foreach($regions_list as $k=>$v)
    	{
    		$names = '';
    		$regions = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "delivery_region where id in(".$v['region_ids'].")");
    		
    		foreach($regions as $kk=>$vv)
    		{
    			$names.=$vv['name'].",";
    		}
    		$names = substr($names,0,-1);
    		$regions_list[$k]['names'] = $names;
    		
    		$regions_list[$k]['first_fee'] = round($regions_list[$k]['first_fee'],2);
    		$regions_list[$k]['first_weight'] = floatval($regions_list[$k]['first_weight']);
    		$regions_list[$k]['continue_fee'] = round($regions_list[$k]['continue_fee'],2);
    		$regions_list[$k]['continue_weight'] = floatval($regions_list[$k]['continue_weight']);
    	}
    	$GLOBALS['tmpl']->assign("regions_list",$regions_list);
    	
    	
    	$GLOBALS['tmpl']->assign("ajax_url", url("biz","delivery#select_regions"));// 返回列表连接
    	
    	//格式化数字
    	$delivery_info['first_fee'] = round($delivery_info['first_fee'],2);
    	$delivery_info['first_weight'] = floatval($delivery_info['first_weight']);
    	$delivery_info['continue_fee'] = round($delivery_info['continue_fee'],2);
    	$delivery_info['continue_weight'] = floatval($delivery_info['continue_weight']);
    	$GLOBALS['tmpl']->assign("vo",$delivery_info);
    	$GLOBALS['tmpl']->assign("go_list_url", url("biz","delivery"));// 返回列表连接
    
    	/* 系统默认 */

    	$GLOBALS['tmpl']->assign("page_title", "配送方式编辑");
    	$GLOBALS['tmpl']->display("pages/delivery/edit.html");
    }
    //选取配送地区
    public function select_regions()
    {

    	$delivery_regions =$GLOBALS['db']->getAll("select * from " . DB_PREFIX . "delivery_region where region_level = 1");
    	
    	$region_conf_id = intval($_REQUEST['region_conf_id']);
    	$delivery_fee = $GLOBALS['db']->getOne("select region_ids from ". DB_PREFIX . "delivery_fee where id=".$region_conf_id);
    	$delivery_fee = explode(",",$delivery_fee);
    	$delivery_fee_id = $GLOBALS['db']->getOne("select id from ". DB_PREFIX . "delivery_fee where id=".$region_conf_id);
    	foreach($delivery_regions as $k=>$v)
    	{
    		$delivery_regions[$k]['delivery_regions'] = $GLOBALS['db']->getAll("select * from ". DB_PREFIX . "delivery_region where pid=".$v['id']);
    		foreach($delivery_regions[$k]['delivery_regions'] as $kk=>$vv){
    			if(in_array($vv['id'],$delivery_fee))
    				$delivery_regions[$k]['delivery_regions'][$kk]['ischecked']=1;
    			else 
    				$delivery_regions[$k]['delivery_regions'][$kk]['ischecked']=0;
    		}
    	}

    	$GLOBALS['tmpl']->assign("delivery_regions",$delivery_regions);
    	$GLOBALS['tmpl']->assign("delivery_fee",$delivery_fee);
    	$GLOBALS['tmpl']->assign("delivery_fee_id",$delivery_fee_id);
    	$GLOBALS['tmpl']->display("pages/delivery/select_regions.html");
    }
    public function getSubRegion()
    {
    	$id = intval($_REQUEST['id']);
    	$region_conf_id = intval($_REQUEST['delivery_fee_id']);
    	
    	$delivery_fee = $GLOBALS['db']->getOne("select region_ids from ". DB_PREFIX . "delivery_fee where id=".$region_conf_id);
    	
    	$delivery_fee = explode(",",$delivery_fee);
    	
    	$delivery_regions = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "delivery_region where pid=".$id);
 
    	foreach($delivery_regions as $kk=>$vv){
    		if(in_array($vv['id'],$delivery_fee))
    			$delivery_regions[$kk]['ischecked']=1;
    		else
    			$delivery_regions[$kk]['ischecked']=0;
    	}
    	
    	$GLOBALS['tmpl']->assign("delivery_regions",$delivery_regions);
    	$GLOBALS['tmpl']->assign("delivery_fee",$delivery_fee);
    	$GLOBALS['tmpl']->assign("delivery_fee_id",$region_conf_id);
    	$GLOBALS['tmpl']->display("pages/delivery/sub_region.html");
    
    }
    public function do_save_publish() {
    	$account_info = $GLOBALS['account_info'];
    	$supplier_id = $account_info['supplier_id'];
    	$account_id = $account_info['id'];

    	require_once APP_ROOT_PATH."system/utils/child.php";
    	$child = new child("delivery_region");
    	
    	$delivery_regions = $_REQUEST['region_support_region'];
    	if(count($delivery_regions)>5)
    	{
    		showErr("最多支持5条配置项");
    	}
    	
    	$data = array();
    	$delivery_id=intval($_REQUEST['id']);
    	
    	$GLOBALS['db']->query("delete from ". DB_PREFIX . "delivery_fee where delivery_id =".$delivery_id." and supplier_id=".$supplier_id);

    	foreach($delivery_regions as $k=>$v)
    	{
    		if($v!='')
    		{
    			$id_arr = explode(",",$v);
    			$sub_ids = array();
    			foreach($id_arr as $vv)
    			{
    				if(!in_array($vv,$sub_ids))
    				{
    					$tmp_ids = $child->getChildIds($vv);
    					$tmp_ids[] = $vv;
    					$sub_ids = array_merge($sub_ids,$tmp_ids);
    				}
    			}
    				
    			//添加相应的支持地区
    			
    			$data['name']=strim($_REQUEST['name']);
    			$data['description']=strim($_REQUEST['description']);
    			$data['weight_name']=strim($_REQUEST['weight_name']);
    			$data['supplier_id']=$supplier_id;
    			$data['delivery_id']=$delivery_id;
    			
    			$data['region_ids'] = implode(",",$sub_ids);
    			$data['first_weight'] = floatval($_REQUEST['region_first_weight'][$k]);
    			$data['first_fee'] = floatval($_REQUEST['region_first_fee'][$k]);
    			$data['continue_weight'] = floatval($_REQUEST['region_continue_weight'][$k]);
    			$data['continue_fee'] = floatval($_REQUEST['region_continue_fee'][$k]);
    			
    		
    			$list=$GLOBALS['db']->autoExecute(DB_PREFIX . "delivery_fee", $data);
    			if(!$list){
    				$result['status'] = 0;
    				$result['info'] = "设置失败";
    				$result['jump'] = url("biz", "delivery#edit", array("id" => $delivery_id));
    			}
    		}
    	}
    	$result['status'] = 1;
    	$result['info'] = "设置成功";
    	$result['jump'] = url("biz", "delivery#edit", array("id" => $delivery_id));
    	ajax_return($result);
	
    }

}



?>