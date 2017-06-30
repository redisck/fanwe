<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once(APP_ROOT_PATH.'system/libs/msg.php');
class dp_msg implements msg 
{		
	public function send_msg($user_id,$content,$data_id)
	{
		$msg = array();
		$msg['content'] = $content;
		$msg['user_id'] = $user_id;
		$msg['create_time'] = NOW_TIME;
		$msg['type'] = "dp";
		$msg['data_id'] = $data_id;		
		$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$data_id);
		if($data['deal_id']>0)
		{
			require_once APP_ROOT_PATH."system/model/deal.php";
			$data_info  = get_deal($data['deal_id']);
			$data['url'] = $data_info['url'];
			$data['icon'] = $data_info['icon'];
		}
		elseif($data['youhui_id']>0)
		{
			require_once APP_ROOT_PATH."system/model/youhui.php";
			$data_info = get_youhui($data['youhui_id']);
			$data['url'] = $data_info['url'];
			$data['icon'] = $data_info['icon'];
		}
		elseif($data['event_id']>0)
		{
			require_once APP_ROOT_PATH."system/model/event.php";
			$data_info = get_event($data['event_id']);
			$data['url'] = $data_info['url'];
			$data['icon'] = $data_info['icon'];
		}
		else
		{
			require_once APP_ROOT_PATH."system/model/supplier.php";
			$data_info = get_location($data['supplier_location_id']);
			$data['url'] = $data_info['url'];
			$data['icon'] = $data_info['preview'];
		}
		$msg['data'] = serialize($data);		
		$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg,"INSERT","","SILENT");
	} 	

	/**
	 * 加载相应的类型消息
	 * @param unknown_type $msg  数据集(即数据库中的对应消息行)
	 *
	 * 返回：array("id"=>"当前消息ID",title="标题",is_read=>"是否已读","icon"=>"相关数据的图片(可为空)","content"=>"内容","create_time"=>"时间","link"=>"(可为空)相关数据的跳转链接");
	 */
	public function load_msg($msg)
	{
		if(!$msg['data'])
		{
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$msg['data_id']);
			if($data['deal_id']>0)
			{
				require_once APP_ROOT_PATH."system/model/deal.php";
				$data_info  = get_deal($data['deal_id']);
				$data['url'] = $data_info['url'];
				$data['icon'] = $data_info['icon'];
				$data['title'] = $data_info['name'];
			}
			elseif($data['youhui_id']>0)
			{
				require_once APP_ROOT_PATH."system/model/youhui.php";
				$data_info = get_youhui($data['youhui_id']);
				$data['url'] = $data_info['url'];
				$data['icon'] = $data_info['icon'];
				$data['title'] = $data_info['name'];
			}
			elseif($data['event_id']>0)
			{
				require_once APP_ROOT_PATH."system/model/event.php";
				$data_info = get_event($data['event_id']);
				$data['url'] = $data_info['url'];
				$data['icon'] = $data_info['icon'];
				$data['title'] = $data_info['name'];
			}
			else
			{
				require_once APP_ROOT_PATH."system/model/supplier.php";
				$data_info = get_location($data['supplier_location_id']);
				$data['url'] = $data_info['url'];
				$data['icon'] = $data_info['preview'];
				$data['title'] = $data_info['name'];
			}
			$msg['data'] = serialize($data);
			$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg,"UPDATE","id=".$msg['id'],"SILENT");
		}
		$data = unserialize($msg['data']);
		$msg['icon'] = $data['icon'];
		$msg['link'] = $data['url'];
		$msg['title'] = "对 [".$data['title']."] 的点评";
		$msg['short_title'] = "对 [".msubstr($msg['title'])."] 的点评";
		return $msg;
	}
	
	public function load_type()
	{
		return "用户点评相关消息";
	}
}
?>