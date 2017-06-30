<?php
class dcajaxModule extends MainBaseModule
{

	

	/**
	 * 添加或者取消餐厅收藏
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dcajax&act=add_location_collect&r_type=2&location_id=41
	 * 
	 * 输入：
	 * location_id：商家ID
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：操作返回的状态：status=0，操作失败，status=1，为收藏成功或取消收藏成功
	 * info，当state=0时的错误提示信息，如： 无效商家
	 * 
	 * 
	 */
	public function add_location_collect()
	{
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$location_id = intval($GLOBALS['request']['location_id']);
			$location_info = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."supplier_location where id = ".$location_id." and is_effect = 1");
			if($location_info)
			{
	
				$sql = "INSERT INTO `".DB_PREFIX."dc_location_sc` (`id`,`location_id`, `user_id`, `add_time`) select '','".$location_info['id']."','".intval($GLOBALS['user_info']['id'])."','".get_gmtime()."' from dual where not exists (select * from `".DB_PREFIX."dc_location_sc` where `location_id`= '".$location_info['id']."' and `user_id` = ".intval($GLOBALS['user_info']['id']).")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0){		

					$root['is_collected']=1;
					output($root,1,$GLOBALS['lang']['COLLECT_SUCCESS']);
					
					
				}else{
					$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_location_sc where location_id=".$location_info['id']." and user_id=".intval($GLOBALS['user_info']['id']));
					if($GLOBALS['db']->affected_rows()>0){	
						$root['is_collected']=0;
						output($root,1,$GLOBALS['lang']['LOCATION_COLLECT_CANCEL']);
					}
	
				}
			}
			else
			{
				output($root,0,$GLOBALS['lang']['INVALID_LOCATION']);
			}
		}
	}
	

	
	/*
	 * 返回当前用户的ID
	 */
	public function get_user_id(){
	
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		ajax_return($user_id);
	}
	
	

	
}
?>