<?php

require_once APP_ROOT_PATH."system/model/dc.php";
class DcRsItemAction extends CommonAction{
	
	
	
	public function index()
	{
		$id = intval($_REQUEST['id']);
		$condition['id'] = $id;	
		
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$list = M("DcRsItem")->where("location_id=".$id)->findAll();
	
	
	//print_r($list);die;
		$this->assign("list",$list);
		$this->assign("id",$id);
		$this->assign("vo",$vo);
		$this->display ();
	}
	
	
	
	   public function table_set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M("DcRsItem")->where("id=".$id)->getField("title");
		$c_is_effect = M("DcRsItem")->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M("DcRsItem")->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		 
		 
		 
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	
	
	public function add()
	{	
		$id = intval($_REQUEST['id']);
		$supplier_id = M("SupplierLocation")->where("id=".$id)->getField("supplier_id");
		$this->assign("location_id",$id);
		$this->assign("supplier_id",$supplier_id);	
		$this->display();
	}
	
	
	
	public function insert() {
		B('FilterString');
		
		$supplier_id=$_REQUEST['supplier_id'];
		$location_id=$_REQUEST['location_id'];
		$data = M(MODULE_NAME)->create ();
		
		//开始验证有效性
		
		$this->assign("jumpUrl",u(MODULE_NAME."/add",array("id"=>$data['location_id'])));
		
		if(!check_empty($data['name']))
		{
			$this->error(L("NAME_EMPTY_TIP"));
		}	
		
		if(!check_empty($data['sort']))
		{
			$data['sort']=0;
		}	
		
		if(!check_empty($data['price']) || $data['price']==0)
		{
			$this->error("请填写定金金额");
		}	
		
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
			
		if (false !== $list) {
			
			$rs_time=$_REQUEST['rs_time'];
			$total_count=$_REQUEST['total_count'];
			$is_effect=$_REQUEST['is_teffect'];
			
			
			if($rs_time)
			{	
			
				foreach($rs_time as $k =>$v)
				{
					$time_data=array();
					/*
					$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])[:](0?[0-9]|[1-5][0-9])?$/";
					*/
					$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])$/";
					$time_data['rs_time']=$rs_time[$k];
					if(!preg_match($patten,$time_data['rs_time']))
					{
						$this->error(L("NOT_TIME_TYPE"));
					}
					$time_data['location_id']=$location_id;
					$time_data['supplier_id']=$supplier_id;
					$time_data['total_count']=intval($total_count[$k]);
					$time_data['is_effect']=intval($is_effect[$k]);
					$time_data['item_id']=$list;
	
		     			//插入新的时间段
		     			M("DcRsItemTime")->add($time_data);
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
	
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M("DcRsItem")->where(array ('location_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error (l("SUB_ARTICLECATE_EXIST"),$ajax);
				}
				if(M("DcRsItemTime")->where(array ('item_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error (l("SUB_TIME_EXIST"),$ajax);
				}

				$rel_data = M(MODULE_NAME)->where($condition)->findAll();			
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
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
	
		$item_time=M('DcRsItemTime')->where('item_id='.$id)->findAll();
		if(count($item_time)>0)
		{	require_once APP_ROOT_PATH."system/model/dc.php";
			foreach($item_time as $k=>$v){
				$item_time[$k]['rs_time']=to_date(to_timespan($v['rs_time']),"H:i");
			}
		
			$item_time=array_sort($item_time,'rs_time');
			$table_time_html=$this->get_template_html('add_table_time',$item_time);
			

			$this->assign("table_time_html",$table_time_html);
		}
		$this->assign ( 'vo', $vo );
		
		$this->display ();
	}
	
	
	
	public function update() {
		B('FilterString');
		$supplier_id=$_REQUEST['s_id'];
		$location_id=$_REQUEST['l_id'];
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if($data['price']==0)
		{
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error("定金不能为0");
		}
		
		if (false !== $list) {
			
			$rs_time=$_REQUEST['rs_time'];
			$total_count=$_REQUEST['total_count'];
			$is_effect=$_REQUEST['is_teffect'];
			$time_id=$_REQUEST['time_id'];
			
			$left_aids = array();
			
			
			foreach($time_id as $k=>$v)
			{
				if(intval($v) >0)
				{
				$left_aids[] = $v;
				}
			}
			
			
			if($left_aids)
			{  
				$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time WHERE id not in(".implode(",",$left_aids).") and item_id =".$data['id']);	
			}
			else
			{
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_rs_item_time WHERE item_id =".$data['id']);
			}
			
			
			if($rs_time)
			{	
			
				foreach($rs_time as $k =>$v)
				{
					$time_data=array();
					/*
					$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])[:](0?[0-9]|[1-5][0-9])?$/";
					*/
					$patten = "/^\s?(0?[0-9]|1[0-9]|2[0-3])[:](0?[0-9]|[1-5][0-9])$/";
					$time_data['rs_time']=$rs_time[$k];

					if(!preg_match($patten,$time_data['rs_time']))
					{
						$this->error(L("NOT_TIME_TYPE"));
					}
					
					$time_data['total_count']=intval($total_count[$k]);
					$time_data['is_effect']=intval($is_effect[$k]);
					$time_data['item_id']=$data['id'];
					$time_data['id']=intval($time_id[$k]);
					$time_data['supplier_id']=$supplier_id;
					$time_data['location_id']=$location_id;
					if($time_data['id']>0)
					{		//更新时间段
							$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_time",$time_data,"UPDATE","id='".$time_data['id']."'");
		    	 	}
		    	 	else
		    	 	{
		     			//插入新的时间段
		     			M("DcRsItemTime")->add($time_data);
		     		
		     		}	
		
				}	
			
			 }
			
			
			
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
	
	
	public function get_template_html($templte,$data=array()){
	
		$template_html = "";
		foreach($data as $data_row)
		{	
		$this->assign("data",$data_row);
		$template_html .= $this->fetch($templte);
		}
		return $template_html;
	}
	
	
	public function add_table_time()
	{
		$this->display();
	}
	
	
}
?>
