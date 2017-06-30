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
class locationModule extends BizBaseModule
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
     
        $conditions .= " and id in(" . implode(",", $account_info['location_ids']) . ") ";
        
        
        $sql_count = " select count(distinct(id)) from " . DB_PREFIX . "supplier_location";
        $sql = " select distinct(id),name,preview,deal_cate_id from " . DB_PREFIX . "supplier_location";
        
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
        
        //分类数据集
        $deal_cate_id = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate");
        foreach($deal_cate_id as $k=>$v){
            $f_cate_id[$v['id']] = $v['name'];
        }
        foreach ($list as $k => $v) {
            $list[$k]['edit_url'] = url("biz", "location#edit", array(
                "id" => $v['id'],
                "edit_type" =>1
            ));
            $list[$k]['preview_url'] = url("index","preview#store",array("id"=>$v['id'],"type"=>0));
            $list[$k]['update_url'] = url("biz","location#update",array("id"=>$v['id']));
            
            if($v['deal_cate_id'])
                $list[$k]['cate_name'] = $f_cate_id[$v['deal_cate_id']];
            else
                $list[$k]['cate_name'] = "暂无";
        }
        
        /* 数据 */
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "location#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("biz", "location#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "location"));
	    $GLOBALS['tmpl']->assign("index_url", url("biz", "location#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "location#no_online_index"));

        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "门店列表管理");
        $GLOBALS['tmpl']->display("pages/location/index.html");
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
	        $filter_admin_check =-1;
	    }

	    /* 业务逻辑部分 */
	    $conditions .= " where is_effect = 1 "; // 查询条件
	
	    if ($account_info['is_main'] == 1) { // 总管理员
	        $conditions .= " and supplier_id = " . $supplier_id;
	    } else { // 子账户操作
	        // 只查询支持门店的
	        $conditions .= " and account_id =" . $account_id;
	    }
	
	    if ($filter_admin_check >= 0) {
	        $conditions .= " and admin_check_status = " . $filter_admin_check;
	    }

	    $sql_count = " select count(*) from " . DB_PREFIX . "supplier_location_biz_submit ";
	    $sql = " select id,name,preview,biz_apply_status,admin_check_status from " . DB_PREFIX . "supplier_location_biz_submit ";
	
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
	        $list[$k]['edit_url'] = url("biz", "location#edit", array(
	            "id" => $v['id'],
	            "edit_type" =>2
	        ));
	        $list[$k]['preview_url'] = url("index","preview#store",array("id"=>$v['id'],"type"=>1));
	    }

	    /* 数据 */
	    $GLOBALS['tmpl']->assign("filter_admin_check", $filter_admin_check);
	    $GLOBALS['tmpl']->assign("list", $list);
	    $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "location#publish"));
	    $GLOBALS['tmpl']->assign("form_url", url("biz", "location#no_online_index"));
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "location"));
	    $GLOBALS['tmpl']->assign("index_url", url("biz", "location#index"));
	    $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "location#no_online_index"));
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("page_title", "门底列表管理");
	    $GLOBALS['tmpl']->display("pages/location/index.html");
	}
	
	public function publish(){
	    /* 基本参数初始化 */
	    init_app_page();
	    
	    /* 业务逻辑 */
	    
	    // 支持门店
	    $location_infos = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
	    
	    /* 数据 */
	    $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持门店
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "location"));
	    $GLOBALS['tmpl']->assign("page_title", "新增门店");
	    $GLOBALS['tmpl']->display("pages/location/publish.html");
	}
	
	public function do_save_publish(){
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    $edit_type = intval($_REQUEST['edit_type']);
	    $id = intval($_REQUEST['id']);
	    if($edit_type == 1 && $id>0){ //判断是否有存在修改
	        $location_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_biz_submit where location_id = ".$id." and supplier_id = ".$supplier_id);
	        if($location_submit_info && $location_submit_info['admin_check_status']==0){//存在未审核数据
	            $result['status'] = 0;
	            $result['info'] = "已经存在申请操作，请先删除避免重复申请";
	            ajax_return($result);
	            exit;
	        }else{
	            $location_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_location
                            where is_effect = 1 and id=".$id." and supplier_id = ".$supplier_id."
                            and id in(" . implode(",", $account_info['location_ids']).")");
	    
	            if(empty($location_info)){
	                $result['status'] = 0;
	                $result['info'] = "数据不存在或没有权限操作该数据";
	                ajax_return($result);
	                exit;
	            }
	            $new_data = $location_info;
	            $new_data['location_id'] = $location_info['id'];
	            unset($new_data['id']);
	            $new_data['supplier_id'] = $supplier_id;
	            $new_data['account_id'] = $account_id;
	    
	            //如果数据已经有存在，通过审核的数据，先清除掉在进行插入更新操作
	            if($location_submit_info && $location_submit_info['admin_check_status']!=0){//删除已审核 或 拒绝的数据
	                $GLOBALS['db']->query("delete from ".DB_PREFIX."supplier_location_biz_submit where id=".$location_submit_info['id']);
	            }
	    
	            //先建立数据
	            $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_biz_submit",$new_data);
	            $location_submit_id = $GLOBALS['db']->insert_id();
	        }
	        
	    }
	    // 白名单过滤
	    require_once APP_ROOT_PATH . 'system/model/no_xss.php';
	    //数据验证
	    $this->check_location_publish_data($_REQUEST);
	    
	    $data['supplier_id'] = $supplier_id; // 所属商户
	    $data['account_id'] = $account_id;
	    $data['name'] = strim($_REQUEST['name']); // 名称
	    $data['is_dc'] = intval($_REQUEST['is_dc']); // 是否支持订餐
	    $data['tags'] = strim($_REQUEST['tags']); // 标签
	    //供应商标志图片
	    $preview_img = strim($_REQUEST['preview']); // 缩略图
	    if($id > 0){ //更新操作需要替换图片地址
	        $preview_img = replace_domain_to_public($preview_img);
	    }
	    $data['preview'] = $preview_img;
	    //图库
	    $location_images = $_REQUEST['location_images'];
	    foreach ($location_images as $k=>$v){
	        $cache_location_images[] = replace_domain_to_public($v);
	    }
	    $data['cache_supplier_location_images'] = serialize($cache_location_images);
	    
	    $data['city_id'] = intval($_REQUEST['city_id']); // 城市
	    $area_id = $_REQUEST['area_id']; // 地区列表
	    $data['cache_supplier_location_area_link'] = serialize($area_id);
	    
	    $data['deal_cate_id'] = intval($_REQUEST['cate_id']); // 分类
	    $deal_cate_type_id = $_REQUEST['deal_cate_type_id']; // 子分类
	    $data['cache_deal_cate_type_location_link'] = serialize($deal_cate_type_id);

	    $data['address'] = strim($_REQUEST['address']); // 地址
	    $data['route'] = strim($_REQUEST['route']); // 交通路线
	    $data['tel'] = strim($_REQUEST['tel']); // 联系电话
	    $data['contact'] = strim($_REQUEST['contact']); // 联系人
	    $data['open_time'] = strim($_REQUEST['open_time']); // 营业时间
	    $data['api_address'] = strim($_REQUEST['api_address']); // 地图定位的地址
	    $data['xpoint'] = strim($_REQUEST['xpoint']); // 经度
	    $data['ypoint'] = strim($_REQUEST['ypoint']); // 纬度
	    $data['brief'] = btrim(no_xss($_REQUEST['brief'])); // 部门简介
	    
	    
	    /*默认参数*/
	    $data['is_main'] = 0;
	    $data['is_effect'] = 1;
	    
	    // 管理员状态
	    $data['admin_check_status'] = 0; // 待审核
	    
	    if ($id > 0) {
	        if($edit_type == 1){
	            $id = $location_submit_id; //上面生成的记录IDs
	            $data['biz_apply_status'] = 2; // 修改申请
	        }
	        $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location_biz_submit", $data, "UPDATE", " id=" . $id . " and account_id=" . $account_id);
	        $result['status'] = 1;
	        $result['info'] = "修改成功，等待管理员审核";
	        $result['jump'] = url("biz","location#no_online_index");
	    } else {
	        $data['biz_apply_status'] = 1; // 新增申请

	        $list = $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location_biz_submit", $data);
	        if ($list) {
	            $result['status'] = 1;
	            $result['info'] = "提交成功，等待管理员审核";
	            $result['jump'] = url("biz","location#no_online_index");
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
	        $location_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_biz_submit where location_id = ".$id." and supplier_id = ".$supplier_id);
	        if($location_submit_info && $location_submit_info['admin_check_status']==0){
	            showBizErr("已经存在申请操作，请先删除避免重复申请",0,url("biz","location#index"));
	            exit;
	        }
	    }

	    /* 业务逻辑 */
	    $is_online = 0;
	    if ($edit_type == 1) {//管理员发布
	        /*********************************
	         * 取真正的门店数据表数据
	         ********************************/
	        $location_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_location  where is_effect = 1 and id=".$id." and supplier_id = ".$supplier_id."
                         and id in(" . implode(",", $account_info['location_ids']).")");

	        if (empty($location_info)) {
	            showBizErr("数据不存在或没有操作权限！",0,url("biz","location#index"));
	            exit();
	        }
	        $location_images_data = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "supplier_location_images  where supplier_location_id = ".$id);
	        foreach ($location_images_data as $k=>$v){
	            $location_images[] = $v['image'];
	        }
            
	        $go_list_url = url("biz","location#index");
	        $is_online = 1;
	    } elseif($edit_type == 2) {//商户提交
	        /**********************************
	         * 取商户提交数据表
	         *********************************/
	        $location_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_location_biz_submit where id=" . $id." and supplier_id = ".$supplier_id." and account_id = ".$account_id);
	
	        if (empty($location_info)) {
	            showBizErr("数据不存在或没有操作权限！",0,url("biz","location#no_online_index"));
	            exit();
	        }
	        
	        $location_images = unserialize($location_info['cache_supplier_location_images']);
	        $go_list_url = url("biz","location#no_online_index");
	        if($location_info['location_id']>0){$is_online = 1;}
	    }                                  

	


	    /* 数据 */
	    $GLOBALS['tmpl']->assign("is_online", $is_online); // 是否为在线数据
	    $GLOBALS['tmpl']->assign("vo", $location_info); // 门店所有数据
	    $GLOBALS['tmpl']->assign("location_images", $location_images); // 图库
	    $GLOBALS['tmpl']->assign("edit_type", $edit_type); // 请求数据类型
	    $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
	
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "location"));
	    $GLOBALS['tmpl']->assign("page_title", "门店项目编辑");
	    $GLOBALS['tmpl']->display("pages/location/edit.html");
	}
	/**
	 * 时时更新的商户数据
	 */
	public function update(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    $id = intval($_REQUEST['id']);
        
	    $location_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "supplier_location  where is_effect = 1 and id=".$id." and supplier_id = ".$supplier_id."
                         and id in(" . implode(",", $account_info['location_ids']).")");
	    
	    if (empty($location_info)) {
	        showBizErr("数据不存在或没有操作权限！",0,url("biz","location#index"));
	        exit();
	    }
	    $location_images_data = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "supplier_location_images  where supplier_location_id = ".$id);
	    foreach ($location_images_data as $k=>$v){
	        $location_images[] = $v['image'];
	    }
	    
	    $go_list_url = url("biz","location#index");
	    
	    /* 数据 */
	    
	    $GLOBALS['tmpl']->assign("vo", $location_info); // 门店所有数据
	    $GLOBALS['tmpl']->assign("location_images", $location_images); // 图库

	    $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
	    
	    /* 系统默认 */
	    $GLOBALS['tmpl']->assign("ajax_url", url("biz", "location"));
	    $GLOBALS['tmpl']->assign("page_title", "门店资料更新");
	    $GLOBALS['tmpl']->display("pages/location/update.html");
	}
	
	/**
	 * 保存时时更新的商户数据
	 * */
	public function do_update(){
	    /* 基本参数初始化 */
	    init_app_page();
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	    
	    // 白名单过滤
	    require_once APP_ROOT_PATH . 'system/model/no_xss.php';
	    
	    /*获取参数*/
	    $id = intval($_REQUEST['id']);
	    
	    $location_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$id." and supplier_id=".$supplier_id);
	    if(empty($location_data)){
	        $result['status'] = 0;
	        $result['info'] = "数据不存在或没有权限操作该数据";
	        ajax_return($result);
	        exit;
	    }
	    
	    //供应商标志图片
	    $preview_img = strim($_REQUEST['preview']); // 缩略图
	    $data['preview'] = replace_domain_to_public($preview_img);
	    
	    //图库
	    $location_images = $_REQUEST['location_images'];
	    foreach ($location_images as $k=>$v){
	        $f_location_images[] = replace_domain_to_public($v);
	    }
	    $data['address'] = strim($_REQUEST['address']); // 地址
	    $data['route'] = strim($_REQUEST['route']); // 交通路线
	    $data['tel'] = strim($_REQUEST['tel']); // 地址
	    $data['contact'] = strim($_REQUEST['contact']); // 联系人
	    $data['open_time'] = strim($_REQUEST['open_time']); // 营业时间
	    $data['api_address'] = strim($_REQUEST['api_address']); // 地图定位的地址
	    $data['xpoint'] = strim($_REQUEST['xpoint']); // 经度
	    $data['ypoint'] = strim($_REQUEST['ypoint']); // 纬度
	    $data['brief'] = btrim(no_xss($_REQUEST['brief'])); // 部门简介
	    $data['adv_img_1'] = btrim(no_xss($_REQUEST['adv_img_1'])); // 门店顶部广告位1
	    $data['adv_img_2'] = btrim(no_xss($_REQUEST['adv_img_2'])); // 门店侧边广告位2
	    $data['location_qq'] = strim($_REQUEST['location_qq']); // 门店客服QQ

	    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location", $data, "UPDATE", " id=" . $id." and supplier_id=".$supplier_id);
        if($GLOBALS['db']->affected_rows()){
            //更新图库
            if(count($f_location_images)>0){
                $GLOBALS['db']->query("delete from ".DB_PREFIX."supplier_location_images where supplier_location_id=".$supplier_id);
              
                foreach($f_location_images as $k=>$v){
                    $imgdata = array();
                    $imgdata['image'] = $v;
                    $imgdata['sort'] = 100;
                    $imgdata['create_time'] = NOW_TIME;
                    $imgdata['supplier_location_id'] = $id;
                    $imgdata['status'] = 1;
                    
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location_images", $imgdata);
                }
            }
            $GLOBALS['db']->autoExecute(DB_PREFIX . "supplier_location", array("image_count"=>count($f_location_images)), "UPDATE", " id=" . $id." and supplier_id=".$supplier_id);
            
            //更新后更新商户缓存
            rm_auto_cache("store",array("id"=>$id));
            $result['status'] = 1;
            $result['info'] = "修改成功";
        }else{
            $result['status'] = 0;
            $result['info'] = "修改失败，请稍后再试";
        }
	    
        $result['jump'] = url("biz","location#index");
		
		ajax_return($result);
	    
	    
	}
	
	public function del()
	{
	    /* 基本参数初始化 */
	    $id = intval($_REQUEST['id']);
	    $account_info = $GLOBALS['account_info'];
	    $supplier_id = $account_info['supplier_id'];
	    $account_id = $account_info['id'];
	
	    /* 业务逻辑 */
	    if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "supplier_location_biz_submit where id=" . $id . " and account_id =" . $account_id)) {
	        // 存在切用户有权限删除
	        $GLOBALS['db']->query("delete from " . DB_PREFIX . "supplier_location_biz_submit where id=" . $id . " and account_id =" . $account_id);
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
	
	    $id = intval($_REQUEST['id']);
	
	    if($id>0){
	        //商户提交数据
	        $location_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_biz_submit where location_id =".$id." and supplier_id=".$supplier_id);
	        //真实门店数据
	        $location_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$id." and supplier_id=".$supplier_id);
	        if($location_info){
	            //数据导入location_submit表
	            $data = array();
	            $data['admin_check_status'] = 0;
	            $data['biz_apply_status'] = 3;
	            $data['supplier_id'] = $supplier_id;
	            $data['account_id'] = $account_id;
	            $data['is_effect'] = 1;

	
	            if($location_submit_info){ //存在数据
	                if($location_submit_info['biz_apply_status']!=3){ //更新状态
	
	                    $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_biz_submit",$data,"UPDATE","id=".$location_submit_info['id']);
	                    $result['status'] = 1;
	                    $result['info'] = "下架申请成功等待管理员审核";
	                }elseif($deal_submit_info['biz_apply_status']==3){
	                    $result['status'] = 0;
	                    $result['info'] = "下架待审核中，请勿重复申请";
	                }
	            }else{ //增加新数据
	
	                $data['location_id'] = $location_info['id'];
	                $data['name'] = $location_info['name'];
	                $data['deal_cate_id'] = $location_info['deal_cate_id'];
	                $data['city_id'] = $location_info['city_id'];
	                $data['create_time'] = NOW_TIME;
	                $data['preview'] = $location_info['preview'];
	                
	                $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_biz_submit",$data);
	                $result['status'] = 1;
	                $result['info'] = "下架申请成功等待管理员审核";
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
	 * 加载子分类
	 */
	public function load_sub_cate(){
	    $cate_id = intval($_REQUEST['cate_id']);
	    $edit_type = intval($_REQUEST['edit_type']);
	    $id = intval($_REQUEST['id']);
	
	    $sub_cate_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_cate_type as c left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = c.id where l.cate_id = ".$cate_id);
	    if($edit_type == 1){ //管理员添加数据
	        $sub_cate_arr_data = $GLOBALS['db']->getAll("select deal_cate_type_id from ".DB_PREFIX."deal_cate_type_location_link where location_id = ".$id);
	        foreach ($sub_cate_arr_data as $k=>$v){
	            $sub_cate_arr[] = $v['deal_cate_type_id'];
	        }
	
	    }elseif ($edit_type == 2){//商户提交数据
	        $sub_cate_arr = unserialize($GLOBALS['db']->getOne("select cache_deal_cate_type_location_link from ".DB_PREFIX."supplier_location_biz_submit where id=".$id));  //序列化的字段
	    }
	    //处理选择状态
	    foreach ($sub_cate_list as $k=>$v){
	        if(in_array($v['id'], $sub_cate_arr)){
	            $sub_cate_list[$k]['checked'] =1 ;
	        }
	    }
	
	
	    $html = '';
	    if($sub_cate_list){
	        $result['status'] = 1;
	        foreach($sub_cate_list as $k=>$v){
	            if($v['checked']){
	                $html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$v['id'].'" checked="checked"/>'.$v['name'].'</label>';
	            }else{
	                $html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$v['id'].'" />'.$v['name'].'</label>';
	            }
	        }
	
	    }else
	        $result['status'] = 0;
	
	    $result['html'] = $html;
	    ajax_return($result);
	}
	
	/**
	 * 城市子集地区
	 */
	public function load_area_list_box()
	{
	    $id =  intval($_REQUEST['id']); //门店id
	    $city_id = intval($_REQUEST['city_id']);
	    $edit_type = intval($_REQUEST['edit_type']);
	    
	    if($edit_type == 1){//来自管理员
	        $location_curr_area = $GLOBALS['db']->getAll("select area_id from ".DB_PREFIX."supplier_location_area_link where location_id = ".$id);
	        foreach ($location_curr_area as $k=>$v){
	            $f_curr_area[] = $v['area_id'];
	        }
	    }
	    
	    if($edit_type == 2){//来自商户提交
	        $location_curr_area = $GLOBALS['db']->getOne("select cache_supplier_location_area_link from ".DB_PREFIX."supplier_location_biz_submit where id = ".$id);
	        $f_curr_area = unserialize($location_curr_area);
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
	private function check_location_publish_data($data){
	    $id = intval($data['id']);
	    $edit_type = intval($data['edit_type']);
	    
	    if(strim($data['name'])==''){
	        $result['status'] = 0;
	        $result['info'] = '门店名称不允许为空';
	        ajax_return($result);
	    }
	    $conditions = " where name='".strim($data['name'])."'";
	    if($edit_type == 1 && $id>0){ //后台数据
	        $conditions .= " and id<>".$id;
	    }elseif($edit_type==2 && $id>0){
	        $location_id =  $GLOBALS['db']->getOne("select location_id from ".DB_PREFIX."supplier_location_biz_submit where id=".$id);
	        $conditions .= " and id<>".$location_id;
	    }
	    

	    $sql = "select count(*) from ".DB_PREFIX."supplier_location ";
	    /*查询是否有重复数据*/
	    if($GLOBALS['db']->getOne($sql.$conditions)){
	        $result['status'] = 0;
	        $result['info'] = '门店名称已被使用';
	        ajax_return($result);
	    }
	    return true;
	}
	
}
?>