<?php 
/**
 * 商户账户管理
 */

class accountModule extends BizBaseModule{
    function __construct()
    {
        parent::__construct();
        global_run();
        $this->check_auth();
    }
    public function index(){
    
        init_app_page();
        if(!$GLOBALS['account_info']){
            es_session::set("msg_info", "请用主管理员账户登录查看！");
            app_redirect(url("biz","user#login"));
        }
        $account_info = $GLOBALS['account_info'];
        $account_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_account WHERE supplier_id=".$account_info['supplier_id']." AND id<>".$account_info['id']);
        foreach($account_list as $k=>$v){
            $f_account_list[$v['id']] = $v;
            $acct_ids[] = $v['id'];
        }
    
    
        //获取门店
        $location_links = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_account_location_link WHERE account_id in(".implode(",", $acct_ids).")");
        foreach($location_links as $k=>$v){
            $f_locations_links[$v['account_id']][] = $v['location_id'];
            $location_ids[] = $v['location_id'];
        }
    
        $location_ids = array_unique($location_ids);
        $locations = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_location WHERE id in(".implode(",", $location_ids).")");
        foreach($locations as $k=>$v){
            $f_locations[$v['id']]= $v;
        }
    
        foreach($f_locations_links as $k=>$v){
            	
            $temp_location = array();
            foreach($v as $kk=>$vv){
                $temp_location[$vv] = $f_locations[$vv]['name'];
            }
            $result_data[$k]['account_info'] = $f_account_list[$k];
            $result_data[$k]['location_infos'] = $temp_location;
        }
    
        $GLOBALS['tmpl']->assign("account_info",$account_info);
        $GLOBALS['tmpl']->assign("account_list",$result_data);
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "子账户管理");
        $GLOBALS['tmpl']->display("pages/account/index.html");
    
    }
	public function add_account(){
		init_app_page();
		if(!$GLOBALS['account_info']){
			es_session::set("msg_info", "请用主管理员账户登录查看！");
			app_redirect(url("biz","user#login"));
		}
		$account_info = $GLOBALS['account_info'];
		$locations = $GLOBALS['db']->getAll("SELECT id,name FROM ".DB_PREFIX."supplier_location WHERE supplier_id=".$account_info['supplier_id']);
		
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->assign("account_info",$account_info);
		$GLOBALS['tmpl']->display("pages/account/add_account.html");
	}
	
	public function do_account(){
		global_run();
		$account_info = $GLOBALS['account_info'];
		$account_id = intval($_REQUEST['id']);
		$data = array();
		$data['status'] = 1;
		$data['info'] = '';
		$data['field'] = '';
		if(!$GLOBALS['account_info']['is_main']){//非管理员直接退出
			$data['status'] = 0;
			$data['info'] = '非主管理员账户无法创建';
			ajax_return($data);
		}

		$account_name = strim($_REQUEST['account_name']);
		$account_password = strim($_REQUEST['account_password']);
		$login_password = strim($_REQUEST['login_password']);
		$description = strim($_REQUEST['description']);
		
		if($account_id){ //修改操作
			if(count($_REQUEST['location']) == 0){
				$data['status'] = 0;
				$data['info'] = '至少设置一个账号所属门店';
				$data['field'] = '';
				ajax_return($data);
			}else{
				$location_ids= $_REQUEST['location'];
			}
			
			if($account_password && strlen($account_password)<6){
				$data['status'] = 0;
				$data['info'] = "新密码长度不能小于6位";
				$data['field'] = 'account_password';
				ajax_return($data);
			}
			
			//删除原有门店重新设置
			$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."supplier_account_location_link WHERE account_id = ".$account_id);
			if($account_password){
				$ins_data['account_password'] = md5($account_password);
			}
			$ins_data['description'] = $description;
			$ins_data['update_time'] = NOW_TIME;
				
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account",$ins_data,"UPDATE","id=".$account_id);
			foreach ($location_ids as $k=>$v){
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account_location_link",array("account_id"=>$account_id,"location_id"=>$v));
			}

		}else{//新增操作
		
			if(count($_REQUEST['location']) == 0){
				$data['status'] = 0;
				$data['info'] = '至少设置一个账号所属门店';
				$data['field'] = '';
				ajax_return($data);
			}else{
				$location_ids= $_REQUEST['location'];
			}
			
			if($account_name == ''){
				$data['status'] = 0;
				$data['info'] = '帐号名称不能为空';
				$data['field'] = 'account_name';
				ajax_return($data);
			}
			if($account_password == ''){
				$data['status'] = 0;
				$data['info'] = '帐号密码不能为空';
				$data['field'] = 'account_password';
				ajax_return($data);
			}
			if(strlen($account_password)<6){
				$data['status'] = 0;
				$data['info'] = "新密码长度不能小于6位";
				$data['field'] = 'account_password';
				ajax_return($data);
			}
			if($login_password == ''){
				$data['status'] = 0;
				$data['info'] = '验证登录密码不能为空';
				$data['field'] = 'login_password';
				ajax_return($data);
			}
			if(md5($login_password)!=$account_info['account_password']){
				$data['status'] = 0;
				$data['info'] = '验证登录密码错误';
				$data['field'] = 'login_password';
				ajax_return($data);
			}
			
			$ins_data['account_name'] = $account_name;
			$ins_data['account_password'] = md5($account_password);
			$ins_data['description'] = $description;
			$ins_data['supplier_id'] = $account_info['supplier_id'];
			$ins_data['update_time'] = NOW_TIME;
			$ins_data['is_effect'] = 1;
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account",$ins_data);
			$account_id = $GLOBALS['db']->insert_id();
			foreach ($location_ids as $k=>$v){
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account_location_link",array("account_id"=>$account_id,"location_id"=>$v));
			}
			
		}
		$data['jump'] = url("biz","account#set_auth",array("account_id"=>$account_id));
		$data['jump2'] = url("biz","account#account_list");
		ajax_return($data);
		
	}
	
	public function set_auth(){
        init_app_page();
		$account_id = intval($_REQUEST['account_id']);
		$auth_list = require_once APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/biznode_cfg.php";

		if(OPEN_WEIXIN)
		{
			$weixin_conf = load_auto_cache("weixin_conf");
			if($weixin_conf['platform_status']==1&&$GLOBALS['account_info']['platform_status']==1)
			{
			$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/wxbiznode_cfg.php";
			$auth_list = array_merge_biznode($auth_list, $config_file);
			}
		}
		if(OPEN_FX)
		{
			$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/fxbiznode_cfg.php";
			$auth_list = array_merge_biznode($auth_list, $config_file);
		}
		if(OPEN_DC)
		{
			$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/dcbiznode_cfg.php";
			$auth_list = array_merge_biznode($auth_list, $config_file);
		}
		
		$account_auth = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_account_auth WHERE supplier_account_id=".$account_id);
		
		$format_auth = array();
		foreach($account_auth as $k=>$v){
			$has_auth[] = $v['module'];
		}
		$has_auth = array_unique($has_auth);
// 		print_r($auth_list);exit;
		//处理选中状态
		foreach($auth_list as $k=>$v){
				$temp_check = 0;
				$note_count = count($v['node']);
				$temp_count = 0;
				foreach($v['node'] as $nk=>$nv){
					if(in_array($nk,$has_auth)){
						$auth_list[$k]['node'][$nk]['is_check'] = 1;
						$temp_count++;
					}
				}
				if($note_count == $temp_count)
					$auth_list[$k]['is_check'] = 1;
		}

		$GLOBALS['tmpl']->assign("account_id",$account_id);
		$GLOBALS['tmpl']->assign("auth_list",$auth_list);
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "权限设置");
		$GLOBALS['tmpl']->display("pages/account/set_auth.html");
	}
	
	public function do_auth(){
		$account_info = $GLOBALS['account_info'];
		$module_list = $_REQUEST['module']; 
		$account_id = intval($_REQUEST['account_id']);
		if(!$account_info['is_main']){//非主管理员直接退出
			$data['status'] = 0;
			$data['info'] = '非主帐号';
			ajax_return($data);
		}
		$supp_acct_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_account WHERE id=".$account_id);
		
		if($account_info['supplier_id'] != $supp_acct_info['supplier_id']){//非同一个商户下帐号不允许修改
			$data['status'] = 0;
			$data['info'] = '非同一商户';
			ajax_return($data);
		}
		//删除原有权限
		$GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."supplier_account_auth WHERE supplier_account_id=".$account_id);

		//重新插入权限
		foreach($module_list as $k=>$v){
			$v = strtolower($v);

			$ins_data = array();
			$ins_data['supplier_account_id'] = $account_id;
			$ins_data['module'] = $v;
			$ins_data['node'] = 'index';
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account_auth",$ins_data);
		}
		$data['status'] = 1;
		$data['info'] = '设置成功';
		$data['jump'] = url("biz","account#index");
		ajax_return($data);
		
	}
	
	
	
	public function set_account(){
	    init_app_page();
		$account_id = intval($_REQUEST['id']);
		
		if(!$GLOBALS['account_info']){
			es_session::set("msg_info", "请用主管理员账户登录查看！");
			app_redirect(url("biz","user#login"));
		}
		if(!$account_id){
			app_redirect("biz","account#add_account");
		}
		
		$account_info = $GLOBALS['account_info'];
		$locations = $GLOBALS['db']->getAll("SELECT id,name FROM ".DB_PREFIX."supplier_location WHERE supplier_id=".$account_info['supplier_id']);
		$account_links =  $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_account_location_link WHERE account_id=".$account_id); 
		foreach($account_links as $k=>$v){
			$check_location[] = $v['location_id'];
		}

		foreach($locations as $k=>$v){
			if(in_array($v['id'], $check_location)){
				$locations[$k]['is_check'] = 1;
			}
		}
		$set_account_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_account WHERE id=".$account_id);
		$GLOBALS['tmpl']->assign("set_account_info",$set_account_info);
		$GLOBALS['tmpl']->assign("locations",$locations);
		$GLOBALS['tmpl']->assign("account_info",$account_info);
		
		/* 系统默认 */
		$GLOBALS['tmpl']->assign("page_title", "编辑子账户");
		$GLOBALS['tmpl']->display("pages/account/set_account.html");
	}
}
?>