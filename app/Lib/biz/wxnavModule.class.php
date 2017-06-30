<?php 
/**
 * 微信菜单设置
 */

class wxnavModule extends BizBaseModule
{
    
	function __construct()
	{
        parent::__construct();
        global_run();
        $this->check_auth();
    }
	
    
	public function index()
	{		
				
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		

		$navs = require_once APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/wxnav_cfg.php";
		
		$main_navs=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and pid = 0 ");
  		 
		foreach($main_navs as $k=>$v){
					$temp = unserialize($v['data']);
					$v['data'] = $temp[$navs[$v['ctl']]['field']];
					$v['data_name'] = $navs[$v['ctl']]['fname'];
					$result_navs[] = $v;
					 
					//$sub_navs = M("WeixinNav")->where(array('account_id'=>0,'pid'=>$v['id']))->order('sort asc')->findAll();
					$sub_navs=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and pid =".$v['id']." order by sort asc");
					foreach($sub_navs as $kk=>$vv){
						$temp = unserialize($vv['data']);
						$vv['data'] = $temp[$navs[$vv['ctl']]['field']];
						$vv['data_name'] = $navs[$vv['ctl']]['fname'];
						$result_navs[] = $vv;
					} 					
 		}
 		//print_r($result_navs);exit;		 
		$GLOBALS['tmpl']->assign("result_navs",$result_navs);

 		$GLOBALS['tmpl']->assign("navs",$navs);
 		$GLOBALS['tmpl']->assign("navs_json",json_encode($navs));		
		
		
		
		$GLOBALS['tmpl']->assign("head_title","公众号菜单设置");
		$GLOBALS['tmpl']->display("pages/weixin/wxnav.html");	
	
	}
	

	
	public function new_nav_row(){
		$row_type= strim($_REQUEST['row_type']) == "main" ? "main" : "sub";
		if($row_type=="sub"){
			$pid = intval($_REQUEST['id']);		
			$item['pid'] = $pid;
			 
			$GLOBALS['tmpl']->assign("item",$item);
		}
		$navs = require_once APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/wxnav_cfg.php";
		
		$GLOBALS['tmpl']->assign("row_type",$row_type);
 		$GLOBALS['tmpl']->assign("navs",$navs);
		echo $GLOBALS['tmpl']->fetch("pages/weixin/new_nav_row.html");
	}	
	
	
	
	
	public function nav_save()
	{	
		

	//print_r($_REQUEST);exit;
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);		

		$navs = require_once APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/wxnav_cfg.php";
		$ids = $_REQUEST['id'];
		if(count($ids) == 0){
 			$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id);
			$data['status'] = 1;
			$data['info'] = "保存成功";
			ajax_return($data);			
		}
			
		//先验证
		$main_count = 0;
		$sub_count = array();
		foreach($_POST['row_type'] as $k=>$v){
			if($v=="main"){
				$main_count++;
				foreach($_POST['pid'] as $kk=>$pid){
					if(intval($pid)>0&&intval($pid)==intval($_POST['id'][$k])){
						$sub_count[$pid] = intval($sub_count[$pid])+1;
					}
				}
			}
		}

		if($main_count>3){
			$data['status'] = 0;
			$data['info'] = "主菜单不能超过3个";
			ajax_return($data);	
		}
		foreach ($sub_count as $sub_c)
		{
			if(intval($sub_c)>5){
			$data['status'] = 0;
			$data['info'] = "子菜单不能超过5个";
			ajax_return($data);
			}
		}

		$saved_ids = array();
		
		foreach($ids as $k=>$id){
			$id = intval($id);			
			if($id>0){
				//更新
				$nav_data['name'] = trim($_REQUEST['name'][$k]);
				$nav_data['sort'] = intval($_REQUEST['sort'][$k]);
				$nav_data['pid'] = intval($_REQUEST['pid'][$k]);
				$nav_data['ctl'] = strim($_REQUEST['ctls'][$k]);
				
				$datas = strim($_REQUEST['data'][$k]);
				$field = $navs[$nav_data['ctl']]['field'];
				if($field)
				{
					if($field=='spid'){
						$datas=$supplier_id;
					}
					$nav_data['data'] = serialize(array($field=>$datas));
				}				
				
				$nav_data['status'] = 0;
				$nav_data['account_id']=$supplier_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_nav",$nav_data,'update',"id=".$id);
				
 				array_push($saved_ids, $id);
			}else{
				//新增
				$nav_data['name'] = trim($_REQUEST['name'][$k]);
				$nav_data['sort'] = intval($_REQUEST['sort'][$k]);
				$nav_data['pid'] = intval($_REQUEST['pid'][$k]);
				$nav_data['ctl'] = strim($_REQUEST['ctls'][$k]);
				
				$datas = strim($_REQUEST['data'][$k]);
				$field = $navs[$nav_data['ctl']]['field'];
				if($field)
				{
					if($field=='spid'){
						$datas=$supplier_id;
					}
					$nav_data['data'] = serialize(array($field=>$datas));
				}				
				
				$nav_data['status'] = 0;
				$nav_data['account_id']=$supplier_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_nav",$nav_data);
				$nid = $GLOBALS['db']->insert_id();	
				array_push($saved_ids,intval($nid));
			}
		}
	
//		$condition['account_id'] = 0;
//		$condition['id'] = array('not in',$saved_ids);
//		$del_items = M("WeixinNav")->where($condition)->findAll();
		$saved_ids=implode(',',$saved_ids);
		$del_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and id not in (".$saved_ids.")");
		foreach($del_items as $it){
			$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and pid=".$it['id']);
			//M("WeixinNav")->where(array('pid'=>$it['id']))->delete();
		}
		
		//M("WeixinNav")->where(array('account_id'=>0,'id'=>array('not in',$saved_ids)))->delete();
		$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and id not in (".$saved_ids.")");	
		$data['status'] = 1;
		$data['info'] = "保存成功";
		ajax_return($data);		
		
	}
	

	public function syn_to_weixin(){

		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);		
		
		$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where type=0 and user_id=".$supplier_id);
		if(!$account){
			$datas['status'] = 0;
			$datas['info'] = "请先将微信基本配置补充完整";
			ajax_return($datas);
		}		
		//开始获取微信的token
		$weixin_app_id = $account['authorizer_appid'];
		$weixin_app_key = $account['authorizer_access_token'];
		if($weixin_app_id=="" || $weixin_app_key==""){
				$datas['status'] = 0;
				$datas['info'] = "请先设置公众号授权";
				ajax_return($datas);
		}
		require APP_ROOT_PATH."system/wechat/platform_wechat.class.php";
		$weixin_conf = load_auto_cache("weixin_conf");
		
		$option = array(		 		
		 				'authorizer_access_token'=>$account ['authorizer_access_token'],
		 				'authorizer_access_token_expire'=>$account ['expires_in'],
		 				'authorizer_appid'=>$account['authorizer_appid'],
		 				'authorizer_refresh_token'=>$account ['authorizer_refresh_token'],		 		
		 				'logcallback'=>'log_result',
		 				
		 		);		
		
		$platform= new PlatformWechat($option);
  	 	$platform_authorizer_token=$platform->check_platform_authorizer_token();
 		if($platform_authorizer_token)
 		{
 				//开始读取菜单配置
				$navs =$GLOBALS['db']->getAll("select * from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and pid=0 order by sort asc"); 
 				foreach($navs as $k=>$v){
 					$data = unserialize($v['data']);
 					if($v['ctl']=="url")
 						$navs[$k]['url'] = $data['url'];
 					else
 						$navs[$k]['url'] = SITE_DOMAIN.wap_url("index",$v['ctl'],$data);
 					
					$sub_navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."weixin_nav where account_id=".$supplier_id." and pid=".$v['id']." order by sort asc");
					foreach($sub_navs as $kk=>$vv)
					{
						$data = unserialize($vv['data']);
						if($vv['ctl']=="url")
							$sub_navs[$kk]['url'] = $data['url'];
						else
							$sub_navs[$kk]['url'] = SITE_DOMAIN.wap_url("index",$vv['ctl'],$data);
						
					}
					$navs[$k]['sub_button'] = $sub_navs;
				}
				$button_data = array();
				foreach($navs as $k=>$v){
					$button_data[$k]['name'] = $v['name'];
					if(count($v['sub_button'])==0){
						
							if(strtolower(substr($v['url'], 0,7))=="http://"){
								$button_data[$k]['type'] = "view";
								$button_data[$k]['url'] = $v['url'];
									
							}else{
								$button_data[$k]['type'] = "click";
								$button_data[$k]['key'] = $v['url'];
							}						
							
					}else{
						$sub_button_data = array();
						foreach($v['sub_button'] as $kk=>$vv){
							$sub_button_data[$kk]['name'] = $vv['name'];
					
							if(strtolower(substr($vv['url'], 0,7))=="http://"){
								$sub_button_data[$kk]['type'] = "view";
								$sub_button_data[$kk]['url'] = $vv['url'];
							}else{
								$sub_button_data[$kk]['type'] = "click";
								$sub_button_data[$kk]['key'] = $vv['url'];
							}								
						}
						$button_data[$k]['sub_button'] = $sub_button_data;
					}					
				}
				
				$json_data['button'] = $button_data;
 				$result=$platform->createMenu($json_data);
 				
				if($result){
 					if(!isset($result['errcode']) || intval($result['errcode'])==0){
 						$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_nav",array('status'=>1),'UPDATE',"account_id=".$supplier_id);						
						$datas['status'] = 1;
						$datas['info'] = "同步成功";
						ajax_return($datas);
					}else{
						$datas['status'] = 0;
						$datas['info'] = "同步出错，错误代码".$result['errcode'].":".$result['errmsg'];
						ajax_return($datas);						
 						//$this->error("同步出错，错误代码".$result['errcode'].":".$result['errmsg'],$this->isajax);
					}
				}else{
						$datas['status'] = 0;
						$datas['info'] = $platform->errMsg;
						ajax_return($datas);
						//$this->error($platform->errMsg,1);
				}
			}else{
					$datas['status'] = 0;
					$datas['info'] = "通讯出错，请重试";
					ajax_return($datas);
					//$this->error("通讯出错，请重试",1);
		    }
	}	
	

}
?>