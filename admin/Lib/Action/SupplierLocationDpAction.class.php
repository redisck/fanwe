<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +-----------------------------------------

class SupplierLocationDpAction extends CommonAction
{
	public function index() {
		

		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		
		if(strim($_REQUEST['keyword'])!='')
		{
			$where['content'] = array('like','%'.strim($_REQUEST['keyword']).'%');		
			$where['title'] = array('like','%'.strim($_REQUEST['keyword']).'%');		
			$where['_logic'] = 'or';
			$map['_complex'] = $where;			
		}

		if(strim($_REQUEST['type'])=='all'||strim($_REQUEST['type'])=='')
		{
			unset($map['deal_id']);
			unset($map['youhui_id']);
			unset($map['event_id']);
		}		
	
		if(strim($_REQUEST['type'])=='dealdp')
		{
			unset($map['deal_id']);
			$map['deal_id'] = array("neq",0);
		}
		if(strim($_REQUEST['type'])=='youhuidp')
		{
			unset($map['youhui_id']);
			$map['youhui_id'] = array("neq",0);
		}	
		if(strim($_REQUEST['type'])=='eventdp')
		{
			unset($map['event_id']);
			$map['event_id'] = array("neq",0);
		}	
		if(intval($_REQUEST['wait_reply'])==1)
		{
			$map['reply_content'] = array("eq","");
		}	
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ("SupplierLocationDp");
		if (! empty ( $model )) {
			$this->_list($model,$map);
		}
		
		M("SupplierLocation")->where("id=".intval($_REQUEST['supplier_location_id']))->setField("new_dp_count_time",NOW_TIME);
		$new_count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".intval($_REQUEST['supplier_location_id'])." and create_time > ".NOW_TIME)); 
		
		M("SupplierLocation")->where("id=".intval($_REQUEST['supplier_location_id']))->setField("new_dp_count",$new_count);
		
		$this->display ();
		return;
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
			$ids = explode ( ',', $id );
			$condition = array ($pk => array ('in', $ids ) );
			$condition_link = array ("dp_id" => array ('in', $ids ) );
			$dp_list = $model->where($condition)->findAll();
			
			if(M("SupplierLocationDpReply")->where($condition_link)->count()>0)
			{
				$this->error ("请先清空点评回应",$ajax);
			}
			
			if(false !== $model->where ( $condition )->delete ())
			{
				M("SupplierLocationDpImages")->where($condition_link)->delete();
				M("SupplierLocationDpPointResult")->where($condition_link)->delete();
				M("SupplierLocationDpTagResult")->where($condition_link)->delete();
				M("DealDpPointResult")->where($condition_link)->delete();
				M("DealDpTagResult")->where($condition_link)->delete();
				M("YouhuiDpPointResult")->where($condition_link)->delete();
				M("YouhuiDpTagResult")->where($condition_link)->delete();
				M("EventDpPointResult")->where($condition_link)->delete();
				M("EventDpTagResult")->where($condition_link)->delete();
				foreach($dp_list as $k=>$v)
				{
					if($v['status']==1)
					{
						$merchant_info = M("SupplierLocation")->getById($v['supplier_location_id']);
						syn_supplier_locationcount($merchant_info);
						syn_deal_review_count($v['deal_id']);
						syn_youhui_review_count($v['youhui_id']);
						syn_event_review_count($v['event_id']);
						cache_store_point($merchant_info['id']);
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count - 1 where id = ".intval($v['user_id']));
				}
					
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
	
	
	function edit() {
		
		$name = $this->getActionName();
		$model = D($name);
		
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById($id);//print_r($vo);
		$vo = sys_get_dp_detail($vo);//print_r($vo);
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	
	
	public function removePhoto()
	{
		$ajax = intval($_REQUEST['ajax']);
		$photo_id = intval($_REQUEST['img_id']);
		$dp_id = intval($_REQUEST['id']);		
		$list =D("SupplierLocationDpImages")->where("id=".$photo_id)->delete();						
		if ($list!==false) {
			$Model = new Model();
			$img_array=$Model->query("select image from ".DB_PREFIX."supplier_location_dp_images where dp_id=".$dp_id);
			if(count($img_array)>0){
				$is_img = 1;
			}else{
				$is_img = 0;
			}
			$img_cache=array();
			foreach ($img_array as $k=>$v){
				$img_cache[]=$v['image'];
			}		
			$img_cache= serialize($img_cache);			
			$Model->query("update ".DB_PREFIX."supplier_location_dp set images_cache = '".$img_cache."' ,is_img=".$is_img." where id = ".$dp_id);	
			$info = $dp_id."点评".$photo_id;
			save_log($info.l("删除图片成功"),1); 			 
			$this->success (l("DELETE_SUCCESS"),$ajax);			
		}else{
			save_log($info.l("删除图片失败"),0);
			$this->error (l("DELETE_FAILED"),$ajax);
		}
       

	}
	
	function update() {
		//B('FilterString');
		$name=$this->getActionName();
		$model = D ( $name );
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		$id = $data[$model->getPk()];

		if($data['reply_content']!=""&&$data['reply_time']=="")
			$data['reply_time'] = NOW_TIME;
		// 更新数据
		$list=$model->save ($data);
		$dp = $model->getById($id);
		
		if (false !== $list) {
			//成功提示
			$group_points = $_REQUEST['group_point'];
			foreach($group_points as $group_id=>$point)
			{
				$model->query("update ".DB_PREFIX."supplier_location_dp_point_result set point = ".$point." where group_id = ".$group_id." and dp_id = ".$id." and supplier_location_id = ".$dp['supplier_location_id']);
				M()->query("update ".DB_PREFIX."deal_dp_point_result set point = ".$point." where group_id = ".$group_id." and dp_id = ".$id." and deal_id = ".$dp['deal_id']);
				M()->query("update ".DB_PREFIX."youhui_dp_point_result set point = ".$point." where group_id = ".$group_id." and dp_id = ".$id." and youhui_id = ".$dp['youhui_id']);
				M()->query("update ".DB_PREFIX."event_dp_point_result set point = ".$point." where group_id = ".$group_id." and dp_id = ".$id." and event_id = ".$dp['event_id']);
			}
			
			$group_tags = $_REQUEST['group_tag'];
			M()->query("update ".DB_PREFIX."supplier_location_dp set tags_match = '',tags_match_row='' where id = ".$id);
			foreach($group_tags as $group_id=>$tags)
			{
				$model->query("update ".DB_PREFIX."supplier_location_dp_tag_result set tags = '".$tags."' where group_id = ".$group_id." and dp_id = ".$id." and supplier_location_id = ".$dp['supplier_location_id']);
				M()->query("update ".DB_PREFIX."deal_dp_tag_result set tags = '".$tags."' where group_id = ".$group_id." and dp_id = ".$id." and deal_id = ".$dp['deal_id']);
				M()->query("update ".DB_PREFIX."youhui_dp_tag_result set tags = '".$tags."' where group_id = ".$group_id." and dp_id = ".$id." and youhui_id = ".$dp['youhui_id']);
				M()->query("update ".DB_PREFIX."event_dp_tag_result set tags = '".$tags."' where group_id = ".$group_id." and dp_id = ".$id." and event_id = ".$dp['event_id']);		

				insert_match_item($tags,"supplier_location_dp",$id,"tags_match"); //更新点评的索引
			}
		
			
			$count = M("SupplierLocationDpReply")->where("dp_id=".$id)->count();
			$model->where("id=".$id)->setField("reply_count",$count);
			$supplier_info['id'] = $dp['supplier_location_id'];
			syn_supplier_locationcount($supplier_info);		
			save_log($dp.L("UPDATE_SUCCESS"),1);
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('UPDATE_SUCCESS'));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($dp.L("UPDATE_FAILED").$dbErr,0);
			$this->error (L('EDIT_ERROR'));
		}
	}
	


}
function getUNAME($id)
{
	return 	M("User")->where("id=".$id)->getField("user_name");
}
function getMerchantName($id)
{
	return M("SupplierLocation")->where("id=".$id)->getField("name");
}
function getIsImg($tag)
{
	if($tag)return "是";
	else
	return "否";
}
?>