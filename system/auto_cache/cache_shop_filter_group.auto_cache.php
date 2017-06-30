<?php
//商城分类筛选组
class cache_shop_filter_group_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("cid"=>$param['cid']);
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$filter_group = $GLOBALS['cache']->get($key);
		if($filter_group===false)
		{
			$param['cid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."shop_cate where id = ".intval($param['cid'])));
			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$filter_group = $GLOBALS['cache']->get($key);
			if($filter_group!==false)return $filter_group;
			
			$cate_id = $param['cid'];	
			$filter_group = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."filter_group where is_effect = 1 and (cate_id = ".$cate_id." or cate_id = ".$cate_id." ) order by sort  ");
			
			foreach($filter_group as $k=>$v)
			{
				$filter_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."filter where filter_group_id = ".$v['id']." limit 30");						
				$filter_group[$k]['filter_list'] = $filter_list;
			}

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$filter_group);
		}	
		return $filter_group;
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