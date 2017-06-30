<?php
//团购模块的筛选切换单菜
class dc_mapi_filter_res_nav_cache_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("city_id"=>$param['city_id'],"cid"=>$param['cid'],"aid"=>$param['aid'],"qid"=>$param['qid']); //重新定义缓存的有效参数，过滤非法参数		
		$key = $this->build_key(__CLASS__,$param);
		//传入参数 city_id(城市)  cid(分类ID) aid(区域ID) qid(商圈ID) 
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$result = $GLOBALS['cache']->get($key);
		if($result===false||IS_DEBUG)
		{
			//验证参数有效性
			$param['city_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_city where id = ".intval($param['city_id'])));
			$param['cid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_cate where id = ".intval($param['cid'])));
			$param['aid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."area where id = ".intval($param['aid'])));
			$param['qid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."area where id = ".intval($param['qid'])));			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$result = $GLOBALS['cache']->get($key);
			if($result!==false)return $result;
			
			
			$city_id = intval($param['city_id']);
			$deal_cate_id = intval($param['cid']);
			$deal_area_id = intval($param['aid']);
			$deal_quan_id = intval($param['qid']);	


			$url_param = array("cid"=>$param['cid'],"aid"=>$param['aid'],"qid"=>$param['qid']);
	
			$bquan_list_res = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=0 AND city_id=".$city_id." ORDER BY `sort` asc ");

			$bquan_list = array();
			$all_current = 0;
			if($deal_quan_id == 0)
				$all_current = 1;
			$tmp_url_param = $url_param;
			unset($tmp_url_param['aid']);
			unset($tmp_url_param['qid']);
			$bquan_list[] = array("url"=>url("index","dcres",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);

			$squan_list_res = $GLOBALS['db']->getAll("select id,name,pid from ".DB_PREFIX."area WHERE pid >0 AND city_id=".$city_id." ORDER BY `sort` asc ");	
			$squan_arr=array();
			foreach($squan_list_res as $kk=>$vv){
			
				$squan_arr[$vv['pid']][]=$vv;
			}
			
			
				
			foreach($bquan_list_res as $k=>$v)
			{
					if($deal_area_id==$v['id'])
						$v['current'] = 1;
					
					$tmp_url_param = $url_param;
					$tmp_url_param['aid'] = $v['id'];
					unset($tmp_url_param['qid']);
					$durl = url("index","dcres",$tmp_url_param);
					$v['url']=$durl;
					$v['quan_sub']=$squan_arr[$v['id']];
										
					$bquan_list[] = $v;
			
			}

			$result['bquan_list'] = $bquan_list;
			
		/*	
			//当前城市的二级商圈
				
					$squan_list_res = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=".$deal_area_id." AND city_id=".$city_id." ORDER BY `sort` asc ");					
					if($squan_list_res)
					{
						$squan_list = array();
						$all_current = 0;
						if($deal_quan_id == 0)
							$all_current = 1;
					
						$tmp_url_param = $url_param;
						unset($tmp_url_param['aid']);
						unset($tmp_url_param['qid']);
						$squan_list[] = array("url"=>url("index","dcres",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);
					}
					foreach($squan_list_res as $k=>$v){
						if($deal_quan_id==$v['id'])
							$v['current'] = 1;
						$tmp_url_param = $url_param;
						$tmp_url_param['qid'] = $v['id'];
						$durl = url("index","dcres",$tmp_url_param);
						$v['url']=$durl;
						$squan_list[] = $v;
				
					}
					
					
			
			$result['squan_list'] = $squan_list;
			*/
			
			//大类
			$bcate_list_res = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate where is_effect = 1 order by sort asc");
			$bcate_list = array();
			$all_current = 0;
			if($deal_cate_id == 0)
				$all_current = 1;
				
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			$bcate_list[] = array("url"=>url("index","dcres",$tmp_url_param),"name"=>$GLOBALS['lang']['ALL'],"current"=>$all_current);
			foreach($bcate_list_res as $k=>$v)
			{		
						if($deal_cate_id==$v['id'])
						$v['current'] = 1;
						$tmp_url_param = $url_param;
						$tmp_url_param['cid'] = $v['id'];
						$v['url'] = url("index","dcres",$tmp_url_param);
						
						$bcate_list[] = $v;
						
			}
			$result['bcate_list'] = $bcate_list;

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$result);
		}	
		return $result;
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