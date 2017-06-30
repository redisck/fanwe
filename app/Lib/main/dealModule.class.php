<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dealModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		$deal_key = strim($_REQUEST['act']);
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal = get_deal($deal_key);	
		if($deal)
		{	
			if($deal['is_shop']==1)
			{
				if($deal['buy_type']==1)
					$GLOBALS['tmpl']->assign("cate_tree_type",2); //积分商城商品下拉菜单加载积分分类
				else
				{
					set_view_history("shop", $deal['id']);
					$history_ids = get_view_history("shop");
					$GLOBALS['tmpl']->assign("cate_tree_type",1);
				}
				$GLOBALS['tmpl']->assign("search_type",5);
			}
			else
			{
				set_view_history("deal", $deal['id']);
				$history_ids = get_view_history("deal");
			}			
	
			//浏览历史
			if($history_ids)
			{
				$ids_conditioin = " d.id in (".implode(",", $history_ids).") ";
				
				if($deal['is_shop']==0)
				{
					$history_deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);	
				}
				elseif($deal['is_shop']==1)
				{
					if($deal['buy_type']==0)
						$history_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
					
				}
				
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
			
			//$GLOBALS['tmpl']->assign("drop_nav","no_drop"); //首页下拉菜单不输出
			//$GLOBALS['tmpl']->assign("wrap_type","1"); //首页宽屏展示			
			$deal['description'] = format_html_content_image($deal['description'],720);
			$deal['notes'] = format_html_content_image($deal['notes'],720);
			$GLOBALS['tmpl']->assign("deal",$deal);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
			
			//输出右侧的其他团购
			if($deal['is_shop']==0)
			$side_deal_list = get_deal_list(5,array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type <> 1 and d.is_shop = 0 and d.id<>".$deal['id']);
			elseif($deal['is_shop']==1)
			{
				if($deal['buy_type']==1)
					$side_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['shop_cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type = 1 and d.is_shop = 1 and d.id<>".$deal['id']);
				else
					$side_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['shop_cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type <> 1 and d.is_shop = 1 and d.id<>".$deal['id']);
			}
	
				
			//$side_deal_list = get_deal_list(4,array(DEAL_ONLINE));
			$GLOBALS['tmpl']->assign("side_deal_list",$side_deal_list['list']);		
			

			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($deal['supplier_info']['name'])
			{
				$page_title.="[".$deal['supplier_info']['name']."]";
				$page_keyword.=$deal['supplier_info']['name'].",";
				$page_description.=$deal['supplier_info']['name'].",";
			}
			$page_title.= $deal['sub_name'];
			$page_keyword.=$deal['sub_name'].",";
			$page_description.=$deal['sub_name'].",";
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));			
			
			if($deal['cate_id'])
			{
				$deal['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']);
				$deal['cate_url'] = url("index","tuan",array("cid"=>$deal['cate_id']));				
			}
			elseif($deal['shop_cate_id'])
			{
				$deal['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".$deal['shop_cate_id']);
				if($deal['buy_type']==1)
				$deal['cate_url'] = url("index","scores",array("cid"=>$deal['shop_cate_id']));
				else
				$deal['cate_url'] = url("index","cate",array("cid"=>$deal['shop_cate_id']));
			}			
			if($deal['cate_name'])
			{
				$page_title.=" - ".$deal['cate_name'];
				$page_keyword.=$deal['cate_name'].",";
				$page_description.=$deal['cate_name'].",";
				$site_nav[] = array('name'=>$deal['cate_name'],'url'=>$deal['cate_url']);
			}
			$site_nav[] = array('name'=>$deal['sub_name'],'url'=>$deal['url']);				
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			//输出促销
			if($deal['allow_promote']==1)
			{
				$promote = load_auto_cache("cache_promote");
				$GLOBALS['tmpl']->assign("promote",$promote);
			}
			
			if($deal['seo_title'])$page_title = $deal['seo_title'];
			if($deal['seo_keyword'])$page_keyword = $deal['seo_keyword'];
			if($deal['seo_description'])$page_description = $deal['seo_description'];
			
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
			
			$GLOBALS['tmpl']->display("deal.html");
		}
		else
		{
			app_redirect_preview();
		}
		
		
	}
	

	
	
}
?>