<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/dc.php";
class dcModule extends BizBaseModule
{
    public function __construct()
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
        
        /* 获取参数 */
        
        /* 业务逻辑部分 */
        $conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
        

        // 需要连表操作 只查询支持门店的
     
        $conditions .= " and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";
        
        
        $sql_count = " select count(id) from " . DB_PREFIX . "supplier_location";
        $sql = " select id,name,preview,is_close from " . DB_PREFIX . "supplier_location";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count.$conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);


        $list = $GLOBALS['db']->getAll($sql.$conditions . " order by id desc limit " . $limit);
        
        //获取分类数量
        $menu_cate_count = $GLOBALS['db']->getAll("select count(*) as count,location_id from ".DB_PREFIX."dc_supplier_menu_cate where location_id in(" . implode(",", $account_info['location_ids']) . ") GROUP BY location_id");
        foreach ($menu_cate_count as $k=>$v){
            $f_menu_cate_count[$v['location_id']] = $v;
        }
        $menu_count = $GLOBALS['db']->getAll("select count(*) as count,location_id from ".DB_PREFIX."dc_menu where location_id in(" . implode(",", $account_info['location_ids']) . ") GROUP BY location_id");
        
        foreach ($menu_count as $k=>$v){
            $f_menu_count[$v['location_id']] = $v;
        }

        foreach ($list as $k=>$v){
            $list[$k]['menu_cate_count'] = $f_menu_cate_count[$v['id']]['count']?$f_menu_cate_count[$v['id']]['count']:0;
            $list[$k]['menu_count'] = $f_menu_count[$v['id']]['count']?$f_menu_count[$v['id']]['count']:0;
            $list[$k]['menu_cate_url'] = url("biz","dc#dc_menu_cate_index",array("id"=>$v['id']));
            $list[$k]['menu_url'] = url("biz","dc#dc_menu_index",array("id"=>$v['id']));
        }
        
        /* 数据 */
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "外卖预订管理");
        $GLOBALS['tmpl']->display("pages/dc/index.html");
	}
	
	/**
	 * 订餐设置
	 */
	public function dc_set()
	{
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	    
	    /* 业务逻辑部分 */
	    $conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
	    
	    
	    // 只查询支持门店的
	     
	    $conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";
		
		
	    $sql = " select * from " . DB_PREFIX . "supplier_location";
	    $data = $GLOBALS['db']->getRow($sql.$conditions);
	    if(empty($data)){
	        showBizErr("数据不存在/没有管理权限！",0,url("biz","dc#index"));
	    }
	    
	    //获取餐厅分类
	    $dc_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate where type=0 and is_effect=1");
	    $dc_cate_cur = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate_supplier_location_link where location_id = ".$data['id']);
	    foreach ($dc_cate_cur as $k=>$v){
	        $f_dc_cate_cur[] = $v['dc_cate_id'];
	    }
	    foreach ($dc_cate as $k=>$v){
	        if(in_array($v['id'], $f_dc_cate_cur)){
	            $dc_cate[$k]['is_checked'] = 1;
	        }
	    }
	    
	    //获取时间数据
	    $open_time = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$data['id']);

        $open_time = array_sort($open_time,"begin_time_h");
	    foreach ($open_time as $k=>$v){
	        $v['end_time_m'] = str_pad($v['end_time_m'],2,0,STR_PAD_LEFT);
	        $temp_time['begin_time'] = $v['begin_time_h'].":".$v['begin_time_m'];
	        $temp_time['end_time'] = $v['end_time_h'].":".$v['end_time_m'];
	        $open_time_list[] = $temp_time;
	    }
	    
	    //获取配送地址数据
	    $delivery_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_delivery where location_id=".$data['id']." order by id asc");
	
	    //获取打包费数据
	    $package_conf =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$data['id']);
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("form_url", url("biz", "dc#do_save_dcset"));
	    
	    $GLOBALS['tmpl']->assign("dc_cate", $dc_cate);
	    $GLOBALS['tmpl']->assign("open_time_list", $open_time_list);
	    $GLOBALS['tmpl']->assign("delivery_data", $delivery_data);
	    $GLOBALS['tmpl']->assign("package_conf", $package_conf);
	    $GLOBALS['tmpl']->assign("vo", $data);
	    
    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "餐厅设置");
	    $GLOBALS['tmpl']->display("pages/dc/dc_set.html");
	}
	
	/**
	 * 保存配置
	 */
	public function do_save_dcset(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    /* 获取参数 */
	    $datasl['is_reserve'] = intval($_REQUEST['is_reserve']);
	    $datasl['max_delivery_scale']=max($_REQUEST['scale']);
	    $datasl['dc_location_notice']=strim($_REQUEST['dc_location_notice']);
	    $dc_cate_ids = $_REQUEST['cate_id'];
	    //营业时间
	    $id = intval($_REQUEST['id']);
	    $op_begin_time = $_REQUEST['op_begin_time'];
	    $op_end_time = $_REQUEST['op_end_time'];
	    
	    //配送价格

	    $scale = $_REQUEST['scale'];
	    $start_price = $_REQUEST['start_price'];
	    $delivery_price = $_REQUEST['delivery_price'];
	    
	    //打包费用
	    $package_conf['package_price'] = floatval($_REQUEST['package_price']);
	    $package_conf['package_start_price'] = floatval($_REQUEST['package_start_price']);
	    
	    
	    $conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";
	    
	    $sql = " select * from " . DB_PREFIX . "supplier_location";
	    $data = $GLOBALS['db']->getRow($sql.$conditions);
	    
	    if(empty($data)){
	        $data['status'] = 0;
	        $data['info'] = "数据不存在/没有管理权限！";
	        ajax_return($data);
	    }
	    

	    /* 业务逻辑部分 */
	    
	    //保存餐厅分类
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cate_supplier_location_link where location_id=".$id);
	    foreach($dc_cate_ids as $dc_cate)
	    {
	        $cate_data['dc_cate_id'] = $dc_cate;
	        $cate_data['location_id'] = $id;
	        $GLOBALS['db']->autoExecute(DB_PREFIX."dc_cate_supplier_location_link",$cate_data);
	    }
	    syn_supplier_location_dc_cate_match($id);
	    
	    //清除营业时间
	    //$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$id);
	    foreach ($op_begin_time as $k=>$v){
	        if($v){
	            $temp_op_begin_time = explode(":",$v);
	            $temp_op_end_time = explode(":",$op_end_time[$k]);
	            $temp_time['begin_time_h'][] = trim($temp_op_begin_time[0]);
	            $temp_time['begin_time_m'][] = trim($temp_op_begin_time[1]);
	            $temp_time['end_time_h'][] = trim($temp_op_end_time[0]);
	            $temp_time['end_time_m'][] = trim($temp_op_end_time[1]);

	            //保存时间数组($table, $field_values, $mode = 'INSERT'
// 	            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_location_open_time",$temp_time);
	        }
	    }
	    syn_supplier_location_open_time_match($temp_time,$id);
	    //清除配送配置
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_delivery where location_id=".$id);
	    foreach ($scale as $k=>$v){
	        if($v){

	            $temp_delivery['scale'] = floatval($scale[$k]);
	            $temp_delivery['start_price'] = floatval($start_price[$k]);
	            $temp_delivery['delivery_price'] = floatval($delivery_price[$k]);
	            $temp_delivery['location_id'] = $id;
	            //保存配送配置
	            $GLOBALS['db']->autoExecute(DB_PREFIX."dc_delivery",$temp_delivery);
	        }
	    }
	    
	    //保存打包费用
	    $package_conf['location_id'] = $id;
	    
	    $package_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_package_conf where location_id =".$package_conf['location_id']);
	    if($package_id)
	    {
	    	$GLOBALS['db']->autoExecute(DB_PREFIX."dc_package_conf",$package_conf,"UPDATE","location_id=".$package_conf['location_id']);
	    }
	    else
	    {
	   		 	
	    	 $GLOBALS['db']->autoExecute(DB_PREFIX."dc_package_conf",$package_conf);
	    }
	   
	    
	    //更新坐标
	    $menu['xpoint']=$data['xpoint'];
	    $menu['ypoint']=$data['ypoint'];
	    $menu['location_id']=$id;
	    sys_location_menu_xypoint($menu);
	    
	    //更新主表
	    $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$datasl,"UPDATE","id=".$id);
	    
	    /* 数据 */
	    $data['status'] = 1;
	    $data['jump'] = url("biz","dc#index");
	    $data['info'] = "修改成功";
	    ajax_return($data);
	}
	
	
	public function set_is_close(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	    
	    /* 业务逻辑部分 */
	    $conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";
	     
	    $sql = " select * from " . DB_PREFIX . "supplier_location";

	    $data = $GLOBALS['db']->getRow($sql.$conditions);
	     
	    if(empty($data)){
	        $data['status'] = 0;
	        $data['info'] = "数据不存在/没有管理权限！";
	        ajax_return($data);
	    }
	    
	    $s_value = $data['is_close']>0?0:1;
	    
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",array("is_close"=>$s_value),"UPDATE","id=".$id)){
	        $data['status'] = 1;
	        $data['info']="操作成功";
	        $data['is_close'] = $s_value;
	    }else{
	        $data['status']=0;
	        $data['info']="操作失败";
	    }
	    ajax_return($data);
	    
	}
	
	/*=========================菜单分类部分==================================*/
	
	/**
	 * 菜单分类
	 */
	public function dc_menu_cate_index(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
        
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    
	     /* 业务逻辑部分 */
	    $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and location_id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";
	    
	    $sql_count = " select count(id) from " . DB_PREFIX . "dc_supplier_menu_cate";
	    $sql = " select id,name,is_effect,sort from " . DB_PREFIX . "dc_supplier_menu_cate ";

	    /* 分页 */
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if ($page == 0)
	        $page = 1;
	    $limit = (($page - 1) * $page_size) . "," . $page_size;
	    
	    $total = $GLOBALS['db']->getOne($sql_count.$conditions);
	    $page = new Page($total, $page_size); // 初始化分页对象
	    $p = $page->show();
	    $GLOBALS['tmpl']->assign('pages', $p);
	    
	    
	    $list = $GLOBALS['db']->getAll($sql.$conditions . " order by sort desc limit " . $limit);
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_id", $id);
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "菜单分类管理");
	    $GLOBALS['tmpl']->display("pages/dc/menu_cate_index.html");
	    
	}
	
	/**
	 * 菜单分类添加
	 */
	public function load_add_menu_cate_weebox(){
	    $location_id = intval($_REQUEST['location_id']);
	    $GLOBALS['tmpl']->assign("location_id",$location_id);
	    $data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_menu_cate_weebox.html");
        ajax_return($data);
	}
	
	public function do_save_menu_cate(){
	    /*初始化*/
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*活出参数*/
	    $location_id = $_REQUEST['location_id'];
	    $name = strim($_REQUEST['cate_name']);
	    
	    /*业务逻辑部分*/
	    $root['status'] = 0;
	    $root['info'] = "";
	    if(!in_array($location_id, $account_info['location_ids'])){
	        $root['status'] = 0;
	        $root['info'] = "您没有添加权限";
	        ajax_return($root);
	    }
	    if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_menu_cate where name='".$name."' and location_id = ".$location_id)){
	        $root['status'] = 0;
	        $root['info'] = "分类名称重复";
	        ajax_return($root);
	    }
	    
	    
	    $data = array();
	    $data['name'] = $name;
	    $data['sort'] = 100;
	    $data['is_effect'] = 1;
	    $data['supplier_id'] = $supplier_id;
	    $data['location_id'] = $location_id;
	    
	    if ($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",$data)){
	        $root['status']=1;
	        $root['jump']= url("biz","dc#dc_menu_cate_index",array('id'=>$location_id));
	    }
	    ajax_return($root);
	    
	}
	

	
	/**
	 * 菜单分类删除
	 */
	public function dc_menu_cate_del(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	      
	    /* 业务逻辑部分 */
	    $root['status'] = 0;
	    $root['info'] = "";
	    
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    
	    //判断存在关联菜单
	    if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_menu where cate_id=".$id)){
	        $root['status'] =0;
	        $root['info'] = "有关联菜单存在无法删除";
	        ajax_return($root);
	    }
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id);
	    /* 数据 */
	    $root['status'] =1;
	    $root['jump'] = url("biz","dc#dc_menu_cate_index",array('id'=>$data['location_id']));
	    
	      
	    /* ajax返回数据 */
	    ajax_return($root);

	}
	
	/**
	 * 菜单状态修改
	 */
	public function dc_menu_cate_status(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	     
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	     
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	     
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }

	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['is_effect'] = $is_effect;
	        $root['info'] = "修改成功";
	    }
	     
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	
	/*
	 * 修改排序
	 */
	public function do_menu_cate_sort(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    $sort = intval($_REQUEST['sort']);
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	
	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("sort"=>$sort),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['info'] = "修改成功";
	    }
	
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	
	public function do_edit_menu_cate_name(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    $name = strim($_REQUEST['name']);
	    
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	    
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_menu_cate where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_menu_cate",array("name"=>$name),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['info'] = "修改成功";
	    }
	    
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	

	/*=========================菜单部分==================================*/
	public function dc_menu_index(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	     
	    /* 业务逻辑部分 */
	    $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and location_id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";
	     
	    $sql_count = " select count(id) from " . DB_PREFIX . "dc_menu";
	    $sql = " select id,name,is_effect,cate_id,price,image from " . DB_PREFIX . "dc_menu ";
	    
	    /* 分页 */
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if ($page == 0)
	        $page = 1;
	    $limit = (($page - 1) * $page_size) . "," . $page_size;
	     
	    $total = $GLOBALS['db']->getOne($sql_count.$conditions);
	    $page = new Page($total, $page_size); // 初始化分页对象
	    $p = $page->show();
	    $GLOBALS['tmpl']->assign('pages', $p);
	     
	     
	    $list = $GLOBALS['db']->getAll($sql.$conditions . " limit " . $limit);

	    //获取菜单分类
	    $menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");
	    foreach ($menu_cate_list as $k=>$v){
	        $f_menu_cate_list[$v['id']] =  $v['name'];
	    }
	    
	    foreach ($list as $k=>$v){
	        $list[$k]['cate_name'] = $f_menu_cate_list[$v['cate_id']]?$f_menu_cate_list[$v['cate_id']]:"暂无";
	    }
	   
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_id", $id);
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
	     
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "菜单管理");
	    $GLOBALS['tmpl']->display("pages/dc/menu_index.html");
	}
	
	public function load_add_menu_weebox(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $location_id = intval($_REQUEST['location_id']);
	    
	    /* 业务逻辑部分 */
	    
	    
	    //获取菜单分类
	    $menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");

	    //获取标签数据
	    $tags = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_menu_cate where type=1 and is_effect=1");
	    
	    $GLOBALS['tmpl']->assign("menu_cate",$menu_cate_list);
	    $GLOBALS['tmpl']->assign("location_id",$location_id);
	    $GLOBALS['tmpl']->assign("tags",$tags);
	    $data['html'] = $GLOBALS['tmpl']->fetch("pages/dc/add_menu_weebox.html");
	    ajax_return($data);
	}
	
	public function do_save_menu(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);

	    $location_id = intval($_REQUEST['location_id']);
	    $data['name'] = strim($_REQUEST['menu_name']);
	    $data['cate_id'] = intval($_REQUEST['cate_id']);
	    $data['image'] =  replace_domain_to_public(strim($_REQUEST['image']));
	    $data['price'] = floatval($_REQUEST['price']);
	    $data['tags'] = implode(",", $_REQUEST['tags']);
	    $data['is_effect'] = intval($_REQUEST['is_effect']);
	    
	    
	    /* 业务逻辑部分 */
	    if (!in_array($location_id, $account_info['location_ids'])){
	       $root['status'] = 0;
	       $root['info'] = "没有权限添加/修改该门店的菜单";
	    }

	   $location_info = $GLOBALS['db']->getRow("select xpoint,ypoint from ".DB_PREFIX."supplier_location where id=".$location_id);
	   $data['location_id'] = $location_id;
	   $data['supplier_id'] = $supplier_id;
	   $data['xpoint'] = $location_info['xpoint'];
	   $data['ypoint'] = $location_info['ypoint'];
	   
	   /*获取标签中文,同步函数*/
	   
	   if($id>0){
	       $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data,"UPDATE","id=".$id);
	       syn_supplier_location_menu_match($id);
	       $root['info'] = "修改成功";
	   }else{
	       $GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$data);
	       $id = $GLOBALS['db']->insert_id();
	       syn_supplier_location_menu_match($id);
	       $root['info'] = "添加成功";
	   }
	   $root['status'] = 1;
	  
	   $root['jump'] = url("biz","dc#dc_menu_index",array("id"=>$location_id));
	   ajax_return($root);
	    
	}
	
    public function load_edit_menu_weebox(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    /* 业务逻辑部分 */
 
	    $vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($vo)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    $location_id = $vo['location_id'];
	    
	    //获取菜单分类
	    $menu_cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id." and supplier_id = ".$supplier_id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ");

	    //获取标签数据
	    $tags = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."dc_menu_cate where type=1 and is_effect=1");
	    
	    $cur_tags = explode(",", $vo['tags']);
	    
	    foreach ($tags as $k=>$v){
	        if(in_array($v['id'], $cur_tags)){
	            $tags[$k]['is_checked'] =1;
	        }
	    }

	 
	    $GLOBALS['tmpl']->assign("vo",$vo);
	    $GLOBALS['tmpl']->assign("menu_cate",$menu_cate_list);
	    $GLOBALS['tmpl']->assign("id",$id);
	    $GLOBALS['tmpl']->assign("location_id",$location_id);
	    $GLOBALS['tmpl']->assign("tags",$tags);
	    $root['status'] =1;
	    $root['html'] = $GLOBALS['tmpl']->fetch("pages/dc/edit_menu_weebox.html");
	    
	    ajax_return($root);
	}
	
	public function dc_menu_status(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	    
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    
	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['is_effect'] = $is_effect;
	        $root['info'] = "修改成功";
	    }
	    
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	
	public function dc_menu_del(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	     
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	     
	    /* 业务逻辑部分 */
	    $root['status'] = 0;
	    $root['info'] = "";
	     
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	     
	    //查询是否有关联菜单
	     
	     
	     
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_menu where id=".$id);
	    /* 数据 */
	    $root['status'] =1;
	    $root['jump'] = url("biz","dc#dc_menu_index",array('id'=>$data['location_id']));
	     
	     
	    /* ajax返回数据 */
	    ajax_return($root);
	}
	
	
	public function batch_del_menu(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	   
	    /*获取参数*/
	    $location_id = intval($_REQUEST['location_id']);
	    $ids = $_REQUEST['del_ids'];
	    if(empty($ids)){
	        $root['status'] = 0;
	        $root['info'] = '至少选中一条数据';
	        ajax_return($root);
	    }
	    
	    
	    /*业务逻辑*/
	    
	    if(!in_array($location_id, $account_info['location_ids'])){
	        $root['status'] = 0;
	        $root['info'] = '没有管理权限';
	        ajax_return($root);
	    }

	    foreach ($ids as $k=>$v){
	        $temp_ids[] = intval($v);
	    }
	    $id_str = implode(",", $temp_ids);

	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_menu where id in(".$id_str.") and location_id=".$location_id);
	    $root['status'] = 1;
	    $root['info'] = "删除成功";
	    $root['jump'] = url("biz","dc#dc_menu_index",array("id"=>$location_id));
	    ajax_return($root);
	}
	
	/*=========================餐桌设置部分==================================*/
	
	/**
	 * 餐桌列表
	 */
	public function dc_rsitem_index(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	     
	    /* 业务逻辑部分 */
	    $conditions .= " where supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and location_id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") ";
	     
	    $sql_count = " select count(id) from " . DB_PREFIX . "dc_rs_item";
	    $sql = " select * from " . DB_PREFIX . "dc_rs_item ";
	    
	    /* 分页 */
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if ($page == 0)
	        $page = 1;
	    $limit = (($page - 1) * $page_size) . "," . $page_size;
	     
	    $total = $GLOBALS['db']->getOne($sql_count.$conditions);
	    $page = new Page($total, $page_size); // 初始化分页对象
	    $p = $page->show();
	    $GLOBALS['tmpl']->assign('pages', $p);
	     
	     
	    $list = $GLOBALS['db']->getAll($sql.$conditions . "order by sort desc limit " . $limit);

	  
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_id", $id);
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
	     
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "预约项目设置");
	    $GLOBALS['tmpl']->display("pages/dc/rsitem_index.html");
	    
	    
	}
	
	public function do_rsitem_sort(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    $sort = intval($_REQUEST['sort']);
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	    
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    
	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("sort"=>$sort),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['info'] = "修改成功";
	    }
	    
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	
	
	
		public function do_rsitem_price(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    $price = intval($_REQUEST['price']);
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
		
		 if($price==0){
	        $root['status'] =0;
	        $root['info'] = "定金不能为0";
	        ajax_return($root);
	    }
		
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("price"=>$price),"UPDATE"," id=".$id)){

	        $root['status'] = 1;
	        $root['info'] = "修改成功";
	    }
	    
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	
	
	
	public function do_rsitem_status(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    
	    /*业务逻辑*/
	    $root['status'] = 0;
	    $root['info'] = "";
	    
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(" . implode(",", $account_info['location_ids']) . ") and supplier_id =".$supplier_id);
	    //判断是否有权限和数据存在
	    if(empty($data)){
	        $root['status'] =0;
	        $root['info'] = "数据不存在/没有修改权限";
	        ajax_return($root);
	    }
	    
	    $is_effect = $data['is_effect']>0?0:1;
	    if($GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",array("is_effect"=>$is_effect),"UPDATE"," id=".$id)){
	        $root['status'] = 1;
	        $root['is_effect'] = $is_effect;
	        $root['info'] = "修改成功";
	    }
	    
	    /*ajax 数据返回*/
	    ajax_return($root);
	}
	public function dc_add_rsitem(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	     
	    /* 获取参数 */
	    $location_id = intval($_REQUEST['location_id']);
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_id", $location_id);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "添加项目");
	    $GLOBALS['tmpl']->display("pages/dc/dc_add_rsitem.html");
	}
	public function dc_edit_rsitem(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	    
	    /*业务逻辑*/
        $vo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id in(".implode(",",$account_info['location_ids'] ).")");
        if(empty($vo)){
            $root['status'] = 0;
            $root['info'] = "数据不存在/没有管理权限！";
            ajax_return($root);
        }
        
        /* 查询时间设置列表 */
        $rs_time_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_rs_item_time where item_id=".$id);
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_id", $vo['location_id']);
	    $GLOBALS['tmpl']->assign("id", $id);
	    $GLOBALS['tmpl']->assign("vo", $vo);
	    $GLOBALS['tmpl']->assign("rs_time_data", $rs_time_data);
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz","dc"));
	     
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "编辑餐桌");
	    $GLOBALS['tmpl']->display("pages/dc/dc_edit_rsitem.html");
	}
	
	public function do_save_rsitem(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	     
	    /* 获取参数 */
	    $location_id = intval($_REQUEST['location_id']);
	    $id = intval($_REQUEST['id']);
	    
	    /* 业务逻辑 */
	      
	    $data['name'] = strim($_REQUEST['name']);
	    $data['location_id'] = $location_id;
	    $data['supplier_id'] = $supplier_id;
	    $data['sort'] = intval($_REQUEST['sort']);
	    $data['is_effect'] = intval($_REQUEST['is_effect']);
	    $data['price'] = floatval($_REQUEST['price']);
	    
	     if($data['price']==0){
	    	
	    	$root['status'] = 0;
	        $root['info'] = "定金不能为0";
	        ajax_return($root);
	    }
	    
	    $conditions .= " where is_effect = 1 and supplier_id = ".$supplier_id; // 查询条件
	    // 只查询支持门店的
	    $conditions .= " and id=".$id." and is_dc=1 and id in(" . implode(",", $account_info['location_ids']) . ") ";
	     
	    $sql = " select * from " . DB_PREFIX . "supplier_location";
	    $location_data = $GLOBALS['db']->getRow($sql.$conditions);

	    
	    if(!empty($location_data)){
	        $root['status'] = 0;
	        $root['info'] = "数据不存在/没有管理权限！";
	        ajax_return($root);
	    }
	    
	    if($id>0){
	        $rsitem_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id." and location_id=".$location_id);
	        if(empty($rsitem_data)){
	            $root['status'] = 0;
	            $root['info'] = "参数错误！";
	            ajax_return($root);
	        }
	        $GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",$data,"UPDATE"," id=".$id);
	        $root['status'] = 1;
	        $root['info'] = "修改成功";
	    }else{
	        $GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item",$data);
	        $id = $GLOBALS['db']->insert_id();
	        if ($id){
	            $root['status'] = 1;
	            $root['info'] = "添加成功";
	        }
	    }
	    
	    
	    
	    /*获取餐桌时间配置*/
	    $rs_time_arr = $_REQUEST['rs_time'];
	    $total_count_arr = $_REQUEST['total_count'];
	    $t_is_effect_arr = $_REQUEST['t_is_effect'];
	    $rs_time_id_arr = $_REQUEST['rs_time_id'];
	    foreach ($rs_time_arr as $k=>$v){
	        if($v){
	            $ins_data['item_id'] =$id;
	            $ins_data['rs_time'] = $v;
	            $ins_data['total_count'] =$total_count_arr[$k];
	            $ins_data['is_effect'] =$t_is_effect_arr[$k];
	            $ins_data['supplier_id'] = $supplier_id;
	            $ins_data['location_id'] = $location_id;
	            if($rs_time_id_arr[$k]>0){
	                $GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_time",$ins_data,"update","id=".$rs_time_id_arr[$k]);
	            }else{
	                $GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_time",$ins_data);
	            }
	        }
	    }
	    
	   
	    /* 数据 */
	    $root['jump'] = url("biz","dc#dc_rsitem_index",array('id'=>$location_id));
	    ajax_return($root);
	}
	public function do_del_rsitem(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	     
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	    
	    /* 业务逻辑 */
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$id);
	    if(empty($data)){
	        $root['status'] = 0;
	        $root['info'] ="数据不存在/没有管理权限";
	        ajax_return($root);
	    }
	    
	    if(!in_array($data['location_id'], $account_info['location_ids'])){
	        $root['status'] = 0;
	        $root['info'] ="没有管理权限";
	        ajax_return($root);
	    }
	    /*删除时间配置*/
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time where item_id=".$id);
	    //删除餐桌
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item where id=".$id);
	    $root['status'] = 1;
	    $root['jump'] = url("biz","dc#dc_rsitem_index",array("id"=>$data['location_id']));
	    $root['info'] ="删除成功";
	    ajax_return($root);
	}
	
	/* =============================时间配置部分 =================================*/
	
	/**
	 * 删除餐桌时间配置
	 */
	public function do_del_time_item(){
	    /* 基本参数初始化 */
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /* 获取参数 */
	    $id = intval($_REQUEST['id']);
	     
	    /* 业务逻辑 */
	    $data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_time where id=".$id);
	    if(empty($data)){
	        $root['status'] = 0;
	        $root['info'] ="数据不存在/没有管理权限";
	        ajax_return($root);
	    }

	    if(!in_array($data['location_id'], $account_info['location_ids'])){
	        $root['status'] = 0;
	        $root['info'] ="没有管理权限";
	        ajax_return($root);
	    }
	    $GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time where id=".$id);
	    $root['status'] = 1;
	    $root['info'] ="删除成功";
	    ajax_return($root);
	}
	
}
?>