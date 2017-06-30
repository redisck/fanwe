<?php
require_once APP_ROOT_PATH."system/model/dc.php";
class DcRsItemTimeAction extends CommonAction{
	
	
	
	public function index()
	{
		$id = intval($_REQUEST['id']);
		$condition['id'] = $id;	
		
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$list = M("DcRsItemTime")->where("item_id=".$id)->findAll();
	
	
	//print_r($list);die;
		$this->assign("list",$list);
		$this->assign("id",$id);
		$this->assign("vo",$vo);
		$this->display ();
	}
	
	
	
	   public function time_set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M("DcRsItemTime")->where("id=".$id)->getField("rs_time");
		$c_is_effect = M("DcRsItemTime")->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M("DcRsItemTime")->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		 
		 
		 
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	
	
	public function add()
	{	
		$id = intval($_REQUEST['id']);
		$vo = M("DcRsItem")->where("id=".$id)->find();
		
		$this->assign("vo",$vo);	
		$this->display();
	}
	
	
	
	public function insert() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$item_id=$data['item_id'];
		$item_info = M("DcRsItem")->where("id=".$item_id)->find();
		//开始验证有效性
		
		//$this->assign("jumpUrl",u(MODULE_NAME."/add",array("id"=>$data['item_id'])));
		$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])[:](0?[0-9]|[1-5][0-9])?$/";
		

		$data['supplier_id']=$item_info['supplier_id'];
		$data['location_id']=$item_info['location_id'];
		if(!preg_match($patten,$data['rs_time']))
		{
			$this->error(L("NOT_TIME_TYPE"));
		}	
		
		if(!check_empty($data['total_count']))
		{
			$data['total_count']=0;
		}	
		
		// 更新数据
		$log_info = $data['rs_time'];
		
		
		$list=M(MODULE_NAME)->add($data);
			
		if (false !== $list) {
			//成功提示
			//save_log($log_info.L("INSERT_SUCCESS"),1);
			 
			 
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			//save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	
	
	
		public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['title'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
	
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					 
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	
	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		
		$vo = M(MODULE_NAME)->where($condition)->find();

		$this->assign ( 'vo', $vo );
		
		$this->display ();
	}
	
	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("id");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		// 更新数据
		
		$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])[:](0?[0-9]|[1-5][0-9])?$/";
		
		if(!preg_match($patten,$data['rs_time']))
		{
			$this->error(L("NOT_TIME_TYPE"));
		}	
	
		$list=M(MODULE_NAME)->save ($data);
		
		if (false !== $list) {
		
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			 

			M("SupplierLocation")->setField("dp_group_point","");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	
}
?>
