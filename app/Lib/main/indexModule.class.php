<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class indexModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['id']);
		if (!$GLOBALS['tmpl']->is_cached('index.html', $cache_id))
		{
			init_app_page();
			
			$GLOBALS['tmpl']->assign("drop_nav","no_drop"); //首页下拉菜单不输出
			$GLOBALS['tmpl']->assign("wrap_type","1"); //首页宽屏展示
			
			//获取首页公告
			$notice_list = get_notice(0,array(0,1));
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);	
			
			//获取热门团购分类
			$tuan_cate = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort asc");
			foreach($tuan_cate as $k=>$v)
			{
				$tuan_cate[$k]['url'] = url("index","tuan",array("cid"=>$v['id']));
			}
			$GLOBALS['tmpl']->assign("tuan_cate",$tuan_cate);
			
			//输出热门团购标签
			$tuan_tag = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id  order by dct.sort limit 15");
			foreach($tuan_tag as $k=>$v)
			{
				$tuan_tag[$k]['url'] = url("index","tuan",array("cid"=>$v['cate_id'],"tid"=>$v['id']));
			}
			$GLOBALS['tmpl']->assign("tuan_tag",$tuan_tag);
			
			
			//输出团购地区
			$tuan_area = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".intval($GLOBALS['city']['id'])." and pid > 0 order by sort limit 50");
			foreach($tuan_area as $k=>$v)
			{
				$tuan_area[$k]['url'] = url("index","tuan",array("aid"=>$v['pid'],"qid"=>$v['id']));
			}
			$GLOBALS['tmpl']->assign("tuan_area",$tuan_area);
			
			//输出首页推荐的分类
			$index_cates = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 and rec_youhui = 1 order by sort");
			foreach($index_cates as $k=>$v)
			{
				$index_cates[$k]['deal_cate_type_list'] = $GLOBALS['db']->getAll("select dct.* from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." and dct.is_recommend = 1 order by dct.sort");			
				require_once APP_ROOT_PATH."system/model/deal.php";
				$deal_result  = get_deal_list(8,array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id'],"cid"=>$v['id']),""," d.buy_type <> 1 and d.is_shop = 0 and d.is_recommend = 1 ");
				
				$index_cates[$k]['deal_list'] =	$deal_result['list'];
	
			}
			$GLOBALS['tmpl']->assign("index_cates",$index_cates);
			
			//输出首页商城推荐分类
			$index_mall_cates = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_delete = 0 and is_effect = 1 and recommend = 1 and pid = 0 order by sort");
			foreach($index_mall_cates as $k=>$v)
			{
				$index_mall_cates[$k]['deal_cate_type_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate  where pid = ".$v['id']." order by sort limit 8");
				require_once APP_ROOT_PATH."system/model/deal.php";
				$deal_result  = get_goods_list(8,array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id'],"cid"=>$v['id']),""," d.buy_type <> 1 and d.is_shop = 1 and d.is_recommend = 1 ");
				$index_mall_cates[$k]['deal_list'] =	$deal_result['list'];
					
			}
			$GLOBALS['tmpl']->assign("index_mall_cates",$index_mall_cates);
			
			//输出推荐门店			
			require_once APP_ROOT_PATH."system/model/supplier.php";
			$store_result = get_location_list(app_conf("INDEX_SUPPLIER_COUNT"),array("city_id"=>intval($GLOBALS['city']['id'])),""," is_recommend=1 AND is_effect = 1 "," is_verify desc,sort desc ");			
			$GLOBALS['tmpl']->assign("store_list",$store_result['list']);
			
			
			//输出首页推荐的优惠券
			require_once APP_ROOT_PATH."system/model/youhui.php";
			$youhui_result = get_youhui_list(20,array(YOUHUI_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"","y.is_recommend = 1");
			$GLOBALS['tmpl']->assign("youhui_list",$youhui_result['list']);
			
			
			//输出友情链接
			$links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1  order by sort desc");
			
			foreach($links as $kk=>$vv)
			{
				if(substr($vv['url'],0,7)=='http://')
				{
					$links[$kk]['url'] = str_replace("http://","",$vv['url']);
				}
			}
			
	
			$GLOBALS['tmpl']->assign("links",$links);
		}
		$GLOBALS['tmpl']->display("index.html",$cache_id);
	}
	
	
}
?>