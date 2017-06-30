<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class SupplierAction extends CommonAction{
	public function index()
	{
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		
		$id = intval($_REQUEST['id']);
		if($id)
			$ex_condition = " and id = ".$id." ";
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
	    if(isset($order))
	    {
	    	$orderby = "order by ".$order." ".$sort;
	    }else 
	    {
	    	 $orderby = "";
	    }

	    	
		
		if(strim($_REQUEST['name'])!='')
		{
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier");
			if($total<50000)
			{
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition  $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition");			
			}
			else
			{
				$kws_div = div_str(trim($_REQUEST['name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE) $ex_condition $orderby limit ".$limit);
				$total = $GLOBALS['db']->getOne("select * from ".DB_PREFIX."supplier where match(`name_match`) against('".$kw_unicode."' IN BOOLEAN MODE) $ex_condition");
				
			}
		}
		else
		{
			$list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where 1=1 $ex_condition  $orderby limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where 1=1 $ex_condition");
		}
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		if(OPEN_WEIXIN)
		{
			foreach($list as $k=>$v)
			{
				if($v['platform_status']==1)
				{
					$list[$k]['is_bind'] = M("WeixinAccount")->where("user_id = ".$v['id'])->count();
				}
			}	
		}
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);
			
		$this->display ();
		return;
	}
	public function add()
	{	
		$this->assign("new_sort", M(MODULE_NAME)->max("sort")+1);
		
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['publish_verify_balance'] = round($vo['publish_verify_balance']*100,2);
		//商户账户信息
		$account_info = M("SupplierAccount")->where("supplier_id=".$id." and is_main=1")->find();
		
		$this->assign("account_info",$account_info);
		$this->assign ( 'vo', $vo );
		$this->display ();
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
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				
				
				if(M("deal")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error (l("该商户下还有商品"),$ajax);
				}
				
				if(M("SupplierLocation")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->count()>0)
				{
					$this->error ("请先清空所有的分店数据",$ajax);
				}
				//查询子账户
				$sub_accounts = M("SupplierAccount")->field("id,account_name")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->select();
				foreach ($sub_accounts as $k=>$v){
				    $f_sub_accounts[] = $v['id'];
				}
				
				M("SupplierAccount")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				M("SupplierAccountAuth")->where(array ('supplier_account_id' => array ('in', $f_sub_accounts )))->delete();
				
				M("SupplierMoneyLog")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				M("SupplierMoneySubmit")->where(array ('supplier_id' => array ('in', explode ( ',', $id ) )))->delete();
				
				
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
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}					
		
		// 更新数据
		$log_info = $data['name'];
		if(M(MODULE_NAME)->where("name='".$data['name']."'")->find()){
			$this->error("商户名重复");
		}else{
			$list=M(MODULE_NAME)->add($data);	
		}
		if (false !== $list) {
			syn_supplier_match($list);
			//成功提示
			
			$supplier_location['name'] = $data['name'];
			$supplier_location['is_main'] = 1;
			$supplier_location['supplier_id'] = $list;
			M("SupplierLocation")->add($supplier_location);
			
			$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$list)));
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success("新增成功，请完善资料");
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
		}
		
		//处理商户帐号信息部分
		$account_id = intval($_REQUEST['account_id']);

		//更新商户账户信息
		$account_ins['supplier_id'] = $data['id'];
		$account_ins['id'] = $account_id;
		$account_ins['account_name'] = strim($_REQUEST['account_name']);
		$account_ins['mobile'] = strim($_REQUEST['mobile']);
		$account_ins['is_effect'] = 1;
		if(!$account_ins['id'])
		{
			if(!$account_ins['account_name'])
			{
				$this->error("请输入管理员账户");
			}
			if(!$account_ins['mobile'])
			{
				$this->error("请输入商户手机");
			}
		}
		if (strim($_REQUEST['account_password'])){
		    $account_ins['account_password'] = md5(strim($_REQUEST['account_password']));
		}
		else
		{
			if(!$account_ins['id'])
			{
				$this->error("请输入密码");
			}
		}
		//商户帐号验证
		if(M("SupplierAccount")->where("account_name='".$account_ins['account_name']."' and id<>".$account_id)->count()){
		    
		    $this->error("账户名已被使用！");
		}

		if(M("SupplierAccount")->where("mobile='".$account_ins['mobile']."' and id<>".$account_id)->count()){
		    $this->error("手机号已被使用！");
		}

		
		$data['publish_verify_balance'] = $data['publish_verify_balance']/100;
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		if (false !== $list) {
			syn_supplier_match($data['id']);
			 
			$account_ins['is_main'] = 1;
			if($account_ins['id'])
				M("SupplierAccount")->save ($account_ins);
			else
				M("SupplierAccount")->add ($account_ins);
			
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	
	
	/*
	 * 商户提现
	 */	
	public function charge_index()
	{
		if(isset($_REQUEST['status']))
		{
			$map['status'] = intval($_REQUEST['status']);
		}		
		$model = D ("SupplierMoneySubmit");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	/*
	 * 商户提现编辑
	 */	
	public function charge_edit()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		if($charge_id>0){
				$charge_info = M("SupplierMoneySubmit")->getById($charge_id);
				$supplier_info= M("Supplier")->where("id=".$charge_info['supplier_id'])->find();
				$charge_info['supplier_name']=$supplier_info['name'];
				$charge_info['supplier_money']= $supplier_info['money'];
				$this->assign("type",1);				
				$this->assign("charge_info",$charge_info);
		}
		if($supplier_id>0){
				$supplier_info= M("Supplier")->where("id=".$supplier_id)->find();
				$this->assign("supplier_info",$supplier_info);
				$this->assign("type",2);
		}

		$this->display();
	}	
	
	public function refuse_edit()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		if($charge_id>0){
			$charge_info = M("SupplierMoneySubmit")->getById($charge_id);
			$this->assign("charge_info",$charge_info);
		}
		if($supplier_id>0){
			$supplier_info= M("Supplier")->where("id=".$supplier_id)->find();
			$this->assign("supplier_info",$supplier_info);
		}
	
		$this->display();
	}
	
	public function dorefuse()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$reason = strim($_REQUEST['reason']);
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_money_submit set status = 2,reason = '".$reason."' where id = ".$charge_id." and status = 0 ");
		if($GLOBALS['db']->affected_rows())
			$this->success("操作成功");
		else
			$this->error("操作失败");
	}
	
	/*
	 * 商户提现审核
	 */	
	public function docharge()
	{
		$charge_id = intval($_REQUEST['charge_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		$log=strim($_REQUEST['log']);
		require_once APP_ROOT_PATH."system/model/supplier.php";
		if($charge_id>0){
				$charge = M("SupplierMoneySubmit")->getById($charge_id);
				$supplier_info=M("Supplier")->getById($charge['supplier_id']);
				$charge['money']=floatval($_REQUEST['money']);
				if($charge['money']<=0)$this->error("提现金额必须大于0");
				

				if($charge['money']>$supplier_info['money'])$this->error("提现超额");				
				
				if($charge['status']==0)
				{
					M("SupplierMoneySubmit")->where("id=".$charge['id'])->setField("status",1);
					M("SupplierMoneySubmit")->where("id=".$charge['id'])->setField("money",$charge['money']);					
					modify_supplier_account($charge['money'],$charge['supplier_id'],5,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);//.提现增加
					modify_supplier_account("-".$charge['money'],$charge['supplier_id'],3,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);//已结算减少
					modify_statements($charge['money'],3,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);
					modify_statements($charge['money'],5,$supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log);
					
					send_supplier_withdraw_sms($supplier_info['id'],$charge['money']);
					save_log($supplier_info['name']."提现".format_price($charge['money'])."元审核通过。".$log,1);					
					$this->success("确认提现成功");
				}
				else
				{
					$this->error("已提现过，无需再次提现");
				}
	
		}
		if($supplier_id>0){
			$supplier_info=M("Supplier")->getById($supplier_id);
			$remittance_num=floatval($_REQUEST['money']);
			if($remittance_num<=0)$this->error("打款金额必须大于0");
			

			if($remittance_num>$supplier_info['money']) $this->error("打款超额");	
								
			modify_supplier_account($remittance_num,$supplier_id,5,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);//.提现增加
			modify_supplier_account("-".$remittance_num,$supplier_id,3,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);//已结算减少
			modify_statements($remittance_num,3,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);
			modify_statements($remittance_num,5,"成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log);
			
			send_supplier_withdraw_sms($supplier_info['id'],$remittance_num);
			save_log("成功打款给".$supplier_info['name'].format_price($remittance_num)."元。".$log,1);					
			$this->success("打款成功");			
			
		}
	}
	
	public function del_charge()
	{
		$id = intval($_REQUEST['id']);
		$charge = M("SupplierMoneySubmit")->getById($id);
		
		$list = M("SupplierMoneySubmit")->where ("id=".$id )->delete();		
		if ($list!==false) {					 
				save_log($charge['supplier_id']."号商户提现".$charge['money']."元记录".l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),1);
		} else {
				save_log($charge['supplier_id']."号商户提现".$charge['money']."元记录".l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),1);
		}

	}	
	
	public function view_wx()
	{
		$id = intval($_REQUEST['id']);
		
		
		$config = M("WeixinAccount")->where("user_id=".$id)->find();
		$this->assign("config",$config);
			
		$this->assign("unbind_url",U("Supplier/unbind",array("id"=>$id)));
		
		
		$verify_type_array=array(-1=>'未认证',0=>'微信认证',1=>'新浪微博认证',2=>'腾讯微博认证',3=>'已资质认证通过但还未通过名称认证',4=>'已资质认证通过、还未通过名称认证，但通过了新浪微博认证',5=>'已资质认证通过、还未通过名称认证，但通过了腾讯微博认证');
		$service_type_array=array(0=>'订阅号',1=>'由历史老帐号升级后的订阅号',2=>'服务号');
		$this->assign("verify_type",$verify_type_array[$config['verify_type_info']]);
		$this->assign("service_type",$service_type_array[$config['service_type_info']]);
		
		$this->display();
	}
	
	public function unbind()
	{
		$id = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_account where user_id = ".$id);
		app_redirect(U("Supplier/view_wx",array("id"=>$id)));
	}
	
	
}
?>