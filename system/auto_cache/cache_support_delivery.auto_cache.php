<?php
//某个地区支持的配送方式列表
class cache_support_delivery_auto_cache extends auto_cache{
	public function load($param)
	{
		$delivery_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery where is_effect = 1 order by sort desc");
		return $delivery_list;
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