<?php
//加载活动信息的模块
class event_auto_cache extends auto_cache{
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
		$event_id = intval($param['id']);
		static $events;
		if(!$events[$event_id])
		{
			if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
			{
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$event = $GLOBALS['cache']->get($key);
			}
			else
			{
				$event = false;
			}
			if($event===false)
			{			
				
				$param['id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."event where id = ".$event_id));
				
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$event = $GLOBALS['cache']->get($key);
				if($event!==false)return $event;
				
				$event_id = $param['id'];
				$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = '".$event_id."'");	
				if($event)
				{				
					$event['url'] = url("index","event#".$event['id']);
					if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
					{
						$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
						$GLOBALS['cache']->set($key,$event,30);
					}
				}
			}
			$events[$event_id] = $event;
		}
		return $events[$event_id];	
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