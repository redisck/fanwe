<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class storeModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		
		$store_id = intval($_REQUEST['act']);
		require_once APP_ROOT_PATH."system/model/supplier.php";
		$store_info = get_location($store_id);
		if($store_info)
		{
			set_view_history("store", $store_info['id']);
			$history_ids = get_view_history("store");
			
			//浏览历史
			if($history_ids)
			{
				$ids_conditioin = " sl.id in (".implode(",", $history_ids).") ";
				$history_deal_list = get_location_list(app_conf("SIDE_DEAL_COUNT"),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
			
			
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
			
			$store_info['good_rate_precent'] = round($store_info['good_rate']*100,1);
			$store_info['ref_avg_price'] = round($store_info['ref_avg_price'],2);
			$store_info['brief'] = format_html_content_image($store_info['brief'],720);
			$GLOBALS['tmpl']->assign("store_info",$store_info);
			
			
			//开始输出商户图库数据json
			$store_images = $GLOBALS['db']->getAll("select brief,image from ".DB_PREFIX."supplier_location_images where supplier_location_id = ".$store_info['id']." and status = 1 order by sort limit ".MAX_SP_IMAGE);
			foreach($store_images as $k=>$v)
			{				
				$store_images[$k]['image'] = format_image_path(get_spec_image($v['image'],600,450,1));
			}
			$GLOBALS['tmpl']->assign("store_images_json",json_encode($store_images));
			$GLOBALS['tmpl']->assign("store_images_count",count($store_images));
			
			
			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			$page_title.= $store_info['name'];
			$page_keyword.=$store_info['name'].",";
			$page_description.=$store_info['name'].",";
				
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
				
			if($store_info['deal_cate_id'])
			{
				$store_info['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$store_info['deal_cate_id']);
				$store_info['cate_url'] = url("index","stores",array("cid"=>$store_info['deal_cate_id']));
			}
			if($store_info['cate_name'])
			{
				$page_title.=" - ".$store_info['cate_name'];
				$page_keyword.=$store_info['cate_name'].",";
				$page_description.=$store_info['cate_name'].",";
				$site_nav[] = array('name'=>$store_info['cate_name'],'url'=>$store_info['cate_url']);
			}
			$site_nav[] = array('name'=>$store_info['name'],'url'=>$store_info['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
				

			if($store_info['seo_title'])$page_title = $store_info['seo_title'];
			if($store_info['seo_keyword'])$page_keyword = $store_info['seo_keyword'];
			if($store_info['seo_description'])$page_description = $store_info['seo_description'];
				
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
			
			//输出右侧的其他团购
			require_once APP_ROOT_PATH."system/model/deal.php";
			$side_deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$store_info['deal_cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type <> 1 and d.is_shop = 0 ");
		
			//$side_deal_list = get_deal_list(4,array(DEAL_ONLINE));
			$GLOBALS['tmpl']->assign("side_deal_list",$side_deal_list['list']);
		}
		else
		{
			app_redirect_preview();
		}
		
		$GLOBALS['tmpl']->display("store.html");
	}
	
	
	
}
?>