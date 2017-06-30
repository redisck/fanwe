<?php
//商城分类
class cache_shop_cate_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("pid"=>$param['pid'],"all"=>$param['all']);
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_cache = $GLOBALS['cache']->get($key);
		if($cate_cache===false)
		{
			$param['pid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."shop_cate where id = ".intval($param['pid'])));
			$param['all'] = intval($param['all']);
			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$cate_cache = $GLOBALS['cache']->get($key);
			if($cate_cache!==false)return $cate_cache;
			
			if($param['all']==1)
			{
				$cate_cache_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_effect = 1 and is_delete = 0");
			}
			elseif($param['pid']==0)
			{
				$cate_cache_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_effect = 1 and  pid =0 and is_delete = 0");
			}
			else
			{
				$pid = $param['pid'];
				$cate_cache_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_effect = 1 and is_delete = 0 and pid = ".$pid);
					
			}
			foreach($cate_cache_res as $k=>$v)
			{
				$cate_cache[$v['id']]['url'] = url("index","cate",array("cid"=>$v['id']));
				$cate_cache[$v['id']]['score_url'] = url("index","scores",array("cid"=>$v['id']));
				$cate_cache[$v['id']]['name'] = $v['name'];
				$cate_cache[$v['id']]['id'] = $v['id'];		
				$cate_cache[$v['id']]['pid'] = $v['pid'];
			}
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$cate_cache);
		}	
		return $cate_cache;
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