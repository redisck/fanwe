<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dctableModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的订座页面
	 *
	 * 输入：
	 * lid:int 商家ID
	 * tid:座位ID,当第一次进入该页面时，不需要传该参数，接口会默认分配第一个tid;
	 *
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述
	 * location_dc_table_cart为订座购物车信息
	 * table_now:array,当前选中座位的信息 
	 * Array
        (
            [id] => 16
            [name] => 2人桌
            [price] => 50.0000
        )
	 * cart_list:购物车中订座或者菜品的购物信息，total_data为购物车的统计数据，total_price为购物总金额，total_count：为购物总数量

	 *  Array
        (
            [cart_list] => Array
                (
                    [0] => Array
                        (
                            [id] => 198
                            [session_id] => db0gdd485luijc8391rr4l5416
                            [user_id] => 0
                            [location_id] => 41
                            [supplier_id] => 43
                            [name] => 散桌8-10人桌
                            [icon] => 
                            [num] => 1
                            [unit_price] => 150.0000
                            [total_price] => 150.0000
                            [menu_id] => 7
                            [table_time_id] => 12
                            [table_time] => 1438651800
                            [cart_type] => 0
                            [add_time] => 1438639174
                            [is_effect] => 1
                            [url] => /o2onew/index.php?ctl=dctable&lid=41
                            [table_time_format] => <span class="time_span">2015-08-04</span><span class="time_span">星期二</span><span class="time_span">17:30</span>
                        )
                 )
           [total_data] => Array
                (
                    [total_price] => 117.0000
                    [total_count] => 6
                )
          )          
	 * 
	 * $dclocation:array:array 商家信息 ,结构如下
	 * is_collected为是否已经收藏
	 * Array
        (
            [id] => 41
            [name] => 果果外卖
            [is_dc] => 1
            [is_reserve] => 1
            [is_collected] => 1
        )   
        
          

     * time_info下面的date_info为日期时间，table_info为该天的可预订的座位时间段
            Array
                (
                    [0] => Array
                        (
                            [date_info] => 2015-08-05
                            [table_info] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 12
                                            [total_count] => 2
                                            [rs_time] => 17:30
                                            [item_id] => 7
                                        )
                                 )
                          )
                  )
                                        
  
          
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";

		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;

		$tname='l';
				
		$location_id = intval($GLOBALS['request']['lid']);
		$table_id = intval($GLOBALS['request']['tid']);
		
		$dclocation=$GLOBALS['db']->getRow("select id,name,is_dc,is_reserve,is_close from ".DB_PREFIX."supplier_location where id=".$location_id);

		$root=array();
		if($dclocation)
		{	
			$is_colloect=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']." and user_id=".$user_id);
			if($is_colloect>0){
				$dclocation['is_collected']=1;
			}else{
				$dclocation['is_collected']=0;
			}

			//关于分类信息与seo
			$page_title = $dclocation['name'];
			$page_keyword = $dclocation['name'];
			$page_description = $dclocation['name'];
		
			
			$table_info=$GLOBALS['db']->getAll("select id,name,price from ".DB_PREFIX."dc_rs_item where is_effect=1 and location_id=".$location_id." order by sort desc");
			if($table_id==0){
				$table_id=$table_info[0]['id'];
			}
			$table_data=$this->mapi_get_rs_item($table_id);
			
			$table_info_t=data_format_idkey($table_info,$key='id');
			$table_now=$table_info_t[$table_id];
			
			
			$user_info=$GLOBALS['db']->getRow("select consignee,mobile from ".DB_PREFIX."dc_consignee where user_id=".$user_id." and is_main=1");
			$root['user_info']=$user_info?$user_info:array();

			$root['is_has_location']=1;
			$root['page_title']=$page_title;
			$root['page_keyword']=$page_keyword;
			$root['page_description']=$page_description;
			$root['table_info']=$table_info;
			$root['table_now']=$table_now;
			$root['time_info']=$table_data;
			$root['dclocation']=$dclocation;
			
			output($root);
		}
		else
		{	
			$root['is_has_location']=0;
			output($root);
		}
		
		
	}
	
	/**
	 * @param $location_id门店的id
	 * @param $table_id 预订座位的信息
	 * @return array('list'=>$rs_list); 1.计算出这天有多少桌子可以预定 或者今天 余下的时间还有多少桌子可以预定
	 */
	
	public function mapi_get_rs_item($table_id){
		 
		require_once APP_ROOT_PATH."system/model/dc.php";
		$sql = "select * from ".DB_PREFIX."dc_rs_item where is_effect = 1 and id = ".$table_id." order by sort desc";
		$rs_list = $GLOBALS['db']->getRow($sql,false);
		//$rs_list=data_format_idkey($rs_list,$key='id');
	
		$rs_time_list = $GLOBALS['db']->getAll("select id,total_count,rs_time,item_id from ".DB_PREFIX."dc_rs_item_time where is_effect = 1 and total_count > 0 and item_id=".$table_id);	//营业时间 和现在时间 分割对比
		$rs_time_list=data_format_idkey($rs_time_list,$key='id');
	
		$arr_d=array();
		foreach($rs_time_list as $kk=>$vv){
			if(!in_array($vv['id'], $arr_d)){
				$arr_d[]=$vv['id'];
			}
			
			$rs_time_list[$kk]['rs_time']=to_date(to_timespan($vv['rs_time']),"H:i");
		}
	
		$table_data=array();
		$table_data['table_info']=$rs_list;
		 
		//获得7天的内的座位库存信息
		$begin_time=to_date(NOW_TIME,"Y-m-d");
		$end_time= to_date(to_timespan($begin_time)+3600*24*7,"Y-m-d");
	
		for($i=to_timespan($begin_time);$i<=to_timespan($end_time);$i+=3600*24){
			$table_data['time_info'][to_date($i,"Y-m-d")]['date_info']=to_date($i,"Y-m-d");
			$table_data['time_info'][to_date($i,"Y-m-d")]['table_info']=$rs_time_list;
		}
	
		//库存
		 $table_stock=$GLOBALS['db']->getAll("select id,buy_count,time_id,rs_time,rs_date from ".DB_PREFIX."dc_rs_item_day where time_id in (".implode(',',$arr_d).") and rs_date between '".$begin_time."' and '".$end_time."'");
		//return $table_stock=data_format_idkey($table_stock,$key='time_id');
		foreach($table_stock as $xl=>$zl){
			if($table_data['time_info'][$zl['rs_date']]['table_info'][$zl['time_id']]['total_count']-$zl['buy_count']<=0){
				unset($table_data['time_info'][$zl['rs_date']]['table_info'][$zl['time_id']]);
			}
		}
		 
		$now_time=NOW_TIME+3600;//延后1个小时
	
		foreach($table_data['time_info'][$begin_time]['table_info'] as $kk=>$vv){
			if(to_timespan($vv['rs_time'])<$now_time){
				unset($table_data['time_info'][$begin_time]['table_info'][$kk]);
			}
		}
		
		//去掉没有座位的信息
		foreach($table_data['time_info'] as $aa=>$bb){
			if(count($table_data['time_info'][$aa]['table_info'])==0){
				unset($table_data['time_info'][$aa]);	
			}	
		}
		ksort($table_data['time_info']);
		
		$table_data['time_info']=array_values($table_data['time_info']);
		
		foreach($table_data['time_info'] as $ka=>$kb){	
			$table_data['time_info'][$ka]['table_info']=data_format_idkey($table_data['time_info'][$ka]['table_info'],$key='rs_time');
		}
		
		foreach($table_data['time_info'] as $ka=>$kb){
			ksort($table_data['time_info'][$ka]['table_info']);
		}
		
		
		foreach($table_data['time_info'] as $ka=>$kb){
			$table_data['time_info'][$ka]['table_info']=array_values($kb['table_info']);
		}

		return $table_data['time_info'];
	}
	
	

	
}
?>