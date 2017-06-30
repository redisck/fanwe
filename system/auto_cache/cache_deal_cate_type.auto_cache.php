<?php
//所有生活服务分类的缓存
class cache_deal_cate_type_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("cate_id"=>$param['cate_id']); //重新定义缓存的有效参数，过滤非法参数		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['cache']->get($key);				
		if($cate_list === false)
		{		
			//验证参数有效性
			$param['cate_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_cate where id = ".intval($param['cate_id'])));
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$cate_list = $GLOBALS['cache']->get($key);
			if($cate_list!==false)return $cate_list;
			
			
			$cate_id = intval($param['cate_id']);
			
			if($cate_id>0)
			$cate_list_rs = $GLOBALS['db']->getAll("select dct.* from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$cate_id);
			else
			$cate_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type");
			foreach($cate_list_rs as $k=>$v)
			{
				$cate_list[$v['id']] = $v;
			}
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$cate_list);
		}
		return $cate_list;	
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