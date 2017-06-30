<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class dc_searchModule extends MainBaseModule
{
	
	/**
	 * 搜索首页
	 * 输入：
	 *           
	 * 
	 * 
	 * 
	 */
	public function index()
	{

		$root = array();
		$root['page_title']='搜索';
		output($root);
	}
	
	

	/**
	 * 搜索商家和商品的提交接口
	 * 
	 * 输入：
	 * keyword：string 搜索关键词
	 *
	 * 输出：
	 * 
	 * location：array 商家列表
	 * lid_count：int 商家数量
	 * menu：array,商品列表
	 * menu_count：商品数量
	 * 
	 * 
	 */ 
	public function do_search(){
	
		$root=array();
		$keyword=strim($GLOBALS['request']['keyword']);
		if($keyword==''){
			output($root,0,'请输入关键词');
		}
		
		/*  搜索餐厅
		 */
		$location_info=$GLOBALS['db']->getAll("select id,name,preview,dc_buy_count from ".DB_PREFIX."supplier_location where name like '%".$keyword."%' and is_dc=1");
		require_once APP_ROOT_PATH."system/model/dc.php";
		foreach($location_info as $k=>$v){
			$location_info[$k]['url']=wap_url('index','dcbuy',array('lid'=>$v['id']));
			$location_info[$k]['preview']=get_abs_img_root(get_spec_image($v['preview'],180,135,1));
			
		}
		$root['location']=$location_info ? $location_info:array();
		$root['lid_count']=count($location_info);
	
		/*
		 * 搜索美食
		*/
	
		$menu_info=$GLOBALS['db']->getAll("select id,name,price,location_id from ".DB_PREFIX."dc_menu where name like '%".$keyword."%' and is_effect=1");
		foreach($menu_info as $k=>$v){
			$lid_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."supplier_location where id=".$v['location_id']." and is_dc=1");
	
			if($lid_info){
				$menu_info[$k]['lid_name']=$lid_info['name'];

				$menu_info[$k]['url']=wap_url('index','dcbuy',array('lid'=>$lid_info['id']));
			}else{
				unset($menu_info[$k]);
			}
		}
		$menu_info=array_values($menu_info);
		$root['menu']=$menu_info;
		$root['menu_count']=count($menu_info);
		
		output($root);
	}
	
}
?>

