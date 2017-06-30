<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class youhuiModule extends MainBaseModule
{
	public function index()
	{		
		if(trim($_REQUEST['act'])=="print")
		{
			$this->doprint();
			exit;
		}
		global_run();
		init_app_page();
		$id = intval($_REQUEST['act']);
		require_once APP_ROOT_PATH."system/model/youhui.php";
		$youhui = get_youhui($id);
		
		if($youhui)
		{
			
			set_view_history("youhui", $youhui['id']);
			$history_ids = get_view_history("youhui");
			
			//浏览历史
			if($history_ids)
			{
				$ids_conditioin = " y.id in (".implode(",", $history_ids).") ";
				$history_deal_list = get_youhui_list(app_conf("SIDE_DEAL_COUNT"),array(YOUHUI_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
						
				//重新组装排序
				$history_list = array();
				foreach($history_ids as $k=>$v)
				{
					foreach($history_deal_list['list'] as $history_item)
					{
						if($history_item['id']==$v)
						{
							$history_list[] = $history_item;
						}
					}
				}
				$GLOBALS['tmpl']->assign("history_deal_list",$history_list);
			}
			
			$youhui['description'] = format_html_content_image($youhui['description'],720);
			$youhui['use_notice'] = format_html_content_image($youhui['use_notice'],720);
			$GLOBALS['tmpl']->assign("youhui",$youhui);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
				
			//输出右侧的其他优惠券
			$side_youhui_list = get_youhui_list(app_conf("SIDE_DEAL_COUNT"),array(YOUHUI_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",""," y.user_count desc ");
			$GLOBALS['tmpl']->assign("side_youhui_list",$side_youhui_list['list']);
			
			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($youhui['supplier_info']['name'])
			{
				$page_title.="[".$youhui['supplier_info']['name']."]";
				$page_keyword.=$youhui['supplier_info']['name'].",";
				$page_description.=$youhui['supplier_info']['name'].",";
			}
			$page_title.= $youhui['name'];
			$page_keyword.=$youhui['name'].",";
			$page_description.=$youhui['name'].",";
				
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
				
			if($youhui['deal_cate_id'])
			{
				$youhui['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$youhui['deal_cate_id']);
				$youhui['cate_url'] = url("index","youhuis",array("cid"=>$youhui['deal_cate_id']));
			}
			
			if($youhui['cate_name'])
			{
				$page_title.=" - ".$youhui['cate_name'];
				$page_keyword.=$youhui['cate_name'].",";
				$page_description.=$youhui['cate_name'].",";
				$site_nav[] = array('name'=>$youhui['cate_name'],'url'=>$youhui['cate_url']);
			}
			$site_nav[] = array('name'=>$youhui['name'],'url'=>$youhui['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
				

			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
				
			$GLOBALS['tmpl']->display("youhui.html");
			
		}
		else
		{
			app_redirect_preview();
		}

		
	}

	
	public function doprint()
	{
		global_run();
		init_app_page();
		if(empty($GLOBALS['user_info']))
		{
			app_redirect(url("index","user#login"));
		}
		$id = intval($_REQUEST['id']);
		$log = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
		if($log)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."youhui set print_count = print_count + 1 where id = ".$log['youhui_id']);
			require_once APP_ROOT_PATH."system/model/youhui.php";
			$youhui_info = get_youhui($log['youhui_id']);
			if($youhui_info)
			{
				$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);
				$GLOBALS['tmpl']->assign("log",$log);
				$GLOBALS['tmpl']->display("youhui_print.html");
			}
			else
			{
				showErr("优惠券已下架");
			}
			
		}
		else
		{
			app_redirect_preview();
		}
	}
}
?>