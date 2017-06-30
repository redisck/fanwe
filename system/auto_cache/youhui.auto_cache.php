<?php
//加载优惠券信息的模块
class youhui_auto_cache extends auto_cache{
	public function load($param)
	{
// 		$key = $this->build_key(__CLASS__,$param);
// 		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
// 		$deal = $GLOBALS['cache']->get($key);				
// 		if($deal === false)
// 		{		
// 			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($param['id']));
// 			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
// 			$GLOBALS['cache']->set($key,$deal);
// 		}
		$param = array("id"=>$param['id']);
		$youhui_id = intval($param['id']);
		static $youhuis;
		if(!$youhuis[$youhui_id])
		{
			if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
			{
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$youhui = $GLOBALS['cache']->get($key);
			}
			else
			{
				$youhui = false;
			}
			if($youhui===false)
			{
				$param['id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."youhui where id = ".$youhui_id));
				
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$youhui = $GLOBALS['cache']->get($key);
				if($youhui!==false)return $youhui;
				
				$youhui_id = $param['id'];
				
				$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = '".$youhui_id."'");
				if($youhui)
				{
					$youhui['url'] = url("index","youhui#".$youhui['id']);
					if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
					{
						$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
						$GLOBALS['cache']->set($key,$youhui,30);
					}
				}
			}			
			$youhuis[$youhui_id] = $youhui;
		}
		return $youhuis[$youhui_id];	
	}
	public function rm($param)
	{
		if($GLOBALS['distribution_cfg']['CACHE_TYPE']=="File")return false;
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->rm($key);
	}
	public function clear_all()
	{
		if($GLOBALS['distribution_cfg']['CACHE_TYPE']=="File")return false;
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->clear();
	}
}
?>