<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/main/core/dc_init.php";
/**
 * 外卖订餐
 * 
 *
 */
class dcpositionModule extends MainBaseModule
{

	/**
	 * 外卖定位页面
	 * 
	 * 输出
	 * s_info：Array 当明地理位置信息
	 *    结构如下
	 *     Array
        (
            [dc_title] => 宝龙城市广场
            [city_name] => 福州
        )
	 * city_name：string当前城市名称
	 * 
	 * 
*/
	public function index()
	{		
		
		global_run();
		dc_global_run();
		init_app_page();
		$param=array();
		$data = request_api("dcposition","index",$param);
		//参数处理
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$GLOBALS['tmpl']->assign("s_info",$s_info);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		
		$GLOBALS['tmpl']->assign("data",$data);
		$dc_search_history_str=es_cookie::get('dc_search_history');
		$dc_search_history=array();
		$dc_search_history=json_decode($dc_search_history_str,true);
		$dc_search_history=array_values($dc_search_history);
		$GLOBALS['tmpl']->assign('dc_search_history',$dc_search_history);
		$GLOBALS['tmpl']->display("dc/dc_position.html");

	}
	
	
	

	/**
	 * 搜索历史的保存
	 * 输入：
	 * xpoint：float 经度
	 * ypoint：float 维度
	 * dc_title:搜索地址
	 * dc_content：搜索地址下面的具体信息
	 * dc_num：商家个数
	 */
	public function do_position(){
		global_run();
	
		if($_REQUEST['xpoint']!='' && $_REQUEST['ypoint']!='' && $_REQUEST['dc_title']!='' && $_REQUEST['dc_content']!='' && $_REQUEST['dc_num']!='' ){
	
			$dc_search['dc_xpoint']=floatval($_REQUEST['xpoint']);
			$dc_search['dc_ypoint']=floatval($_REQUEST['ypoint']);
			$dc_search['dc_title']=strim($_REQUEST['dc_title']);
			$dc_search['dc_content']=strim($_REQUEST['dc_content']);
			$dc_search['dc_num']=intval($_REQUEST['dc_num']);
			$dc_search['city_name']=$GLOBALS['city']['name'];
			$dc_search_history_str=es_cookie::get('dc_search_history');
			$dc_search_history=array();
			$dc_search_history=json_decode($dc_search_history_str,true);
			$search_key=md5($dc_search['dc_xpoint'].$dc_search['dc_ypoint'].$dc_search['dc_title'].$dc_search['dc_content']);
			$dc_search_history_new[$search_key]=$dc_search;
			foreach($dc_search_history as $k=>$v){
				if($k!=$search_key){
					$dc_search_history_new[$k]=$v;
				}
			}
			es_cookie::set('dc_search_history', json_encode($dc_search_history_new),3600*24*7);
			$result['status']= 1;
			ajax_return($result);
		}else{
			$result['status']= 0;
			ajax_return($result);
				
		}
	
	}
	
	/**
	 * 搜索历史的清空
	 */
	public function clear_history(){
	
		es_cookie::delete('dc_search_history');
		$data['status']= 1;
		ajax_return($data);
	}
	

	
}
?>