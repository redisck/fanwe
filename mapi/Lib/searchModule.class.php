<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class searchModule extends MainBaseModule
{
	
	/**
	 * 搜索首页
	 * 输入：
	 * 
	 * 输出：hot_kw array 热搜关键字 结构如下
	 *  Array
        (
            '内衣',
            '泳装',
            '比基尼',
            '蕾丝',
         )            
	 * 
	 * 
	 * 
	 */
	public function index()
	{

		$root = array();
		//输出热门关键词
		$hot_kw = app_conf("SHOP_SEARCH_KEYWORD");
		$hot_kw = preg_split("/[ ,]/i",$hot_kw);
		$root['hot_kw'] = $hot_kw?$hot_kw:array();
		
		output($root);
	}
	
}
?>

