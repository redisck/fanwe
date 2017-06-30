<?php
//某分类的全文索引关键词
class shop_cate_key_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("cid"=>$param['cid']);
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$key_result = $GLOBALS['cache']->get($key);
		if($key_result === false)
		{
			$param['cid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."shop_cate where id = ".intval($param['cid'])));
			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$key_result = $GLOBALS['cache']->get($key);
			if($key_result!==false)return $key_result;
			
			$cate_id = $param['cid'];
			require_once APP_ROOT_PATH."system/utils/child.php";
			$ids_util = new child("shop_cate");
			$ids = $ids_util->getChildIds($cate_id);
			$ids[] = $cate_id;
			
			$deal_cate = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."shop_cate where id in (".implode(",", $ids).") and is_effect = 1 and is_delete = 0");
			foreach($deal_cate as $k=>$item)
			{
				$name_words = div_str($item['name']);
				foreach($name_words as $kk=>$vv)
				{
					$kw[] = str_to_unicode_string($vv);
				}				
			}
			$key_result = implode(" ",$kw);
			
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$key_result);
		}
		return $key_result;
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