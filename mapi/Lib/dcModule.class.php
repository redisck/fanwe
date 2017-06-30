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
                    [code] => delivery_price
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
		//init_app_page();	
		require_once APP_ROOT_PATH."system/model/dc.php";
		/* 获取最新搜索名 */
		
		$root = array();
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		//开始身边团购的地理定位
		
		if(	$GLOBALS['request']['from']=='wap'){
			$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
			$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint

		}else{
			$ypoint = $GLOBALS['request']['ypoint'];  //ypoint
			$xpoint = $GLOBALS['request']['xpoint'];  //xpoint
		}

		
		$tname='sl';
		if($GLOBALS['kw'])
		{
			$ext_condition.=" and ".$tname.".name like '%".$GLOBALS['kw']."%' ";
		}
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";
		
			$sort_field = " distance asc ";
		}
		
		
		
		//参数处理
		$deal_city_id = intval($GLOBALS['city']['id']);
		$sort = intval($GLOBALS['request']['sort']);
		if($sort>4 || $sort < 0){
			$sort=0;
		}
		$deal_cate_id = intval($GLOBALS['request']['cid']);
		$ptag = intval($GLOBALS['request']['ptag']);
		$page = intval($GLOBALS['request']['page']);
		if($page==0){
			$page = 1;
		}	
		if($deal_cate_id)$url_param['cid'] = $deal_cate_id;
		if($ptag)$url_param['ptag'] = $ptag;
		
		$param=array("cid"=>$deal_cate_id,"city_id"=>$deal_city_id);
		
		//seo元素
		$page_title = "外卖";
		$page_keyword = "外卖";
		$page_description = "外卖";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存
		$cate_list = load_auto_cache("cache_dc_cate"); //分类缓存
		$deal_area_id=0;
		$deal_quan_id=0;
		$cache_param = array("cid"=>$deal_cate_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("dc_filter_dc_nav_cache",$cache_param);

		if(($deal_cate_id>0&&$cate_list[$deal_cate_id])||($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0))
			$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","dc"))); //全部
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","dc",$tmp_url_param));
			$filter_row['list'] = $filter_nav_data['bcate_list'];
			$filter_row_data['nav_list'][] = $filter_row;
				
			$page_title = $cate_list[$deal_cate_id]['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$cate_list[$deal_cate_id]['name'];
			$page_description = $page_description.",".$cate_list[$deal_cate_id]['name'];
				
		
		}

			//输出大分类
			$bcate_list = $filter_nav_data['bcate_list'];
			foreach($bcate_list as $k=>$v){
				$bcate_list[$k]['icon_img']=get_abs_img_root(get_spec_image($v['icon_img'],140,85,1));
			}
		
		/**
		 * 外卖促销规则
		 * dc_online_pay：是否在线支付，值定为1
		 * dc_allow_cod：支持货到付款，值定为2
		 * is_firstorderdiscount：是否支持新单立减，值定为3
		 * is_payonlinediscount：是否支持在线支付优惠，值定为4
		 * dc_allow_ecv：支持代金卷，值定为5
		 * dc_allow_invoice：是否支持发票，值定为6
		 * 
		 * 
		 */
		$ptags_arr=array();
		for($t=0;$t<=6;$t++)
		{
			$ptags_arr[] = array(
							"name"	=>	lang("DC_PTAG_".$t),
							"ptag"=>$t
						);
		}

		$ptags = 0;
		if($ptag > 0 && $ptag <7){
			$t2 = pow(2,$ptag);
			$ptags = $ptags|$t2;
		}
	
		$ext_condition .= " and ".$tname.".dc_ptag&".$ptags."=".$ptags." ";
		$promote=$ptags_arr;	


		$promote_info=get_dc_promote_info();
		
		//分页
		$page_size = PAGE_SIZE;
	

		
		$sort_arr=array(

				array("name"=>"起送价最低","code"=>"start_price","sort_index"=>0),
				array("name"=>"评分最高","code"=>"avg_point","sort_index"=>1),
				array("name"=>"距离最近","code"=>"distance","sort_index"=>2),
				array("name"=>"免费配送","code"=>"delivery_price","sort_index"=>3),
		
		);
		
		
		//获取餐厅列表
		$dc_location_list  = get_dc_location_list($type='is_dc',$limit='',$param,$tag=array(), $ext_condition,$sort_field,$field_append);
		if($GLOBALS['db']->getAll($dc_location_list['condition'])){
			$total = count($GLOBALS['db']->getAll($dc_location_list['condition']));
		}else{
			$total=0;
		}
		
		
		$page_total = ceil($total/$page_size);
		$location_delivery_info=get_location_delivery_info($dc_location_list['id_arr']);

		foreach($dc_location_list['list'] as $k=>$v){
				
			$dc_location_list['list'][$k]['url']=wap_url('index','dcbuy',array('lid'=>$v['id']));
			if($location_delivery_info[$k]){
				$dc_location_list['list'][$k]['location_delivery_info']=$location_delivery_info[$k];	
			}
			if(isset($location_delivery_info[$k])){
				$dc_location_list['list'][$k]['is_free_delivery']=$location_delivery_info[$k]['is_free_delivery'];
				$dc_location_list['list'][$k]['start_price']=$location_delivery_info[$k]['start_price'];
				$dc_location_list['list'][$k]['delivery_price']=$location_delivery_info[$k]['delivery_price'];
			}else{
				$dc_location_list['list'][$k]['is_free_delivery']=1;
				$dc_location_list['list'][$k]['start_price']=0;
				$dc_location_list['list'][$k]['delivery_price']=0;
			}
			$dc_location_list['list'][$k]['preview']=get_abs_img_root(get_spec_image($v['preview'],360,270,1));
			$dc_location_list['list'][$k]['distance']=$v['distance']*1000;
		}
		

		if($sort==0){  //起送价最低,升序
			$sort_type='asc';
			$sort_info['name']="起送价最低";
		}elseif($sort==1){  //评分最高,降序
			$sort_type='desc';
			$sort_info['name']="评分最高";
		}elseif($sort==2){  //距离最近,升序
			$sort_type='asc';
			$sort_info['name']="距离最近";
		}elseif($sort==3){  //免费配送,降序
			$sort_type='asc';
			$sort_info['name']="免费配送";
		}
		
		$dc_location_list['list']=array_sort($dc_location_list['list'],$sort_arr[$sort]['code'],$sort_type);

		$limit = (($page-1)*$page_size).",".$page_size;
		
		$dc_location_new=array();

		for($i=($page-1)*$page_size;$i<$page*$page_size;$i++){
			if($total>$i && $total >0){
				$dc_location_new[]=$dc_location_list['list'][$i];
				
			}
		}
	
		//$dc_location_new=count($dc_location_new)>0?$dc_location_new:array();
		
		//广告
		$city_id = $GLOBALS['city']['id'];
		$city_name =  $GLOBALS['city']['name'];
		
		$root['city_id'] = $city_id;
		$root['city_name'] = $city_name;
		$adv_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS_".intval($city_id));
		
		//广告列表
		if($adv_list===false)
		{
			$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and  position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
			$advs = $GLOBALS['db']->getAll($sql);
		
		
			$adv_list = array();
			foreach($advs as $k=>$v)
			{
				$adv_list[$k]['id'] = $v['id'];
				$adv_list[$k]['name'] = $v['name'];
				$adv_list[$k]['img'] = get_abs_img_root($v['img']);  //首页广告图片规格为 宽: 640px 高: 240px
				$adv_list[$k]['type'] = $v['type'];
				$adv_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$adv_list[$k]['ctl'] = $v['ctl'];
			}
			$GLOBALS['cache']->set("MOBILE_INDEX_ADVS_".intval($city_id),$adv_list,300);
		}
		$cate_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."dc_cate where id=".$deal_cate_id);
		if($ptag>0){
				
			$ptag_info['name'] = lang("DC_PTAG_".$ptag);
		}			
		$ptag_info['id'] = $ptag;
		$sort_info['id'] = $sort;

		$root['sort_info'] = $sort_info;
		$root['ptag'] = $ptag_info;
		$root['cate_info'] = $cate_info;
		$root['advs'] = $adv_list?$adv_list:array();
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
		$root['page_title']=$page_title;
		$root['page_keyword']=$page_keyword;
		$root['page_description']=$page_description;
		$root['sort'] =$sort_arr;
		$root['promote_info']=$promote_info;
		$root['bcate_list']=$bcate_list;
		$root['promote']=$promote;
		$root['dc_location_list']=$dc_location_new;

		output($root);
		

	}

	
}
?>