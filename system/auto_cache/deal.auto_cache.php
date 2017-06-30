<?php
//加载商品信息的模块
class deal_auto_cache extends auto_cache{
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
		$deal_key = strim($param['id']);
		static $deal;
		if(!$deal[$deal_key])
		{
			if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
			{
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$deal_info = $GLOBALS['cache']->get($key);
			}
			else 
			{
				$deal_info = false;
			}
			if($deal_info===false)
			{
				$deal_info =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where (id = '".$deal_key."' or (uname= '".$deal_key."' and uname <> ''))");
				if($deal_info['uname'])
					$param['id'] = $deal_info['uname'];
				else
					$param['id'] = intval($deal_info['id']);
				
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$deal_info = $GLOBALS['cache']->get($key);
				if($deal_info!==false)return $deal_info;

				$deal_key = $param['id'];	
				$deal_info =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where (id = '".$deal_key."' or uname= '".$deal_key."') and is_delete = 0");				
				if($deal_info)
				{
					
					if($deal_info['uname']!='')
						$durl = url("index","deal#".$deal_info['uname']);
					else
						$durl = url("index","deal#".$deal_info['id']);
					
					$deal_info['url'] = $durl;
					if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
					{
						$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
						$GLOBALS['cache']->set($key,$deal_info,30);
					}
				}				
			}
			$deal[$deal_key] = $deal_info;
			
		}

		return $deal[$deal_key];	
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