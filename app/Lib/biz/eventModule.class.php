<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class eventModule extends BizBaseModule
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
        $conditions .= " where e.is_effect = 1 and e.supplier_id = ".$supplier_id; // 查询条件
        

        // 需要连表操作 只查询支持活动的
        $join = " left join ".DB_PREFIX."event_location_link ell on ell.event_id = e.id ";
        $conditions .= " and ell.location_id in(" . implode(",", $account_info['location_ids']) . ") ";
        
        
        $sql_count = " select count(distinct(e.id)) from " . DB_PREFIX . "event e";
        $sql = " select distinct(e.id),e.name,e.icon,e.cate_id,e.event_begin_time as begin_time,e.event_end_time as end_time from " . DB_PREFIX . "event e";
        
        

        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count.$join.$conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);


        $list = $GLOBALS['db']->getAll($sql.$join.$conditions . " order by id desc limit " . $limit);
        
        //分类数据集
        $cate_ids = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."event_cate where is_effect = 1");
        foreach($cate_ids as $k=>$v){
            $f_cate_id[$v['id']] = $v['name'];
        }
        foreach ($list as $k => $v) {
            $list[$k]['begin_time'] = $v['begin_time'] != 0 ? to_date($v['begin_time']) : "不限";
            $list[$k]['end_time'] = $v['end_time'] != 0 ? to_date($v['end_time']) : "不限";
            if($v['icon'])
                $list[$k]['images'][0] = array("img"=>$v['icon']);
            $list[$k]['edit_url'] = url("biz", "event#edit", array(
                "id" => $v['id'],
                "edit_type" =>1
            ));
            $list[$k]['preview_url'] = url("index","preview#event",array("id"=>$v['id'],"type"=>0));

        }

        /* 数据 */
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "event#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("biz", "event#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "event"));
	    $GLOBALS['tmpl']->assign("index_url", url("biz", "event#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "event#no_online_index"));

        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "活动列表管理");
        $GLOBALS['tmpl']->display("pages/project/index.html");
	}
	
	public function no_online_index()
	{
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	
	    /* 获取参数 */
	    if(isset($_REQUEST['filter_admin_check']) && $_REQUEST['filter_admin_check']!=''){
	        $filter_admin_check = intval($_REQUEST['filter_admin_check']);
	    }else{
	        $filter_admin_check = -1;
	    }

	    /* 业务逻辑部分 */
	    $conditions .= " where is_effect = 1 "; // 查询条件
	
	    if ($account_info['is_main'] == 1) { // 总管理员
	        $conditions .= " and supplier_id = " . $supplier_id;
	    } else { // 子账户操作
	        // 只查询支持活动的
	        $conditions .= " and account_id =" . $account_id;
	    }

	    if ($filter_admin_check >= 0) {
	        $conditions .= " and admin_check_status = " . $filter_admin_check;
	    }

	    $sql_count = " select count(*) from " . DB_PREFIX . "event_biz_submit ";
	    $sql = " select id,name,icon,biz_apply_status,admin_check_status from " . DB_PREFIX . "event_biz_submit ";
	
	    /* 分页 */
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if ($page == 0)
	        $page = 1;
	    $limit = (($page - 1) * $page_size) . "," . $page_size;
	
	    $total = $GLOBALS['db']->getOne($sql_count . $conditions);
	    $page = new Page($total, $page_size); // 初始化分页对象
	    $p = $page->show();
	    $GLOBALS['tmpl']->assign('pages', $p);

	    $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
	
	    foreach ($list as $k => $v) {
	        $list[$k]['edit_url'] = url("biz", "event#edit", array(
	            "id" => $v['id'],
	            "edit_type" =>2
	        ));
	        if($v['icon'])
	           $list[$k]['images'][] = $v['icon'];
	        $list[$k]['preview_url'] = url("index","preview#event",array("id"=>$v['id'],"type"=>1));
	    }

	    /* 数据 */
	    $GLOBALS['tmpl']->assign("filter_admin_check", $filter_admin_check);
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "event#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("biz", "event#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "event"));
	    $GLOBALS['tmpl']->assign("index_url", url("biz", "event#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "event#no_online_index"));
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "活动列表管理");
	    $GLOBALS['tmpl']->display("pages/project/index.html");
	}
	
	public function publish(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    
	    /* 业务逻辑 */
	    
	    // 支持活动
	    $location_infos = $GLOBALS['db']->getAll("select id,name,xpoint,ypoint from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
	    // 活动类型
	    $event_type_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "event_cate where is_effect = 1");
	  
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持活动
	    $GLOBALS['tmpl']->assign("event_type_list", $event_type_list); // 活动分类
	    //是否开启自动审核
	    $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "event"));
	    $GLOBALS['tmpl']->assign("page_title", "新增活动");
	    $GLOBALS['tmpl']->display("pages/project/event_publish.html");
	}
	
	
	public function do_save_publish(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    $edit_type = intval($_REQUEST['edit_type']);
	    $id = intval($_REQUEST['id']);
	   
	    //是否开启自动审核
	    $supplier_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$supplier_id);
	    if($supplier_data['allow_publish_verify']){
	        $allow_publish_verify = $supplier_data['allow_publish_verify'];
	        $publish_verify_balance = $supplier_data['publish_verify_balance'];
	        require_once APP_ROOT_PATH.'system/model/event.php';
	    }
	    
	    if($edit_type == 1 && $id>0){ //判断是否有存在修改
	        $event_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where event_id = ".$id." and supplier_id = ".$supplier_id);
	        if($event_submit_info && $event_submit_info['admin_check_status']==0){//存在未审核数据
	            $result['status'] = 0;
	            $result['info'] = "已经存在申请操作，请先删除避免重复申请";
	            ajax_return($result);
	            exit;
	        }else{
	            $event_info = $GLOBALS['db']->getRow("select e.* from " . DB_PREFIX . "event e
	                        left join ".DB_PREFIX."event_location_link ell on ell.event_id = e.id 
                            where is_effect = 1 and id=".$id." and supplier_id = ".$supplier_id."
                            and ell.location_id in(" . implode(",", $account_info['location_ids']).")");
	    
	            if(empty($event_info)){
	                $result['status'] = 0;
	                $result['info'] = "数据不存在或没有权限操作该数据";
	                ajax_return($result);
	                exit;
	            }
	            $new_data = $event_info;
	            $new_data['event_id'] = $event_info['id'];
	            unset($new_data['id']);
	            $new_data['supplier_id'] = $supplier_id;
	            $new_data['account_id'] = $account_id;
	    
	            //如果数据已经有存在，通过审核的数据，先清除掉在进行插入更新操作
	            if($event_submit_info && $event_submit_info['admin_check_status']!=0){//删除已审核 或 拒绝的数据
	                $GLOBALS['db']->query("delete from ".DB_PREFIX."event_biz_submit where id=".$event_submit_info['id']);
	            }
	    
	            //先建立数据
	            $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",$new_data);
	            $event_submit_id = $GLOBALS['db']->insert_id();
	        }
	        
	    }
	    // 白名单过滤
	    require_once APP_ROOT_PATH . 'system/model/no_xss.php';
	    //数据验证
	    $this->check_event_publish_data($_REQUEST);
	    
	    $data['supplier_id'] = $supplier_id; // 所属商户
	    $data['account_id'] = $account_id;
	    $data['name'] = strim($_REQUEST['name']); // 名称
	    //供应商标志图片
	    $icon = strim($_REQUEST['icon']); // 活动图片
	    if($id > 0){ //更新操作需要替换图片地址
	        $icon = replace_domain_to_public($icon);
	    }
	    $data['icon'] = $icon;
	    
	    $data['event_begin_time'] = strim($_REQUEST['event_begin_time']) == '' ? 0 : to_timespan($_REQUEST['event_begin_time'], "Y-m-d H:i");
	    $data['event_end_time'] = strim($_REQUEST['event_end_time']) == '' ? 0 : to_timespan($_REQUEST['event_end_time'], "Y-m-d H:i");
	    $data['submit_begin_time'] = strim($_REQUEST['submit_begin_time']) == '' ? 0 : to_timespan($_REQUEST['submit_begin_time'], "Y-m-d H:i"); // 报名开始时间:
	    $data['submit_end_time'] = strim($_REQUEST['submit_end_time']) == '' ? 0 : to_timespan($_REQUEST['submit_end_time'], "Y-m-d H:i"); // 报名结束时间
	    $data['total_count'] = intval($_REQUEST['total_count']);   //名额
	    $data['score_limit'] = intval($_REQUEST['score_limit']);   //消耗积分
	    $data['point_limit'] = intval($_REQUEST['point_limit']);   //经验限制	    
	    $data['city_id'] = intval($_REQUEST['city_id']); // 城市
	    $area_id = $_REQUEST['area_id']; // 地区列表
	    $data['cache_event_area_link'] = serialize($area_id);
	    $data['cate_id'] = intval($_REQUEST['cate_id']); // 分类
	    $location_id = $_REQUEST['location_id']; // 支持门店
	    $data['cache_event_location_link'] = serialize($location_id);
	    $data['address'] = strim($_REQUEST['address']); // 地址
	    
	    $data['api_address'] = strim($_REQUEST['api_address']); // 地图定位的地址
	    $data['xpoint'] = strim($_REQUEST['xpoint']); // 经度
	    $data['ypoint'] = strim($_REQUEST['ypoint']); // 纬度
	    $data['brief'] = strim($_REQUEST['brief']); // 部门简介
	    $data['content'] = btrim(no_xss($_REQUEST['content'])); //内容
	    
	    //字段配置
	    foreach($_REQUEST['field_id'] as $k=>$field_id)
	    {
	        $event_field = array();
	        $event_field['id'] = intval($field_id);
	        $event_field['field_show_name'] = $_REQUEST['field_show_name'][$k];
	        $event_field['field_type'] = $_REQUEST['field_type'][$k];
	        $event_field['value_scope'] = $_REQUEST['value_scope'][$k];
	        $event_field['sort'] = $k;
	        $cache_event_field[] = $event_field;
	    }
	    $data['cache_event_field'] = serialize($cache_event_field);
	    
	    /*默认参数*/
	    $data['is_effect'] = 1;
	    
	    // 管理员状态
	    $data['admin_check_status'] = 0; // 待审核

	    if ($id > 0) {

	        if($edit_type == 1){
	            $id = $event_submit_id; //上面生成的记录IDs
	            $data['biz_apply_status'] = 2; // 修改申请
	        }
	        $GLOBALS['db']->autoExecute(DB_PREFIX . "event_biz_submit", $data, "UPDATE", " id=" . $id . " and account_id=" . $account_id);
	        if($allow_publish_verify){
	            event_auto_publish($id);
	            $result['info'] = "修改成功";
	        }else{
	            $result['info'] = "修改成功，等待管理员审核";
	        }
	        $result['status'] = 1;
	        $result['jump'] = url("biz","event#no_online_index");
	    } else {

	        $data['biz_apply_status'] = 1; // 新增申请
    
	        $list = $GLOBALS['db']->autoExecute(DB_PREFIX . "event_biz_submit", $data);
	        $id = $GLOBALS['db']->insert_id();
	        if ($list) {
	            if($allow_publish_verify){
	                event_auto_publish($id);
	                $result['info'] = "提交成功";
	            }else{
	                $result['info'] = "提交成功，等待管理员审核";
	            }
	            $result['status'] = 1;
	            $result['jump'] = url("biz","event#no_online_index");
	        }
	    }
	    
	    ajax_return($result);
	}
	
	public function edit()
	{
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];

	    $id = intval($_REQUEST['id']);
	    $edit_type = intval($_REQUEST['edit_type']);
	
	    if($edit_type == 1 && $id>0){ //判断是否有存在修改
	        $event_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where event_id = ".$id." and supplier_id = ".$supplier_id);
	        if($event_submit_info && $event_submit_info['admin_check_status']==0){
	            showBizErr("已经存在申请操作，请先删除避免重复申请",0,url("biz","event#index"));
	            exit;
	        }
	    }

	    /* 业务逻辑 */
	
	    if ($edit_type == 1) {//管理员发布
	        /*********************************
	         * 取真正的活动数据表数据
	         ********************************/
	        $event_info = $GLOBALS['db']->getRow("select e.* from " . DB_PREFIX . "event e
	                        left join ".DB_PREFIX."event_location_link ell on ell.event_id = e.id
                            where is_effect = 1 and id=".$id." and supplier_id = ".$supplier_id."
                            and ell.location_id in(" . implode(",", $account_info['location_ids']).")");
	        
	        if (empty($event_info)) {
	            showBizErr("数据不存在或没有操作权限！",0,url("biz","event#index"));
	            exit();
	        }
	        
	        //支持活动 , 活动选中状态
            $location_infos = $GLOBALS['db']->getAll("select id,name,xpoint,ypoint from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
            $curr_location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_location_link where  event_id = ".$id);
            foreach($curr_location_list as $k=>$v){
                $curr_locations[] = $v['location_id'];
            }
            
            foreach ($location_infos as $k => $v) {
                if (in_array($v['id'], $curr_locations) ) {
                    $location_infos[$k]['checked'] = 1;
                }
            }
            
            $event_field = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$id." order by sort asc");
	        $go_list_url = url("biz","event#index");
	    } elseif($edit_type == 2) {//商户提交
	        /**********************************
	         * 取商户提交数据表
	         *********************************/
	        $event_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "event_biz_submit where id=" . $id." and supplier_id = ".$supplier_id." and account_id = ".$account_id);
	
	        if (empty($event_info)) {
	            showBizErr("数据不存在或没有操作权限！",0,url("biz","event#no_online_index"));
	            exit();
	        }
	        
	        // 支持活动 , 活动选中状态
	        $cache_location_id = unserialize($event_info['cache_event_location_link']);
	        $location_infos = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
	        
	        foreach ($location_infos as $k => $v) {
	            if (in_array($v['id'], $cache_location_id)) {
	                $location_infos[$k]['checked'] = 1;
	            }
	        }
	        
	        $event_field = unserialize($event_info['cache_event_field']);
	        
	        $go_list_url = url("biz","event#no_online_index");
	    }                                  

	   
	    // 时间格式化
	    $event_info['event_begin_time'] = $event_info['event_begin_time']>0?to_date($event_info['event_begin_time'], "Y-m-d H:i"):'';
	    $event_info['event_end_time'] = $event_info['event_end_time']>0?to_date($event_info['event_end_time'], "Y-m-d H:i"):'';
	    $event_info['submit_begin_time'] = $event_info['submit_begin_time']>0?to_date($event_info['submit_begin_time'], "Y-m-d H:i"):'';
	    $event_info['submit_end_time'] = $event_info['submit_end_time']>0?to_date($event_info['submit_end_time'], "Y-m-d H:i"):'';
	    $event_info['total_count'] = $event_info['total_count']>0?$event_info['total_count']:'';
	    $event_info['score_limit'] = $event_info['score_limit']>0?$event_info['score_limit']:'';
	    $event_info['point_limit'] = $event_info['total_count']>0?$event_info['point_limit']:'';

	    // 活动类型
	    $event_type_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "event_cate where is_effect = 1");
	     
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("event_field",$event_field);  //配置字段
	    $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持活动
	    $GLOBALS['tmpl']->assign("event_type_list", $event_type_list); // 活动类型
	    $GLOBALS['tmpl']->assign("vo", $event_info); // 活动所有数据
	    $GLOBALS['tmpl']->assign("edit_type", $edit_type); // 请求数据类型
	    $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
	    //是否开启自动审核
	    $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "event"));
	    $GLOBALS['tmpl']->assign("page_title", "活动项目编辑");
	    $GLOBALS['tmpl']->display("pages/project/event_edit.html");
	}
	
	public function del()
	{
	    /* 基本参数初始化 */
	    $id = intval($_REQUEST['id']);
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	
	    /* 业务逻辑 */
	    if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "event_biz_submit where id=" . $id . " and account_id =" . $account_id)) {
	        // 存在切用户有权限删除
	        $GLOBALS['db']->query("delete from " . DB_PREFIX . "event_biz_submit where id=" . $id . " and account_id =" . $account_id);
	        $data['status'] = 1;
	        $data['info'] = "删除成功";
	    } else {
	        $data['status'] = 0;
	        $data['info'] = "数据不存在货没有管理权限";
	    }
	    ajax_return($data);
	}
	/**
	 * 下架操作
	 */
	public function down_line(){
	    $account_info = $GLOBALS['account_info'];
	    $account_id = $account_info['id'];
	    $supplier_id = $account_info['supplier_id'];
	    //是否开启自动审核
	    $supplier_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$supplier_id);
	    if($supplier_data['allow_publish_verify']){
	        $allow_publish_verify = $supplier_data['allow_publish_verify'];
	        $publish_verify_balance = $supplier_data['publish_verify_balance'];
	        require_once APP_ROOT_PATH.'system/model/event.php';
	    }
	    $id = intval($_REQUEST['id']);
	
	    if($id>0){
	        //商户提交数据
	        $event_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where event_id =".$id." and supplier_id=".$supplier_id);
	        //真实活动数据
	        $event_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id=".$id." and supplier_id=".$supplier_id);
	        if($event_info){
	            //数据导入location_submit表
	            $data = array();
	            $data['admin_check_status'] = 0;
	            $data['biz_apply_status'] = 3;
	            $data['supplier_id'] = $supplier_id;
	            $data['account_id'] = $account_id;
	            $data['is_effect'] = 1;

	
	            if($event_submit_info){ //存在数据
	                if($allow_publish_verify){ //自动审核
	                    if($event_submit_info['biz_apply_status']!=3){ //更新状态
	                         $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",$data,"UPDATE","id=".$event_submit_info['id']);
	                    }
	                    event_auto_downline($event_submit_info['id']);
	                    $result['status'] = 1;
	                    $result['info'] = "下架申请成功";
	                }else{
	                    if($event_submit_info['biz_apply_status']!=3){ //更新状态
	                        $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",$data,"UPDATE","id=".$event_submit_info['id']);
	                        $result['status'] = 1;
	                        $result['info'] = "下架申请成功等待管理员审核";
	                    }elseif($deal_submit_info['biz_apply_status']==3){
	                        $result['status'] = 0;
	                        $result['info'] = "下架待审核中，请勿重复申请";
	                    }
	                }
	            }else{ //增加新数据
	
	                $data['event_id'] = $event_info['id'];
	                $data['name'] = $event_info['name'];
	                $data['cate_id'] = $event_info['cate_id'];
	                $data['city_id'] = $event_info['city_id'];
	                $data['create_time'] = NOW_TIME;
	                $data['icon'] = $event_info['icon'];
	                
	                $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",$data);
	                $id = $GLOBALS['db']->insert_id();
	                if($allow_publish_verify){ //自动审核
	                    event_auto_downline($id);
	                    $result['status'] = 1;
	                    $result['info'] = "下架申请成功";
	                }else{
	                    $result['status'] = 1;
	                    $result['info'] = "下架申请成功等待管理员审核";
	                }
	            }
	
	        }else{
	            $result['status'] = 0;
	            $result['info'] = "数据不存在或权限不足";
	
	        }
	    }else{
	        $result['status'] = 0;
	        $result['info'] = "请正确提交数据";
	    }
	    ajax_return($result);
	}

	
	/**
	 * 城市子集地区
	 */
	public function load_area_list_box()
	{
	    $id =  intval($_REQUEST['id']); //id
	    $city_id = intval($_REQUEST['city_id']);
	    $edit_type = intval($_REQUEST['edit_type']);

	    if($edit_type == 1){//来自管理员
	        $event_curr_area = $GLOBALS['db']->getAll("select area_id from ".DB_PREFIX."event_area_link where event_id = ".$id);
	        foreach ($event_curr_area as $k=>$v){
	            $f_curr_area[] = $v['area_id'];
	        }
	    }
	     
	    if($edit_type == 2){//来自商户提交
	        $event_curr_area = $GLOBALS['db']->getOne("select cache_event_area_link from ".DB_PREFIX."event_biz_submit where id = ".$id);
	        $f_curr_area = unserialize($event_curr_area);
	    }
	    $area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id);
	     
	     
	    foreach($area_list as $k=>$v)
	    {
	        if(in_array($v['id'], $f_curr_area))
	        {
	            $area_list[$k]['checked'] = true;
	        }
	    }
	    $GLOBALS['tmpl']->assign("area_list",$area_list);
	    echo $GLOBALS['tmpl']->fetch("inc/area_box.html");
	}
	
	/**
	 * 表单验证
	 */
	private function check_event_publish_data($data){
	    $id = intval($data['id']);
	    $edit_type = intval($data['edit_type']);
	    
	    if(strim($data['name'])==''){
	        $result['status'] = 0;
	        $result['info'] = '活动名称不允许为空';
	        ajax_return($result);
	    }
	    if ($is_err == 0 && intval($data['cate_id']) == 0) {
            $result['status'] = 0;
            $result['info'] = '请选择分类！';
            ajax_return($result);
        }
        if ($is_err == 0 && count($data['location_id']) <= 0) {
            $result['status'] = 0;
            $result['info'] = '至少支持一家门店！';
            ajax_return($result);
        }
	    return true;
	}
	
}
?>