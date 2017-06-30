<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/main/core/dc_init.php";
/**
 * 外卖订餐
 * 
 *
 */
class dcModule extends MainBaseModule
{

	/**
	 * 
	 * 外卖商家列表页面
	 * 
	 * 输入：
	 * page:int 当前的页数
	 * cid：int 商家分类id
	 * sort:int 综合排序值，	起送价最低：0，评分最高：1，距离最近：2，免费配送：3 
	 * ptag：int 活动优惠，代表意义如下：
	 * 			dc_online_pay：是否在线支付，ptag=1
	 * 			dc_allow_cod：支持货到付款，ptag=2
	 * 			is_firstorderdiscount：是否支持新单立减，ptag=3
	 * 			is_payonlinediscount：是否支持在线支付优惠，ptag=4
	 * 			dc_allow_ecv：支持代金卷，ptag=5
	 * 			dc_allow_invoice：是否支持发票，ptag=6
	 * 
	 * 输出：
	 * advs: array 首页广告
	 * city_id：int 城市ID
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * sort:array:array  综合排序，结构如下
	 *  Array(
            [0] => Array
                (
                    [name] => 起送价最低
                    [code] => start_price
                    [sort_index] => 0
                )

            [1] => Array
                (
                    [name] => 评分最高
                    [code] => avg_point
                    [sort_index] => 1
                )

            [2] => Array
                (
                    [name] => 距离最近
                    [code] => distance
                    [sort_index] => 2
                )

            [3] => Array
                (
                    [name] => 免费配送
                    [code] => is_free_delivery
                    [sort_index] => 3
                )

        )
        
        
        *promote_info:array:array 首单立减和在线支付的具体信息，结构如下
        * Array
        (
            [is_firstorderdiscount] => Array
                (
                    [id] => 3
                    [class_name] => FirstOrderDiscount
                    [sort] => 5
                    [config] => a:1:{s:15:"discount_amount";s:2:"10";}
                    [description] => 首单立减10元（在线支付专享）
                )

            [is_payonlinediscount] => Array
                (
                    [id] => 7
                    [class_name] => PayOnlineDiscount
                    [sort] => 6
                    [config] => a:3:{s:14:"discount_limit";a:2:{i:0;s:2:"20";i:1;s:2:"40";}s:15:"discount_amount";a:2:{i:0;s:1:"5";i:1;s:2:"12";}s:11:"daily_limit";s:1:"2";}
                    [description] => 在线支付下单满20减5元，满40减12元，活动期间每天2单
                )

        )
        *
        *
        
        bcate_list:array:array，商家分类，结构如下
		 Array
                (
                    [0] => Array
                        (
                            [url] => /o2onew/index.php?ctl=dc
                            [name] => 全部
                            [current] => 1
                        )
                )
        promote :array:array，优惠活动，结构如下
         Array
                (
                    [0] => Array
                        (
                        [name] => 支持在线支付
                  		[ptag] => 1
                        )
                )
          
         
      dc_location_list：array:array 商家列表，结构如下
      	优惠详情用到的字段：
      	is_payonlinediscount：是否支持在线支付优惠
      	is_firstorderdiscount：是否支持新单立减
      	dc_allow_invoice：是否支持发票
      	dc_allow_ecv：是否支持代金卷
      	dc_online_pay：是否支持在线支付
      	dc_allow_cod：是否支持线下支付（即餐到付款）
      	其他有用字段
      	preview：图片
      	distance：距离,单位为米
      	location_delivery_info：array  配送信息，没有，则是免运费
      	
          Array
        (
            [0] => Array
                (
                    [id] => 28
                    [name] => 石山水美式餐厅（东街店）
                    [route] => 
                    [address] => 鼓楼区东街14号闽辉大厦1楼
                    [tel] => 059188855588
                    [contact] => 李四
                    [xpoint] => 119.307134
                    [ypoint] => 26.092442
                    [supplier_id] => 28
                    [open_time] => 9:00-22:00
                    [brief] => 
                    [is_main] => 1
                    [api_address] => 
                    [city_id] => 15
                    .
                    .
                    .
                    .
                    .
                  )
         )      
             
	 */
	public function index()
	{		
		global_run();
		dc_global_run();
		init_app_page();	
		//参数处理
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		
		$GLOBALS['tmpl']->assign("s_info",$s_info);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		
		if(isset($GLOBALS['geo']['address']) && strim($GLOBALS['city']['name'])==strim($s_info['city_name'])){
		
		
		$param['sort']=$sort = intval($_REQUEST['sort']);
		if($sort>4 || $sort < 0){
			$param['sort']=0;
		}

		$param['cid'] = intval($_REQUEST['cid']);
		$param['ptag'] = intval($_REQUEST['ptag']);
		$param['page'] = intval($_REQUEST['page']);
		
		$data = request_api("dc","index",$param);
	
		foreach($data['dc_location_list'] as $k=>$v){
			$data['dc_location_list'][$k]['distance']=$v['distance']/1000;
			$data['dc_location_list'][$k]['url']=wap_url("index","dcbuy",array('lid'=>$data['dc_location_list'][$k]['id']));
			
		}

		if(isset($data['page']) && is_array($data['page'])){
		
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		
		foreach($data['bcate_list'] as $k=>$v){	
			$data['bcate_list'][$k]['url']=wap_url("index","dc",array('cid'=>$v['id']));	
		}
		
		foreach($data['sort'] as $k=>$v){
			$data['sort'][$k]['url']=wap_url("index","dc",array('sort'=>$v['sort_index']));
		}

		foreach($data['promote'] as $k=>$v){
			$data['promote'][$k]['url']=wap_url("index","dc",array('ptag'=>$v['ptag']));
		}
		

		$GLOBALS['tmpl']->assign('s_info',$s_info);
		$GLOBALS['tmpl']->assign("data",$data);
	
		$GLOBALS['tmpl']->display("dc/dc_location.html");

		}
		else
		{	
			/*
			$dc_search_history_str=es_cookie::get('dc_search_history');
			$dc_search_history=array();
			$dc_search_history=json_decode($dc_search_history_str,true);
			$dc_search_history=array_values($dc_search_history);
			$GLOBALS['tmpl']->assign('dc_search_history',$dc_search_history);
			$GLOBALS['tmpl']->display("dc/dc_position.html");
			*/
			app_redirect(wap_url('index','dcposition'));
		}
		

	}
	

}
?>