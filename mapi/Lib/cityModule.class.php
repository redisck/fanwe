<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class cityModule extends MainBaseModule
{
	
	/**
	 * 城市列表展示接口
	 * 
	 * 输入：
	 * 
	 * 
	 * 输出：
	 * page_title:string 页面标题
	 * hot_city:array 热门城市
	 * Array(
	 * 		Array(
	 * 			[id] => int 城市ID
	 * 			[name] => string 城市名称
	 * 		)
	 * )
	 * city_list:array:array:array 城市列表，结构如下
	 * Array
        (
            [B] => Array
                (
                    [0] => Array
                        (
                            [id] => 18
                            [name] => 北京
                            [zm] => B
                        )
                )

            [F] => Array
                (
                    [0] => Array
                        (
                            [id] => 15
                            [name] => 福州                            
                            [zm] => F
                        )
                )

            [S] => Array
                (
                    [0] => Array
                        (
                            [id] => 19
                            [name] => 上海                            
                            [zm] => S
                        )
                )
        ) 
	 * 
	 */
	public function index()
	{
	
		$root = array();
		
		$city_list=load_auto_cache("city_list_result");
		$city_list=$city_list['zm'];
		$root['city_list'] = $city_list?$city_list:array();
		
		$hot_city = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_city where pid>0 and is_effect = 1 and is_hot = 1 order by uname asc");
		$root['hot_city'] = $hot_city;

		
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="城市切换";
		
		output($root);
	}
	
	

	
	
	/**
	 *城市切换接口,切换后跳回首页
	 * 
	 * 输入： 
	 * city_id: int 城市ID
	 * 
	 * 输出：
	 * 
	 */
	public function city_change()
	{
		$root = array();
		output($root);
	}
	
}
?>