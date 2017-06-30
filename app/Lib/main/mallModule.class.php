<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class mallModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['id']);
		if (!$GLOBALS['tmpl']->is_cached('mall.html', $cache_id))
		{
			init_app_page();
			
			//获取商城公告
			$notice_list = get_notice(0,array(0,2));
			$GLOBALS['tmpl']->assign("notice_list",$notice_list);
			
			
			//输出首页推荐的分类
			$index_cates = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_delete = 0 and is_effect = 1 and recommend = 1 and pid = 0 order by sort");
			foreach($index_cates as $k=>$v)
			{
				$index_cates[$k]['deal_cate_type_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate  where pid = ".$v['id']." order by sort limit 8");
				require_once APP_ROOT_PATH."system/model/deal.php";
				$deal_result  = get_goods_list(8,array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id'],"cid"=>$v['id']),""," d.buy_type <> 1 and d.is_shop = 1 and d.is_recommend = 1 ");		
				$index_cates[$k]['deal_list'] =	$deal_result['list'];
			
			}
			$GLOBALS['tmpl']->assign("index_cates",$index_cates);
				
			
			
			$GLOBALS['tmpl']->assign("drop_nav","no_drop"); //首页下拉菜单不输出
			$GLOBALS['tmpl']->assign("wrap_type","1"); //首页宽屏展示
		}
		$GLOBALS['tmpl']->display("mall.html",$cache_id);
	}

	
	
}
?>