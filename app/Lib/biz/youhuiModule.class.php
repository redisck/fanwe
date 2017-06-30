<?php
/**
 * 商户中心优惠券管理
 * @author Administrator
 *
 */
require APP_ROOT_PATH . 'app/Lib/page.php';

class youhuiModule extends BizBaseModule
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
        
        /* 获取参数 */
        
        /* 业务逻辑部分 */
        $conditions .= " where y.is_effect = 1 and  y.supplier_id = ".$supplier_id; // 查询条件
        

        // 需要连表操作 只查询支持门店的
        $join = " left join ".DB_PREFIX."youhui_location_link as yl on yl.youhui_id = y.id ";
        $conditions .= " and yl.location_id in(" . implode(",", $account_info['location_ids']) . ") ";
        
        
        $sql_count = " select count(distinct(y.id)) from " . DB_PREFIX . "youhui as y ";
        $sql = " select distinct(y.id),y.name,y.begin_time,y.end_time,y.image,y.icon from " . DB_PREFIX . "youhui as y ";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)  $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count . $join . $conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);		
        $list = $GLOBALS['db']->getAll($sql . $join . $conditions . " order by y.id desc limit " . $limit);
        
        foreach ($list as $k => $v) {
            $list[$k]['begin_time'] = $v['begin_time'] != 0 ? to_date($v['begin_time']) : "不限";
            $list[$k]['end_time'] = $v['end_time'] != 0 ? to_date($v['end_time']) : "不限";
            $list[$k]['images'][0]['img'] = $v['icon'];
            $list[$k]['edit_url'] = url("biz", "youhui#edit", array("id" => $v['id'],"edit_type" =>1));
            $list[$k]['preview_url'] = url("index", "preview#youhui", array( "id" => $v['id'],"type" =>0));
        }
       
        /* 数据 */
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "youhui#publish"));
        $GLOBALS['tmpl']->assign("form_url", url("biz", "youhui#index"));
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "youhui"));
        $GLOBALS['tmpl']->assign("index_url", url("biz", "youhui#index"));
        $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "youhui#no_online_index"));
        
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "优惠券项目管理");
        $GLOBALS['tmpl']->display("pages/project/index.html");
    }

    /**
     * 未发布的列表
     */
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
        $conditions .= " where 1= 1 "; // 查询条件
        
        if ($account_info['is_main'] == 1) { // 总管理员
            $conditions .= " and y.supplier_id = " . $supplier_id;
        } else { // 子账户操作
               // 只查询支持门店的
            $conditions .= " and y.account_id =" . $account_id;
        }
        
        
    	if ($filter_admin_check >= 0)  {
            $conditions .= " and admin_check_status = " . $filter_admin_check;
        }
        
        $sql_count = " select count(*) from " . DB_PREFIX . "youhui_biz_submit as y";
        $sql = " select y.id,y.name,y.begin_time,y.end_time,y.biz_apply_status,y.admin_check_status,y.image,y.icon,y.youhui_id from ".DB_PREFIX."youhui_biz_submit as y";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0) $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;

        $total = $GLOBALS['db']->getOne($sql_count . $conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);

        $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
        
        foreach ($list as $k => $v) {
        	$list[$k]['begin_time'] = $v['begin_time'] != 0 ? to_date($v['begin_time']) : "不限";
            $list[$k]['end_time'] = $v['end_time'] != 0 ? to_date($v['end_time']) : "不限";
            $list[$k]['images'][] = $v['icon'];
            $list[$k]['edit_url'] = url("biz", "youhui#edit", array("id" => $v['id'],"edit_type" =>2));
            $list[$k]['preview_url'] = url("index", "preview#youhui", array( "id" => $v['id'],"type" =>1));
        }
        
        /* 数据 */
        $GLOBALS['tmpl']->assign("filter_admin_check", $filter_admin_check);
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "youhui#publish"));
        $GLOBALS['tmpl']->assign("form_url", url("biz", "youhui#no_online_index"));
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "youhui"));
        $GLOBALS['tmpl']->assign("index_url", url("biz", "youhui#index"));
        $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "youhui#no_online_index"));
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "优惠券项目管理");
        $GLOBALS['tmpl']->display("pages/project/index.html");
    }

    /**
     * 优惠券添加
     */
    public function publish()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        
        // 支持门店
        $location_infos = $GLOBALS['db']->getAll("select id,name,xpoint,ypoint from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");

        // 商品类型
        $goods_type_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "goods_type");
        
        /* 数据 */
        $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持门店 
        $GLOBALS['tmpl']->assign("goods_type_list", $goods_type_list); // 商品分类

        //是否开启自动审核
        $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
                
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "youhui"));
        $GLOBALS['tmpl']->assign("page_title", "优惠券项目发布");
        $GLOBALS['tmpl']->display("pages/project/youhui_publish.html");
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
            $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_biz_submit where youhui_id = ".$id." and supplier_id = ".$supplier_id);
            if($deal_submit_info && $deal_submit_info['admin_check_status']==0){
                showBizErr("已经存在申请操作，请先删除避免重复申请",0,url("biz","youhui#index"));
                exit;
            }
        }
        
        /* 业务逻辑 */
        
        if ($edit_type == 1) {//管理员发布
            /*********************************
             * 取真正的商品、团购数据表数据
             ********************************/
            $youhui_info = $GLOBALS['db']->getRow("select y.* from ".DB_PREFIX."youhui as y left join " . DB_PREFIX . "youhui_location_link as yl on yl.youhui_id = y.id  where y.is_effect = 1  and id=".$id." and supplier_id = ".$supplier_id." and yl.location_id in(" . implode(",", $account_info['location_ids']).")");

            if (empty($youhui_info)) {
                showBizErr("数据不存在或没有操作权限！",0,url("biz","youhui#index"));
                exit();
            }
            
            //支持门店 , 门店选中状态
            $location_infos = $GLOBALS['db']->getAll("select id,name,xpoint,ypoint from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");//该账户权限门店
            $curr_location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."youhui_location_link where  youhui_id = ".$id);//该优惠券门店
            foreach($curr_location_list as $k=>$v){
                $curr_locations[] = $v['location_id']; 
            }
            
            foreach ($location_infos as $k => $v) {
                if (in_array($v['id'], $curr_locations) ) {
                    $location_infos[$k]['checked'] = 1;
                }
            }
  
            // 选中子分类
            $select_sub_cate = $GLOBALS['db']->getOne("select group_concat(Convert(deal_cate_type_id , char)) from ".DB_PREFIX."deal_cate_type_youhui_link where youhui_id = ".$id);
			$go_list_url = url("biz","youhui#index");
        } elseif($edit_type == 2) {//商户提交
            /**********************************
             * 取商户提交数据表
             *********************************/
            $youhui_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "youhui_biz_submit where id=".$id." and supplier_id = ".$supplier_id);

            if (empty($youhui_info)) {
                showBizErr("数据不存在或没有操作权限！",0,url("biz","youhui#no_online_index"));
                exit();
            }
            // 支持门店 , 门店选中状态
            $cache_location_id = unserialize($youhui_info['cache_youhui_location_link']);
            $location_infos = $GLOBALS['db']->getAll("select id,name,xpoint,ypoint from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
            
            foreach ($location_infos as $k => $v) {
                if (in_array($v['id'], $cache_location_id)) {
                    $location_infos[$k]['checked'] = 1;
                }
            }
            
            // 选中子分类
            $select_sub_cate = implode(",", unserialize($youhui_info['cache_deal_cate_type_youhui_link']));            
			$go_list_url = url("biz","youhui#no_online_index");
        }        
        
        // 时间格式化
        $youhui_info['begin_time'] = to_date($youhui_info['begin_time'], "Y-m-d H:i");
        $youhui_info['end_time'] = to_date($youhui_info['end_time'], "Y-m-d H:i");

        $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持门店
        $GLOBALS['tmpl']->assign("select_sub_cate", $select_sub_cate); // 选中的子分类数据
        $GLOBALS['tmpl']->assign("youhui_info", $youhui_info); // 商品所有数据
        $GLOBALS['tmpl']->assign("edit_type", $edit_type); // 请求数据类型
        $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接

        //是否开启自动审核
        $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
        
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "youhui"));
        $GLOBALS['tmpl']->assign("page_title", "优惠券项目编辑");
        $GLOBALS['tmpl']->display("pages/project/youhui_edit.html");
    }

    public function del()
    {
        /* 基本参数初始化 */
        init_app_page();
        
        $id = intval($_REQUEST['id']);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        /* 业务逻辑 */
        if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "youhui_biz_submit where id=" . $id . " and account_id =" . $account_id)) {
            // 存在切用户有权限删除
            $GLOBALS['db']->query("delete from " . DB_PREFIX . "youhui_biz_submit where id=" . $id . " and account_id =" . $account_id);
            $data['status'] = 1;
            $data['info'] = "删除成功";
        } else {
            $data['status'] = 0;
            $data['info'] = "数据不存在货没有管理权限";
        }
        ajax_return($data);
    }

    /**
     * 保存团购产品数据
     */
    public function do_save_publish()
    {
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        //print_r($_REQUEST);exit;
        $edit_type = intval($_REQUEST['edit_type']);
        $id = intval($_REQUEST['id']);
        if($edit_type == 1 && $id>0){ //判断是否有存在修改
            $youhui_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_biz_submit where youhui_id = ".$id." and supplier_id = ".$supplier_id);
            if($youhui_submit_info && $youhui_submit_info['admin_check_status']==0){
                $result['status'] = 0;
                $result['info'] = "已经存在申请操作，请先删除避免重复申请";
                ajax_return($result);
                exit;
            }else{
                $youhui_info = $GLOBALS['db']->getRow("select y.* from " . DB_PREFIX . "youhui as y 
                        left join " . DB_PREFIX . "youhui_location_link yl on yl.youhui_id = y.id  
                            where y.is_effect = 1 and y.id=".$id." and y.supplier_id = ".$supplier_id."
                            and yl.location_id in(".implode(",", $account_info['location_ids']).")");

                if(empty($youhui_info)){
                    $result['status'] = 0;
                    $result['info'] = "数据不存在或没有权限操作该数据";
                    ajax_return($result);
                    exit;
                }
                $new_data = $youhui_info;
                $new_data['youhui_id'] = $youhui_info['id'];
                $new_data['is_effect'] = 1;
                unset($new_data['id']);
                $new_data['supplier_id'] = $supplier_id;
                $new_data['account_id'] = $account_id;
                
                //如果数据已经有存在，通过审核的数据，先清除掉在进行插入更新操作
                if($youhui_submit_info && $youhui_submit_info['admin_check_status']==1){
                    $GLOBALS['db']->query("delete from ".DB_PREFIX."youhui_biz_submit where id=".$youhui_submit_info['id']);
                }
              
                //先建立数据
                $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit",$new_data);
                $youhui_submit_id = $GLOBALS['db']->insert_id();
            }
        
        }
        // 白名单过滤
        require_once APP_ROOT_PATH . 'system/model/no_xss.php';
       
        $this->check_publish_data($_REQUEST);
        
        $data['supplier_id'] = $supplier_id; // 所属商户
        $data['account_id'] = $account_id;
        $data['name'] = strim($_REQUEST['name1']); // 优惠券名称
		$data['icon'] = strim($_REQUEST['icon']); // 优惠券名称
		$data['image'] = strim($_REQUEST['image']); // 优惠券名称
        if($id > 0){ //更新操作需要替换图片地址            
			$data['icon']=replace_domain_to_public($data['icon']);
			$data['image']=replace_domain_to_public($data['image']);
        }
        $data['begin_time'] = strim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time'], "Y-m-d H:i");
        $data['end_time'] = strim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time'], "Y-m-d H:i");
		$data['expire_day'] = intval($_REQUEST['expire_day']); // 有效天数
		$data['total_num'] = intval($_REQUEST['total_num']); // 总条数
		$data['user_limit'] = intval($_REQUEST['user_limit']); // 下载限制		
        $data['city_id'] = intval($_REQUEST['city_id']); // 城市
        $data['deal_cate_id'] = intval($_REQUEST['cate_id']); // 分类
        $data['youhui_type'] = intval($_REQUEST['youhui_type']); // 优惠券类型
        $data['xpoint'] = strim($_REQUEST['xpoint']); 
        $data['ypoint'] = strim($_REQUEST['ypoint']); 
        $data['is_effect'] =1; 
        $data['list_brief'] = strim($_REQUEST['list_brief']); // 简介        
        $data['description'] = btrim(no_xss($_REQUEST['description']));
        $data['use_notice'] = btrim(no_xss($_REQUEST['use_notice']));
        $data['create_time'] = NOW_TIME;  

        
        $deal_cate_type_id = $_REQUEST['deal_cate_type_id']; // 子分类
        foreach ($deal_cate_type_id as $k=>$v){
        	$deal_cate_type_id[$k]=intval($v);
        }
        $location_id = $_REQUEST['location_id']; // 支持门店
        foreach ($location_id as $k=>$v){
        	$location_id[$k]=intval($v);
        } 
        $data['cache_deal_cate_type_youhui_link'] = serialize($deal_cate_type_id);
        $data['cache_youhui_location_link'] = serialize($location_id);


        
        // 管理员状态
        $data['admin_check_status'] = 0; // 待审核
        
        if ($id > 0) {
            if($edit_type == 1){
                $id = $youhui_submit_id; //上面生成的记录IDs
                $data['biz_apply_status'] = 2; // 修改申请
            }

            $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit", $data, "UPDATE", " id=".$id . " and account_id=" . $account_id);
            $result['status'] = 1;
            $result['info'] = "修改成功，等待管理员审核";
            $result['jump'] = url("biz", "youhui#no_online_index");
        } else {
            $data['biz_apply_status'] = 1; // 新增申请
            $list = $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit", $data);
            if ($list&&$GLOBALS['db']->error()=='') {
            	$youhui_submit_id=$GLOBALS['db']->insert_id();
                $result['status'] = 1;
                $result['info'] = "提交成功，等待管理员审核";
                $result['jump'] = url("biz", "youhui#no_online_index");
            }
        }
        
        
        
        //自动审核
 		 $allow_publish_verify=$GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id);      
        if($allow_publish_verify==1){
        	 require_once APP_ROOT_PATH . 'system/model/youhui.php';
        	 if(youhui_check_biz_submit($youhui_submit_id,$supplier_id)){
        	 	$result['info'] = "发布成功";
        	 }
        }
        
        
        
 		 ajax_return($result);
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
            require_once APP_ROOT_PATH.'system/model/youhui.php';
        }
        $id = intval($_REQUEST['id']);
        
        if($id>0){
            //商户提交数据
            $youhui_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_biz_submit where youhui_id =".$id." and supplier_id=".$supplier_id);
            //真实团购数据
            $youhui_info = $GLOBALS['db']->getRow("select y.* from ".DB_PREFIX."youhui as y left join " . DB_PREFIX . "youhui_location_link as  yl on yl.youhui_id = y.id where y.id=".$id." and yl.location_id in(".implode(",", $GLOBALS['account_info']['location_ids']).")");
            if($youhui_info){
                //数据导入 deal_submit表
                $data = array();      
                $data['admin_check_status'] = 0;
                $data['biz_apply_status'] = 3;
                $data['supplier_id'] = $supplier_id;
                $data['account_id'] = $account_id;

                
                if($youhui_submit_info){ //存在数据
                    if($allow_publish_verify){ //自动审核
                        if($youhui_submit_info['biz_apply_status']!=3){ //更新状态
                            $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit",$data,"UPDATE","id=".$youhui_submit_info['id']);
                        }
                        youhui_auto_downline($youhui_submit_info['id']);
                        $result['status'] = 1;
                        $result['info'] = "下架申请成功";
                    }else{
                        if($youhui_submit_info['biz_apply_status']!=3){ //更新状态
                            $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit",$data,"UPDATE","id=".$youhui_submit_info['id']);
                            $result['status'] = 1;
                            $result['info'] = "下架申请成功等待管理员审核";
                        }elseif($youhui_submit_info['biz_apply_status']==3){
                            $result['status'] = 0;
                            $result['info'] = "下架待审核中，请勿重复申请";
                        }
                    }
                    
                }else{ //增加新数据

                    $data['youhui_id'] = $youhui_info['id'];
                    $data['name'] = $youhui_info['name'];
                    $data['deal_cate_id'] = $youhui_info['deal_cate_id'];
                    $data['city_id'] = $youhui_info['city_id'];
                    $data['icon'] = $youhui_info['icon'];
                    $data['create_time'] = $youhui_info['create_time'];

                    $GLOBALS['db']->autoExecute(DB_PREFIX."youhui_biz_submit",$data);
                    $id = $GLOBALS['db']->insert_id();
                    if($allow_publish_verify){ //自动审核
                        youhui_auto_downline($id);
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
     * 验证提交的 团购商品数据是否符合
     * 
     * @param unknown $data            
     */
    function check_publish_data($data)
    {
        $is_err = 0;
        if (strim($data['name1']) == '' &&$is_err == 0) {
            $result['status'] = 0;
            $result['info'] = '优惠券名称不能为空！';
            $is_err = 1;
        }
        if ($is_err == 0 && intval($data['city_id']) == 0) {
            $result['status'] = 0;
            $result['info'] = '请选择城市！';
            $is_err = 1;
        }
        if ($is_err == 0 && intval($data['cate_id']) == 0) {
            $result['status'] = 0;
            $result['info'] = '请选择分类！';
            $is_err = 1;
        }
        if ($is_err == 0 && count($data['location_id']) <= 0) {
            $result['status'] = 0;
            $result['info'] = '至少支持一家门店！';
            $is_err = 1;
        }
        if ($is_err == 0 && strim($data['icon']) == '') {
            $result['status'] = 0;
            $result['info'] = '请上传优惠券列表图！';
            $is_err = 1;
        }
        if ($is_err == 0 && strim($data['image']) == '') {
            $result['status'] = 0;
            $result['info'] = '请上传优惠券打印图！';
            $is_err = 1;
        }

        if ($is_err == 1) {
            $result['jump'] = '';
            ajax_return($result);
        }
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
            $sub_cate_arr_data = $GLOBALS['db']->getAll("select deal_cate_type_id from ".DB_PREFIX."deal_cate_type_youhui_link where youhui_id = ".$id);
            foreach ($sub_cate_arr_data as $k=>$v){
                $sub_cate_arr[] = $v['deal_cate_type_id'];
            }
    
        }elseif ($edit_type == 2){//商户提交数据
            $sub_cate_arr = unserialize($GLOBALS['db']->getOne("select cache_deal_cate_type_youhui_link from ".DB_PREFIX."youhui_biz_submit where id=".$id));  //序列化的字段  
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
    
    
    
}

?>