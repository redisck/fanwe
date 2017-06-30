<?php
//分类树
class cache_user_level_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array();
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$user_level = $GLOBALS['cache']->get($key);
		if($user_level === false)
		{
			$user_level_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_level order by point asc");
			$user_level = array();
			foreach($user_level_data as $k=>$v)
			{
				$v['level'] = $k+1;
				$user_level[$v['id']] = $v;
			}
			
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$user_level);
		}
		return $user_level;
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