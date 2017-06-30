<?php
// +----------------------------------------------------------------------
// | Fanwe 方维订餐小秘书商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require_once APP_ROOT_PATH."system/model/dc.php";
class DcMenuAction extends CommonAction{
	public function index()
	{
		$map = $this->_search ();
		
		if(trim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
				//列表过滤器，生成查询Map对象
		$supplier_location_id=intval($_REQUEST['supplier_location_id']);
		//追加默认参数
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));
		
		if($supplier_location_id){
		$map['location_id'] = $supplier_location_id;
		}
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		 $name=$this->getActionName();

		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		
		if($supplier_location_id)
		{
			$supplier_location_info = M("SupplierLocation")->where('is_effect=1 and id='.$supplier_location_id)->find();
			$this->assign("supplier_location",$supplier_location_info);
		}
		
		$this->display ();
		return;
	}
	public function add()
	{
		$supplier_location_id=intval($_REQUEST['supplier_location_id']);

	
		if($supplier_location_id > 0)
		{

			$location_info=M("SupplierLocation")->where('is_effect=1 and id='.$supplier_location_id)->find();
			$this->assign("location_info",$location_info);
	
			
		}
		$menu_cate_type=$this->fetch('dc_menu_cate_type');
		$this->assign ( 'menu_cate_type', $menu_cate_type );
		$tag_list=M("DcMenuCate")->where('is_effect=1')->findAll();
		$this->assign("tag_list",$tag_list);
		$cate_list = M("DcSupplierMenuCate")->where('is_effect=1 and location_id='.$supplier_location_id)->findAll();
		$this->assign("cate_list",$cate_list);
		
		$this->display();
	}
	
	public function insert() {

		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		if(count($data['tags'])>0){
			$data['tags']=implode(',',$data['tags']);
		}	
		$location_id=intval($_REQUEST['location_id']);
		//开始验证有效性
		if($location_id>0)
			$this->assign("jumpUrl",u(MODULE_NAME."/add",array("supplier_location_id" => $location_id)));
		else
			$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error("菜单名称为空!");
		}
		if(!$data['location_id'])
		{
			$this->error("门店名称为空!");
		}						

		// 更新数据
		$log_info = $data['name'];
		
		$data['xpoint']=M('SupplierLocation')->where('id='.$data['location_id'])->getField('xpoint');
		$data['ypoint']=M('SupplierLocation')->where('id='.$data['location_id'])->getField('ypoint');
		$list=M(MODULE_NAME)->add($data);

		if (false !== $list) {

			syn_supplier_location_menu_match($list);
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$supplier_location_id = intval($_REQUEST ['supplier_location_id']);
		$this->assign("supplier_location_id",$supplier_location_id);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['price']=round($vo['price'],2);
		$location_info = M("SupplierLocation")->field('id,supplier_id')->where("id=".$vo['location_id'])->find();
		$this->assign("location_info",$location_info);
		$this->assign ( 'vo', $vo );
		$cate_list = M("DcSupplierMenuCate")->where('is_effect=1 and location_id='.$vo['location_id'])->findAll();
		$this->assign("cate_list",$cate_list);
		$tag_list=M("DcMenuCate")->where('is_effect=1')->findAll();
		$tagarr=explode(',',$vo['tags']);
		foreach($tag_list as $k=>$tag_row ){
			
			if(in_array($tag_row['id'],$tagarr)){
				
				$tag_list[$k]['checked']=1;
			}
			
		}
		$menu_cate_type=$this->fetch('dc_menu_cate_type');
		$this->assign ( 'menu_cate_type', $menu_cate_type );
		$this->assign("tag_list",$tag_list);
		$this->display ();
	}
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$data['tags']=implode(',',$data['tags']);
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		$location_id=intval($_REQUEST['location_id']);
		//开始验证有效性
		if($location_id>0)
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'],"supplier_location_id" => $location_id)));
		else
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		
		if(!check_empty($data['name']))
		{
			$this->error("菜单名称为空!");
		}
		if(!$data['location_id'])
		{
			$this->error("门店名称为空!");
		}	

		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		if (false !== $list) {
			
			syn_supplier_location_menu_match($data['id']);
			//成功提示

			save_log($log_info.L("UPDATE_SUCCESS"),1);
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
				$this->success (l("DELETE_SUCCESS"),$ajax);
			} else {
				save_log($info.l("DELETE_FAILED"),0);
				$this->error (l("DELETE_FAILED"),$ajax);
			}
		} else {
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}

	
	
}
?>