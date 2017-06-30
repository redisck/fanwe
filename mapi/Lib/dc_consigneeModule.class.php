<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dc_consigneeModule extends MainBaseModule
{
	/**
	 *
	 * 会员中心收货地址列表页
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_consignee&r_type=2
	 * 输入：
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * consignee_list:array:array,会员送货地址列表，结构如下：
	 * api_address：定位地址，address：详细地址，完成地址为：api_address+address
	 * is_main：是否为默认地址，0为不是，1为是。
	 * xpoint，ypoint地址的经维度
	 * Array
        (
            [0] => Array
                (
                    [id] => 119
                    [user_id] => 71
                    [address] => fff
                    [api_address] => 福州市鼓楼区ok保龄球会所
                    [xpoint] => 119.275915
                    [ypoint] => 26.118346
                    [consignee] => 翁贤云
                    [mobile] => 15159646624
                    [is_main] => 1
                )

            [1] => Array
                (
                    [id] => 120
                    [user_id] => 71
                    [address] => 地中心
                    [api_address] => 福州市仓山区福州仓山万达广场
                    [xpoint] => 119.281567
                    [ypoint] => 26.042483
                    [consignee] => 王明
                    [mobile] => 15158789965
                    [is_main] => 0
                )

        )
	 */ 
	public function index()
	{
		global_run();
		$root = array();
		$root['page_title']='我的收货地址';
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;

		}else{
			$root['user_login_status']=1;	
		
			$user_id=intval($GLOBALS['user_info']['id']);
			//输出所有配送方式
			$consignee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_consignee where user_id = ".$user_id);
			$root['consignee_list']=$consignee_list;	
			$root['main_id']= $GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_consignee where is_main=1 and user_id = ".$user_id);

		}

		output($root);
	}
	
	/**
	 * 新增或编辑送货地址页面
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_consignee&act=add&r_type=2
	 *
	 * 输入：
	 * id:int 送货地址的ID,如果是新增，可以不传此参数
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * consignee_id：int,送货地址ID,等于0时，为新增送货地址
	 * dc_consignee_info：array,编辑时，返回的些送货id的送货地址信息
	 * page_title：string ，标题
	 */
	
	public function add()
	{
		global_run();
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$consignee_id=intval($GLOBALS['request']['id']);
			if($consignee_id>0)
			{
				$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where id = ".$consignee_id);
				if($consignee_info){
					$root['dc_consignee_info']=$consignee_info;
					$c_id=$consignee_info['id'];
				}else{
					$c_id=0;
				}

			}else{
				$c_id=0;
			}
				
			$root['consignee_id']=$c_id;
			$root['page_title']="送货地址";
			output($root);

		}
	
	}
	
	
	/**
	 * 新增或修改外卖 送货的提交保存地址
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_consignee&act=save_dc_consignee&r_type=2
	 * 
	 * 输入：
	 * id:如果是修改现在的送货地址，就传该送货地址的ID，如果是新增送货地址，则不要传该参数，或者参数为0
	 * xpoint：送货地址的经度
	 * ypoint：送货地址的维度
	 * api_address：送货地址的定位地址
	 * address：送货地址的具体地址，如：街，道，层
	 * consignee：收货人姓名
	 * mobile：收货人手机号码
	 * is_main:是否设为默认地址，1为设置为默认地址，0为不设为默认地址
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：新增或者修改的状态，如果status=1，则操作成功，status=0，操作失败
	 * info:返回的提示信息
	 */
	
	public function save_dc_consignee(){
	
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;

			$consignee_info['xpoint']=floatval($GLOBALS['request']['xpoint']);
			$consignee_info['ypoint']=floatval($GLOBALS['request']['ypoint']);
			$consignee_info['api_address']=strim($GLOBALS['request']['api_address']);
			$consignee_info['address']=strim($GLOBALS['request']['address']);
			$consignee_info['consignee']=strim($GLOBALS['request']['consignee']);
			$consignee_info['mobile']=$GLOBALS['request']['mobile'];
			
			if(!check_mobile($consignee_info['mobile']))
			{
				output($root,0,"手机号格式不正确");
			}
			
			$consignee_info['is_main']=intval($GLOBALS['request']['is_main']);
			$id=intval($GLOBALS['request']['id']);
			$consignee_info['user_id'] = intval($GLOBALS['user_info']['id']);
				
			$user_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where id=".$id);
			if($consignee_info['xpoint']!='' && $consignee_info['ypoint']!='' && $consignee_info['api_address']!='' && $consignee_info['address']!='' && $consignee_info['consignee']!='' && $consignee_info['mobile']!=''){
				$consignee_num=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where user_id=".$consignee_info['user_id']);
				
				if($consignee_num>0){
					if($consignee_info['is_main']==1){
						$consignee=array();
						$consignee['is_main']=0;
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee,$mode='UPDATE','user_id='.$consignee_info['user_id'],$querymode = 'SILENT');
					}else{
						$main_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_consignee where is_main=1 and user_id=".$consignee_info['user_id']);
						if($main_id==$id){
							output($root,0,'至少保留一个默认地址');
						}
							
					}
				}else{
					
					$consignee_info['is_main']=1;
				}

				
				if($user_count > 0){
					
					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info,$mode='UPDATE','id='.$id,$querymode = 'SILENT');
			
				}else{

					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info);
				}
				
				$status=1;
				$info="操作成功";
				
			}else{

					$status=0;
					$info="请输入完整的送货信息";
			}
			output($root,$status,$info);
		}
	}
	

	/**
	 * 删除送货地址
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_consignee&act=del&r_type=2
	 *
	 * 输入：
	 * id:int 送货地址的ID
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：删除送货地址的状态，如果status=1，则删除送货地址成功，status=0，删除送货地址失败
	 * info:返回的提示信息
	 */
	
	
	public function del(){
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where user_id=".intval($GLOBALS['user_info']['id']));
			if($count>1){
				$id=intval($GLOBALS['request']['id']);	
				$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_consignee where id=".$id." and user_id=".intval($GLOBALS['user_info']['id']));
				if($GLOBALS['db']->affected_rows())
				{
					$count_main=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where is_main=1 and user_id=".intval($GLOBALS['user_info']['id']));
					if($count_main==0){
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=1 where user_id=".intval($GLOBALS['user_info']['id'])." order by id desc limit 1");
					}
					
					$status=1;
					$info=$GLOBALS['lang']['DELETE_SUCCESS'];

				}
				else
				{
					$status=0;
					$info="删除失败";
				}
			}else{
				$status=0;
				$info="至少保留一个送货地址";
				
			}
			output($root,$status,$info);
		
	}
	
}	
	/**
	 * 设为默认送货地址
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_consignee&act=set_default&r_type=2
	 *
	 * 输入：
	 * id:int 送货地址的ID
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：删除送货地址的状态，如果status=1，则删除送货地址成功，status=0，删除送货地址失败
	 * info:返回的提示信息
	 */
	


		public function set_default(){
		
			$root = array();
			if(check_save_login()!=LOGIN_STATUS_LOGINED)
			{
				$root['user_login_status']=0;
				output($root);
			}else{
				$root['user_login_status']=1;
				$id=intval($GLOBALS['request']['id']);
				if($id>0){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=0 where user_id=".intval($GLOBALS['user_info']['id']));
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=1 where id=".$id);
						if($GLOBALS['db']->affected_rows())
						{
							$status=1;
							$info="设置成功";
						}
						else
						{
							$status=0;
							$info="设置失败";
						}
					}else{
						$status=0;
						$info="设置失败";
					}
				}
				output($root,$status,$info);
			
			}

	
}
?>