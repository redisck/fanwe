<?php
// +----------------------------------------------------------------------
// | Fanwe 方维订餐小秘书商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require_once APP_ROOT_PATH."system/model/dc.php";
class DcCateAction extends CommonAction{
	public function index()
	{

		$condition['is_delete'] = 0;
		$this->assign("default_map",$condition);
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();

		//追加默认参数
		/*
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));
		*/
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		
		$name=$this->getActionName();
		$model = D ($name);
		
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$list = $this->get("list");
		
		
	/*	
		$result = array();
		$row = 0;
		foreach($list as $k=>$v)
		{
		
		if($v['supplier_location_id']!=$_REQUEST['supplier_location_id']){
			unset($list[$k]);
			continue;
		}
			$v['level'] = -1;
			$v['name'] = $v['name'];
			$result[$row] = $v;
			$row++;
			$sub_cate = M(MODULE_NAME)->where(array("id"=>array("in",D(MODULE_NAME)->getChildIds($v['id'])),'is_delete'=>0))->findAll();

			$sub_cate = D(MODULE_NAME)->toFormatTree($sub_cate,'name');

			foreach($sub_cate as $kk=>$vv)
			{
				$vv['name']	=	$vv['title_show'];
				$result[$row] = $vv;
				$row++;
			}
		}
		//dump($result);exit;
		$this->assign("list",$result);
		*/
		$this->assign("supplier_location_id",$_REQUEST['supplier_location_id']);
		$this->display ();
		return;
	}
	
	
	public function add()
	{
		$this->assign("newsort",M(MODULE_NAME)->where("is_delete=0")->max("sort")+1);
		$this->assign('supplier_location_id',$_REQUEST['supplier_location_id']);
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEALCATE_NAME_EMPTY_TIP"));
		}	

		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$supplier_l_info['id'] =  $data['supplier_location_id'];
			syn_supplier_locationcount($supplier_l_info);
			clear_auto_cache("cache_supplier_location_menu_cate");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$location_info=M('SupplierLocation')->where('id='.$vo['location_id'])->find();
		$supplier_info=M('Supplier')->where('id='.$vo['supplier_id'])->find();
		$this->assign ( 'vo', $vo );
		$this->assign ( 'location_info', $location_info );
		$this->assign ( 'supplier_info', $supplier_info );
		$this->display ();
	}

	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("title");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			clear_auto_cache("cache_dc_cate");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}

	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {

				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M("DcCateSupplierLocationLink")->where(array ('dc_cate_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error ("餐厅分类下有所属餐厅",$ajax);
				}
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$supplier_l_info['id'] =  $data['supplier_location_id'];
					syn_supplier_locationcount($supplier_l_info);
					clear_auto_cache("cache_dc_cate");
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}

	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		clear_auto_cache("cache_dc_cate");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		clear_auto_cache("cache_dc_cate");
		$this->success(l("SORT_SUCCESS"),1);
	}
}
?>