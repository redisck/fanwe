<?php
//用户关注的用户
class cache_focus_user_auto_cache extends auto_cache{
	public function load($param)
	{		
		$param = array("uid"=>$param['uid'],"count"=>$param['count']); //重新定义缓存的有效参数，过滤非法参数
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$focus_user = $GLOBALS['cache']->get($key);	
		if($focus_user === false || IS_DEBUG)
		{		
			$param['uid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($param['uid'])));
			if($param['count']<=0||$param['count']>30)
			$param['count'] = 30;
			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$focus_user = $GLOBALS['cache']->get($key);
			if($focus_user!==false)return $focus_user;
			
			$uid = $param['uid'];
			$count = intval($param['count'])?intval($param['count']):30;
			if($uid>0)
			$focus_user = $GLOBALS['db']->getAll("select u.id as id ,u.user_name as user_name,u.topic_count from ".DB_PREFIX."user_focus uf left join ".DB_PREFIX."user u on u.id=uf.focused_user_id where uf.focus_user_id = ".$uid." order by u.topic_count desc limit 0,".$count);
			foreach($focus_user as $k=>$v)
			{
				$focus_user[$k] = $v;
			}

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$focus_user);
		}
		return $focus_user;	
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