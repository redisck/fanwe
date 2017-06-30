<?php
//团购模块的筛选切换单菜
class dc_filter_dc_nav_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("cid"=>$param['cid']); //重新定义缓存的有效参数，过滤非法参数		
		$key = $this->build_key(__CLASS__,$param);
		//传入参数 city_id(城市)  cid(分类ID) tid(子分类ID) aid(区域ID) qid(商圈ID) 
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$result = $GLOBALS['cache']->get($key);
		if($result===false||IS_DEBUG)
		{
			//验证参数有效性

			$param['cid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_cate where id = ".intval($param['cid'])));
		
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$result = $GLOBALS['cache']->get($key);
			if($result!==false)return $result;
			
			

			$deal_cate_id = intval($param['cid']);
		
			//大类
			$bcate_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate where is_effect = 1 order by sort asc");
			$bcate_list = array();
			$all_current = 0;
			if($deal_cate_id == 0)
				$all_current = 1;
				
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			$bcate_list[] = array("url"=>url("index","dc",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);
			foreach($bcate_list_res as $k=>$v)
			{		
						if($deal_cate_id==$v['id'])
						$v['current'] = 1;
						$tmp_url_param = $url_param;
						$tmp_url_param['cid'] = $v['id'];
						$v['url'] = url("index","dc",$tmp_url_param);
						
						$bcate_list[] = $v;
						
			}
			$result['bcate_list'] = $bcate_list;

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$result);
		}	
		return $result;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->rm($key);

	}
	public function clear_all()
	{
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->clear();		
	}
}
?>