<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dealModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data_id = intval($_REQUEST['data_id']);
		if($data_id==0)
			$data_id = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where uname = '".strim($_REQUEST['data_id'])."'"));
		
		$data = request_api("deal","index",array("data_id"=>$data_id,"type"=>1));

		if(intval($data['id'])==0)
		{
			app_redirect(wap_url("index"));
		}
		//是否存在关联商品
		$relate_data = $data['relate_data'];
		if($relate_data){
			//把主产品加入 relate_data
			$newGoodsList = array();
			foreach( $relate_data['goodsList'] as $k=>$item ){
				if( intval($item['id'])!=$data_id ){
					$newGoodsList[] = $item;
				}
			}
			//goodsList wap展示为两个商品一组，需要改造一下
			$rsGoodsList = array();
			for( $k=0;$k<ceil(count($newGoodsList)/2);$k++ ){
				$item1 = $newGoodsList[$k*2];
				$item2 = $newGoodsList[$k*2+1];
				if(!$item2){
					$item1['widthP'] = '50%';
				}else{
					$item1['widthP'] = '100%';
				}
				$rsGoodsList[$k][] = $item1;
				if($item2){
					$item2['widthP'] = '100%';
					$rsGoodsList[$k][] = $item2;
				}			
			}
			$GLOBALS['tmpl']->assign("goodsList",$rsGoodsList);
			$GLOBALS['tmpl']->assign("jsonDeal",json_encode($relate_data['dealArray']));
			$GLOBALS['tmpl']->assign("jsonAttr",json_encode($relate_data['attrArray']));
			$GLOBALS['tmpl']->assign("jsonStock",json_encode($relate_data['stockArray']));
		}
		$hasRelateGoods = !empty($relate_data)?1:0;
		$GLOBALS['tmpl']->assign("hasRelateGoods",$hasRelateGoods);
		
		$GLOBALS['tmpl']->assign("download",url("index","app_download"));
		$GLOBALS['tmpl']->assign("data",$data);		
		
		$GLOBALS['tmpl']->display("deal.html");
	}
	
	public function add_collect(){
	    global_run();
	    init_app_page();
	
	
	    $param=array();
	    $param['id'] = intval($_REQUEST['id']);
	    $data = request_api("deal","add_collect",$param);
	    ajax_return($data);
	}
	
}
?>