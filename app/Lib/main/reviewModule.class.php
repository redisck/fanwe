<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class reviewModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		if(empty($GLOBALS['user_info']))
		{
			app_redirect(url("index","user#login"));
		}

		require_once APP_ROOT_PATH."system/model/review.php";
		
		$order_id = intval($_REQUEST['id']);  //订单ID
	
		
		$location_id = intval($_REQUEST['location_id']);
		
		if($location_id>0)
		{
			require_once APP_ROOT_PATH."system/model/supplier.php";
			$location_info = get_location($location_id);
			if($location_info)
			{
				//验证是否可以点评
				
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				
				if($order_id>0)
				{
					
					$url=url("index","dc_dcorder");

					if($order_info['confirm_status']!=2)
					{
						showErr("此订单未完结",0,$url);
					}
				
					if($order_info['is_dp']>0)
					{
						showErr("此订单已点评",0,$url);
					}	
				
				}
				else
				{
				showErr("没有订单无法点评此商家");
				}
				
				$dp_data = load_dp_info(array("location_id"=>$location_id));
				$dp_cfg = load_dp_cfg(array("cate_id"=>$location_info['deal_cate_id']));
		
				$item_info['id'] = $location_info['id'];
				$item_info['key'] = 'location_id';
				$item_info['name'] = $location_info['name'];
				$item_info['url'] = $location_info['url'];
				$item_info['image'] = $location_info['preview'];
				$item_info['order_id'] = $order_id;
				
				$GLOBALS['tmpl']->assign("dp_data",$dp_data);
				$GLOBALS['tmpl']->assign("dp_cfg",$dp_cfg);
				$GLOBALS['tmpl']->assign("item_info",$item_info);
		
		
				//输出导航
				$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
				$site_nav[] = array('name'=>$location_info['name'],'url'=>url("index","review",array("location_id"=>$location_info['id'])));
				$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		
				//输出seo
				$page_title = "";
				$page_keyword = "";
				$page_description = "";
				if($location_info['supplier_info']['name'])
				{
					$page_title.="[".$location_info['supplier_info']['name']."]";
					$page_keyword.=$location_info['supplier_info']['name'].",";
					$page_description.=$location_info['supplier_info']['name'].",";
				}
				$page_title.= $location_info['name'];
				$page_keyword.=$location_info['name'];
				$page_description.=$location_info['name'];
				$GLOBALS['tmpl']->assign("page_title",$page_title);
				$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
				$GLOBALS['tmpl']->assign("page_description",$page_description);
			}
			else
			{
				showErr("你要点评的商家不存在");
			}
		}

		else
		{
			app_redirect(url("index"));
		}

		$GLOBALS['tmpl']->display("review.html");
	}
	
	
	
	public function save()
	{		
		global_run();
		if(empty($GLOBALS['user_info']))
		{
			$data['status']=-1;
			$data['info'] = "";
			ajax_return($data);
		}
		require_once APP_ROOT_PATH."system/model/review.php";
		
	
		$location_id = intval($_REQUEST['location_id']);
		$order_id = intval($_REQUEST['order_id']);
		$param = array(
			"deal_id"	=> $deal_id,
			"youhui_id"	=> $youhui_id,
			"event_id"	=>	$event_id,
			"location_id"	=> $location_id,
			"order_item_id"	=> $order_item_id,
			"youhui_log_id"	=>	$youhui_log_id,
			"event_submit_id"	=> $event_submit_id		
		);
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				
				if($order_id>0)
				{
					
					$url=url("index","dc_dcorder");

					if($order_info['confirm_status']!=2)
					{
						showErr("此订单未完结",0,$url);
					}
				
					if($order_info['is_dp']>0)
					{
						showErr("此订单已点评",0,$url);
					}	
				
				}
				else
				{
				showErr("没有订单无法点评此商家");
				}
		
		$content = strim(valid_str($_REQUEST['content']));  //点评内容

		$dp_point = intval($_REQUEST['dp_point']); //总评分
		if($dp_point<=0)
		{
			$data['status']=0;
			$data['info'] = "请为总评打分";
			ajax_return($data);
		}
		
		$dp_image = array(); //点评图片
		foreach($_REQUEST['dp_image'] as $k=>$v)
		{
			if(strim($v)!="")
				$dp_image[] = strim($v);
		}
		
		$tag_group = array(); //标签分组
		foreach($_REQUEST['dp_tags'] as $k=>$tags_arr)
		{
			foreach($tags_arr as $v)
			{
				if(strim($v)!="")
				{
					$v_array = preg_split("/[ ,]/", $v);
					foreach($v_array as $kk=>$vv)
					{
						if(strim($vv)!="")
						$tag_group[$k][] = strim(valid_str($vv));
					}				
				}	
			}			
		}

		
		$point_group = array(); //评分分组
		foreach($_REQUEST['dp_point_group'] as $k=>$v)
		{
			if(intval($v)>0)
			{
				$point_group[$k] = intval($v);
 			}
 			else
 			{
 				$name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."point_group where id = ".intval($k));
 				$info = "请打分";
 				if($name)
 					$info = "请为".$name."打分";
 				$data['status']=0;
 				$data['info'] = $info;
 				ajax_return($data);
 			}
		}
		
		$result = save_review($GLOBALS['user_info']['id'], $param, $content, $dp_point, $dp_image, $tag_group,$point_group);
		if($result['status'])
		{	
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set is_dp = 1 where id = ".$order_id);
			
			//分享
			$attach_list = array();
			
		
		
			if($result['location_id'])
			{
				require_once APP_ROOT_PATH."system/model/supplier.php";
				$location_info = get_location($result['location_id']);
				
				$type = "slocationcomment";
				$url_route = array(
						'rel_app_index'	=>	'index',
						'rel_route'	=>	'store#'.$result['location_id'],
						'rel_param' => ''
				);
				
				//同步图片
				if($location_info['preview'])
				{
					require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
					$imagecls = new es_imagecls();
					$info = $imagecls->getImageInfo(APP_ROOT_PATH.$location_info['preview']);
						
					$image_data['width'] = intval($info[0]);
					$image_data['height'] = intval($info[1]);
					$image_data['name'] = $location_info['name'];
					$image_data['filesize'] = filesize(APP_ROOT_PATH.$location_info['preview']);
					$image_data['create_time'] = NOW_TIME;
					$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
					$image_data['user_name'] = strim($GLOBALS['user_info']['user_name']);
					$image_data['path'] = $location_info['preview'];
					$image_data['o_path'] = $location_info['preview'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);
						
					$img_id = intval($GLOBALS['db']->insert_id());
					$attach_list[] = array("type"=>"image","id"=>intval($img_id));
				}
			}
			
			foreach($_REQUEST['topic_image_id'] as $att_id)
			{
				if(intval($att_id)>0)
					$attach_list[] = array("type"=>"image","id"=>intval($att_id));
			}
				
			
			require_once APP_ROOT_PATH."system/model/topic.php";			
			
			$tid = insert_topic($content,"",$type,$group="", $relay_id = 0, $fav_id = 0,$group_data = "",$attach_list,$url_route);
			if($tid)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
			}
			$result['jump'] = url($url_route['rel_app_index'],$url_route['rel_route'],$url_route['rel_param']);
			ajax_return($result);
		}
		else
		{
			ajax_return($result);
		}
		
	}
	
}
?>