<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dc_rsorderModule extends MainBaseModule
{

	/**
	 *
	 * 会员中心预订订单列表页
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&r_type=2&page=1
	 * 输入：
	 * page:int 当前的页数，没输入些参数时，默认为第一页
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * order_list:array:array, 外卖订单列表，结构如下
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * pay_status：支付状态: 0未支付 1已支付
	 * confirm_status：订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送，或者预订订单验证成功
	 * is_cancel：是否被用户/商家(拒绝接单)/管理员取消,0:未取消,1:用户取消,2:商户取消,3.管理员取消
	 * refund_status：退款状态：0无退款，1退款申请中，2已退款，3退款驳回
	 * is_rs：是否为预定定单，0:否 ，为外卖 订单，1:是，为预订订单
	 * rs_price：如为预订订单，这字段为预订的定金
	 * is_dp：是否已点评，一个订单只能点评一次
	 * dp_id：0为未点评，大于0，为具体的点评ID
	 * location_name：商家名称
	 * preview：商家图片
	 * payment_id:0为在线支付，1为货到付款
	 * rs_info:array:array,为预订信息,其中name为预订座位名称，table_time为预订的时间
	 * order_state返回订单的状态  state_format为状态的文字描述 ，state为状态，代表意义如下：
	 *  1、待支付
		2、待接单
		3、已接单
		4、已完成
		5、订单关闭
		6.退款申请中
		7.已退款
		8.退款驳回
	 * Array
	 * 		(
			        [id] => 38
                    [order_sn] => 2015080404462699
                    [location_id] => 41
                    [create_time] => 1438649186
                    [order_status] => 0
                    [confirm_status] => 0
                    [pay_status] => 1
                    [total_price] => 150.0000
                    [payment_id] => 0
                    [refund_status] => 0
                    [consignee] => 王明
                    [is_cancel] => 0
                    [rs_price] => 150.0000
                    [is_rs] => 1
                    [is_dp] => 0
                    [location_name] => 果果外卖
                    [dp_id] => 0
                    [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a.jpg
                    [rs_info] => Array
                        (
                            [0] => Array
                                (
                                    [name] => 散桌8-10人桌
                                    [table_time] => 1438977600
                                    [table_time_format] => 2015-08-08 星期六 12:00
                                )

                        )
                     [order_state] => Array
                        (
                            [state] => 5
                            [state_format] => 订单关闭
                        )
                )        
	 *
	 */
	public function index()
	{


		$param['page']=intval($_REQUEST['page']);
		$data = request_api("dc_rsorder","index",$param);
		
		if($data['user_login_status']==1)
		{
		
			
			require_once APP_ROOT_PATH."system/model/dc.php";
			foreach($data['order_list'] as $k=>$v){
				$data['order_list'][$k]['order_state']=get_order_state($v,'wap','index');
			}
			if(isset($data['page']) && is_array($data['page'])){
					
				//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
				$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
				//$page->parameter
				$p  =  $page->show();
				//print_r($p);exit;
				$GLOBALS['tmpl']->assign('pages',$p);
			}
			//print_r($data);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("dc/uc/rsorder_index.html");
		
		}
		else
		{
			app_redirect(wap_url('index','user#login'));
		}
	}
	
	
	
	public function view()
	{
		$param['id']=intval($_REQUEST['id']);
		$data = request_api("dc_rsorder","view",$param);

		if($data['user_login_status']==1)
		{
			if($data['is_order_exists']==1){

				
				
				require_once APP_ROOT_PATH."system/model/dc.php";
				$data['order_info']['order_state']=get_order_state($data['order_info'],'wap','view');
	
				$GLOBALS['tmpl']->assign("data",$data);
				$GLOBALS['tmpl']->display("dc/uc/rsorder_view.html");
			}else{
				showErr('订单不存在',0,wap_url('index','dc_rsorder'));
			}
		}
		else
		{
			showErr('未登录，请先登录',0,wap_url('index','user#login'));
		}
	}
	
	/**
	 * 预订电子劵短信发送接口
	 *
	 */
	public function rend_coupon_sms()
	{
		
		$param['id']=intval($_REQUEST['id']);
		$data = request_api("dc_rsorder","rend_coupon_sms",$param);
		
		if($data['user_login_status']==1)
		{
			if($data['is_order_exist']==1){
				
				$result['status']=$data['status'];
				$result['info']=$data['info'];
				ajax_return($result);
			}else{
				showErr('订单不存在',0,wap_url('index','dc_rsorder'));
			}
		}
		else
		{
			showErr('未登录，请先登录',0,wap_url('index','user#login'));
		}
	}
	
	
	/**
	 * 预订取消订单接口
	 * status:int，操作返回状态，status=1，成功，status=0，失败
	 * info：为提示信息
	 */
	public function cancel()
	{
	
		$param['id']=intval($_REQUEST['id']);
		$data = request_api("dc_rsorder","cancel",$param);
		
		if($data['user_login_status']==1)
		{
			if($data['is_order_exist']==1){
				
				$result['status']=$data['status'];
				$result['info']=$data['info'];
				ajax_return($result);
			}else{
				showErr('订单不存在',0,wap_url('index','dc_rsorder'));
			}
		}
		else
		{
			showErr('未登录，请先登录',0,wap_url('index','user#login'));
		}
	}
	
	
	
	
	
	/**
	 * 预订电子劵申请退款的接口
	 * status：为申请退款操作的状态，status=0,申请退款失败;status=1,申请退款成功
	 * info:返回的提示信息
	 *
	 */
	public function do_refund()
	{
		global_run();
		$param['id']=intval($_REQUEST['id']);
		$param['content']=strim($_REQUEST['content']);
		$data = request_api("dc_rsorder","do_refund",$param);

		if($data['user_login_status']==1)
		{
			if($data['is_order_exist']==1){
		
				$result['status']=$data['status'];
				$result['info']=$data['info'];
				ajax_return($result);
			}else{
				showErr('订单不存在',0,wap_url('index','dc_rsorder'));
			}
		}
		else
		{
			showErr('未登录，请先登录',0,wap_url('index','user#login'));
		}
	}
	
}
?>