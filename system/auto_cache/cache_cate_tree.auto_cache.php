<?php
//分类树
class cache_cate_tree_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("type"=>$param['type']); //重新定义缓存的有效参数，过滤非法参数		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$nav_list = $GLOBALS['cache']->get($key);
		if($nav_list === false)
		{
			//验证参数有效性
			if($param['type']<0||$param['type']>5)$param['type'] = 0;
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$nav_list = $GLOBALS['cache']->get($key);
			if($nav_list!==false)return $nav_list;
			
 			//$param['type'] 0生活服务分类  1商城 2积分 3优惠券 4活动 5商家
			if($param['type']==0)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","tuan",array("cid"=>$v['id']));
					$sub_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 3");
					foreach($sub_nav as $kk=>$vv)
					{
						$sub_nav[$kk]['url'] = url("index","tuan",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}	
					$navs[$k]['sub_nav'] = $sub_nav;
					
					$pop_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 50");
					foreach($pop_nav as $kk=>$vv)
					{
						$pop_nav[$kk]['url'] = url("index","tuan",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}
					$navs[$k]['pop_nav'] = $pop_nav;
					
				}
			}
			elseif($param['type']==1)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_delete = 0 and is_effect = 1 and pid = 0 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","cate",array("cid"=>$v['id']));
					$sub_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where pid = ".$v['id']." and is_effect = 1 order by sort limit 3");
					foreach($sub_nav as $kk=>$vv)
					{
						$sub_nav[$kk]['url'] = url("index","cate",array("cid"=>$vv['id']));
					}
					$navs[$k]['sub_nav'] = $sub_nav;
						
					$pop_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate  where pid = ".$v['id']." and is_effect = 1 order by sort limit 50");
					foreach($pop_nav as $kk=>$vv)
					{
						$pop_nav[$kk]['url'] = url("index","cate",array("cid"=>$vv['id']));
					}
					$navs[$k]['pop_nav'] = $pop_nav;
						
				}
			}
			elseif($param['type']==2)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where is_delete = 0 and is_effect = 1 and pid = 0 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","scores",array("cid"=>$v['id']));
					$sub_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate where pid = ".$v['id']." order by sort limit 3");
					foreach($sub_nav as $kk=>$vv)
					{
						$sub_nav[$kk]['url'] = url("index","scores",array("cid"=>$vv['id']));
					}
					$navs[$k]['sub_nav'] = $sub_nav;
			
					$pop_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."shop_cate  where pid = ".$v['id']." order by sort limit 50");
					foreach($pop_nav as $kk=>$vv)
					{
						$pop_nav[$kk]['url'] = url("index","scores",array("cid"=>$vv['id']));
					}
					$navs[$k]['pop_nav'] = $pop_nav;
			
				}
			}
			elseif($param['type']==3)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","youhuis",array("cid"=>$v['id']));
					$sub_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 3");
					foreach($sub_nav as $kk=>$vv)
					{
						$sub_nav[$kk]['url'] = url("index","youhuis",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}	
					$navs[$k]['sub_nav'] = $sub_nav;
					
					$pop_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 50");
					foreach($pop_nav as $kk=>$vv)
					{
						$pop_nav[$kk]['url'] = url("index","youhuis",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}
					$navs[$k]['pop_nav'] = $pop_nav;
					
				}
			}
			elseif($param['type']==4)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_cate where is_effect = 1 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","events",array("cid"=>$v['id']));						
				}
			}
			elseif($param['type']==5)
			{
				$navs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort asc");
				foreach($navs as $k=>$v)
				{
					$navs[$k]['url'] = url("index","stores",array("cid"=>$v['id']));
					$sub_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 3");
					foreach($sub_nav as $kk=>$vv)
					{
						$sub_nav[$kk]['url'] = url("index","stores",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}
					$navs[$k]['sub_nav'] = $sub_nav;
						
					$pop_nav = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type as dct left join ".DB_PREFIX."deal_cate_type_link as dctl on dct.id = dctl.deal_cate_type_id where dctl.cate_id = ".$v['id']." order by dct.sort limit 50");
					foreach($pop_nav as $kk=>$vv)
					{
						$pop_nav[$kk]['url'] = url("index","stores",array("cid"=>$v['id'],"tid"=>$vv['id']));
					}
					$navs[$k]['pop_nav'] = $pop_nav;
						
				}
			}
			
			$nav_list = $navs;
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$nav_list);
		}
		return $nav_list;
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