<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
class settingModule extends BizBaseModule{
	function __construct(){
		parent::__construct();
		global_run();
		$this->check_auth();
	}
	
	/*
	 * 商家设置
	 */
	public function index(){
		/* 基本参数初始化 */
		init_app_page();
		
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$weishop_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
		if(!$weishop_info){
			showBizErr("商家信息不存在",0,url("biz","dealv#index"));
			exit;
		}
		$banner_images = unserialize($weishop_info['weishop_banner']);
		
		/* 数据 */
		$GLOBALS['tmpl']->assign("vo", $weishop_info); // 支持门店
		$GLOBALS['tmpl']->assign("banner_images", $banner_images); // 图库
		$GLOBALS['tmpl']->assign("ajax_url",url("biz","setting"));
		$GLOBALS['tmpl']->assign("page_title", "微店设置");
		$GLOBALS['tmpl']->display('pages/setting/edit.html');
	}
	
	/*
	 * 保存商家设置
	 */
	public function do_save(){
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		 
		$data['id'] = $supplier_id; // 所属商户
		$data['weishop_name'] = strim($_REQUEST['name']); // 名称
		
		//数据验证
		$this->check_setting_data($data);
		
		//微店LOGO
		$logo_img = strim($_REQUEST['logo']); // 缩略图
		$logo_img = replace_domain_to_public($logo_img);
		$data['weishop_logo'] = $logo_img;
		//微店banner
		$banner_images = $_REQUEST['banner_images'];
		foreach ($banner_images as $k=>$v){
			$cache_banner_images[] = replace_domain_to_public($v);
		}
		$data['weishop_banner'] = serialize($cache_banner_images);

		$GLOBALS['db']->autoExecute(DB_PREFIX . "supplier", $data, "UPDATE", " id=" . $supplier_id);
		$result['status'] = 1;
		$result['info'] = "修改成功";
		$result['jump'] = url("biz","setting#index");
		 
		ajax_return($result);
	}
	
	/**
	 * 表单验证
	 */
	private function check_setting_data($data){
		$id = intval($data['id']);
		 
		if(strim($data['weishop_name'])==''){
			$result['status'] = 0;
			$result['info'] = '微店名称不允许为空';
			ajax_return($result);
		}
		$conditions = " where weishop_name='".strim($data['weishop_name'])."'";
		$conditions .= " and id<>".$id;		 
	
		$sql = "select count(*) from ".DB_PREFIX."supplier ";
		/*查询是否有重复数据*/
		if($GLOBALS['db']->getOne($sql.$conditions)){
			$result['status'] = 0;
			$result['info'] = '微店名称已被使用';
			ajax_return($result);
		}
		return true;
	}
	
}