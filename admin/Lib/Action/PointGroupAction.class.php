<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +-----------------------------------------
class PointGroupAction extends CommonAction
{
	
	public function add()
	{
		$deal_cates = M("DealCate")->where("is_delete=0")->findAll();
		$this->assign("deal_cates",$deal_cates);
	
		$shop_cates = M("ShopCate")->where("is_delete=0 and pid = 0")->findAll();
		$this->assign("shop_cates",$shop_cates);
	
		$event_cates = M("EventCate")->findAll();
		$this->assign("event_cates",$event_cates);
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$data = M("PointGroup")->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("POINTNAME_EMPTY_TIP"));
		}
		
		if(M(MODULE_NAME)->where("`name`='".$data['name']."'")->count()>0){
			$this->error(L("HAD_POINTGROUP"));
		}	

		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $list;
					M("PointGroupLink")->add($link_data);
				}
			}
			
			foreach($_REQUEST['scate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $list;
					M("PointGroupSlink")->add($link_data);
				}
			}
				
			foreach($_REQUEST['ecate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $list;
					M("PointGroupElink")->add($link_data);
				}
			}
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
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		
		$deal_cates = M("DealCate")->where("is_delete=0 and is_effect=1")->findAll();
		foreach($deal_cates as $k=>$v)
		{
			$deal_cates[$k]['checked'] = M("PointGroupLink")->where("category_id=".$v['id']." and point_group_id = ".$vo['id'])->count();
		}
		$this->assign("deal_cates",$deal_cates);
		
		$shop_cates = M("ShopCate")->where("is_delete=0")->findAll();
		foreach($shop_cates as $k=>$v)
		{
			$shop_cates[$k]['checked'] = M("PointGroupSlink")->where("category_id=".$v['id']." and point_group_id = ".$vo['id'])->count();
		}
		$this->assign("shop_cates",$shop_cates);
		
		$event_cates = M("EventCate")->findAll();
		foreach($event_cates as $k=>$v)
		{
			$event_cates[$k]['checked'] = M("PointGroupElink")->where("category_id=".$v['id']." and point_group_id = ".$vo['id'])->count();
		}
		$this->assign("event_cates",$event_cates);
		
		$this->display ();
	}
	
	public function update()
	{
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit"));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("POINTNAME_EMPTY_TIP"));
		}

		if(M(MODULE_NAME)->where("id<>".$data['id']." and `name`='".$data['name']."'")->count()>0){
			$this->error(L("HAD_POINTGROUP"));
		}
		
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		$log_info = $data['name'];
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			
			M("PointGroupLink")->where("point_group_id=".$data['id'])->delete();
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $data['id'];
					M("PointGroupLink")->add($link_data);
				}
			}
				
			M("PointGroupSlink")->where("point_group_id=".$data['id'])->delete();
			foreach($_REQUEST['scate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $data['id'];
					M("PointGroupSlink")->add($link_data);
				}
			}
			
			M("PointGroupElink")->where("point_group_id=".$data['id'])->delete();
			foreach($_REQUEST['ecate_id'] as $cate_id)
			{
				if(intval($cate_id)>0)
				{
					$link_data=  array();
					$link_data['category_id'] = intval($cate_id);
					$link_data['point_group_id'] = $data['id'];
					M("PointGroupElink")->add($link_data);
				}
			}
			
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	public function foreverdelete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$name=$this->getActionName();
			$model = D($name);
			$pk = $model->getPk ();
			$ids =  explode ( ',', $id );
			$condition = array ($pk => array ('in', $ids) );
			$link_condition = array ("point_group_id" => array ('in', $ids ) );
			
			if(false !== $model->where ( $condition )->delete ())
			{
				M("PointGroupLink")->where($link_condition)->delete();
				M("PointGroupSlink")->where($link_condition)->delete();
				M("PointGroupElink")->where($link_condition)->delete();
							
				M("SupplierLocationDpPointResult")->where(array ("group_id" => array ('in', $ids ) ))->delete();		
				save_log($ids.l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
			}
			else
			{
				save_log($ids.l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
			}
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
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
		$this->success(l("SORT_SUCCESS"),1);
	}	
}
?>