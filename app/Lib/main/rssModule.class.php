<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."system/model/city.php";
require_once APP_ROOT_PATH.'system/model/deal.php';
require_once APP_ROOT_PATH.'system/model/youhui.php';
require_once APP_ROOT_PATH.'system/model/event.php';
require_once APP_ROOT_PATH.'system/libs/rss.php';
class rssModule extends MainBaseModule
{
	public function index()
	{	
		$rss = new UniversalFeedCreator();   
		 $rss->useCached(); // use cached version if age<1 hour  
		 $rss->title = app_conf("SHOP_TITLE")." - ".app_conf("SHOP_SEO_TITLE");   
		 $rss->description = app_conf("SHOP_SEO_TITLE"); 
		   
		 //optional  
		 $rss->descriptionTruncSize = 500;  
		 $rss->descriptionHtmlSyndicated = true;  
		   
		 $rss->link = get_domain().APP_ROOT;   
		 $rss->syndicationURL = get_domain().APP_ROOT; 
		 
		 //optional  
		 $image->descriptionTruncSize = 500;  
		 $image->descriptionHtmlSyndicated = true;  

		 //对图片路径的修复
		 if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		 {
		 	$domain = $GLOBALS['distribution_cfg']['OSS_DOMAIN'];
		 }
		 else
		 {
		 	$domain = SITE_DOMAIN.APP_ROOT;
		 }
		 
		$city = City::locate_city();
		$city_id = $city['id'];
		
		 $tuan_list = get_deal_list(10,array(DEAL_ONLINE),array("cid"=>0,"city_id"=>0),'',"  is_shop = 0 and is_effect =1 and is_delete = 0 and buy_type <> 1"," create_time desc ");
		 $tuan_list = $tuan_list['list'];
		 foreach($tuan_list as $data) {   
		     $item = new FeedItem();   

		     $gurl = url("index","deal#".$data['id']);
		     $data['url'] = $gurl;
		     $item->title = msubstr($data['name'],0,30);   
		     $item->link = get_domain().$data['url'];  
		
		     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
		     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
		        
		     $data['img'] = str_replace("./public/",$domain."/public/",$data['img']);
		     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
		       
		     //optional  
		     $item->descriptionTruncSize = 500;  
		     $item->descriptionHtmlSyndicated = true;  
		
		     if($data['end_time']!=0)
		     $item->date = date('r',$data['end_time']);   
		     $item->source = $data['url'];   
		     $item->author = app_conf("SHOP_TITLE"); 
		        
		     $rss->addItem($item);   
		 }
		 
		
		 $deal_list = get_deal_list(10,array(DEAL_ONLINE),array("cid"=>0,"city_id"=>0),'',"  is_shop = 1 and is_effect =1 and is_delete = 0 and buy_type <> 1"," create_time desc ");
		 $deal_list = $deal_list['list'];
		 foreach($deal_list as $data) {   
		     $item = new FeedItem();   

		     $gurl = url("index","deal#".$data['id']);
		     $data['url'] = $gurl;
		     $item->title = msubstr($data['name'],0,30);   
		     $item->link = get_domain().$data['url'];  
		
		     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
		     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
		        
		     $data['img'] = str_replace("./public/",$domain."/public/",$data['img']);
		     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
		       
		     //optional  
		     $item->descriptionTruncSize = 500;  
		     $item->descriptionHtmlSyndicated = true;  
		
		     if($data['end_time']!=0)
		     $item->date = date('r',$data['end_time']);   
		     $item->source = $data['url'];   
		     $item->author = app_conf("SHOP_TITLE");		  
		        
		     $rss->addItem($item);   
		 }


		 $youhui_list = get_youhui_list(10,array(YOUHUI_ONLINE),array("cid"=>0,"city_id"=>0),'',"  is_effect =1 "," create_time desc ");
		 $youhui_list = $youhui_list['list'];
		 foreach($youhui_list as $data) {   
		     $item = new FeedItem();   

		     $gurl = url("index","youhui#".$data['id']);
		     $data['url'] = $gurl;
		     $item->title = msubstr($data['name'],0,30);   
		     $item->link = get_domain().$data['url'];  
		
		     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
		     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
		        
		     $data['img'] = str_replace("./public/",$domain."/public/",$data['icon']);
		     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
		       
		     //optional  
		     $item->descriptionTruncSize = 500;  
		     $item->descriptionHtmlSyndicated = true;  
		
		     if($data['end_time']!=0)
		     $item->date = date('r',$data['end_time']);   
		     $item->source = $data['url'];   
		     $item->author = app_conf("SHOP_TITLE"); 
		        
		     $rss->addItem($item);   
		 }
		 
		 
		 $event_list = get_event_list(10,array(EVENT_ONLINE),array("cid"=>0,"city_id"=>0),'',"  is_effect =1 "," sort asc ");
		 $event_list = $event_list['list'];
		 foreach($event_list as $data) {   
		     $item = new FeedItem();   

		     $gurl = url("index","event#".$data['id']);
		     $data['url'] = $gurl;
		     $item->title = msubstr($data['name'],0,30);   
		     $item->link = get_domain().$data['url'];  
		
		     $data['description'] = str_replace($GLOBALS['IMG_APP_ROOT']."./public/",$domain."/public/",$data['description']);	
		     $data['description'] = str_replace("./public/",$domain."/public/",$data['description']);
		        
		     $data['img'] = str_replace("./public/",$domain."/public/",$data['icon']);
		     $item->description =  "<img src='".$data['img']."' /><br />".$data['brief']."<br /> <a href='".get_domain().$data['url']."' target='_blank' >".$GLOBALS['lang']['VIEW_DETAIL']."</a>";   
		       
		     //optional  
		     $item->descriptionTruncSize = 500;  
		     $item->descriptionHtmlSyndicated = true;  
		
		     if($data['end_time']!=0)
		     $item->date = date('r',$data['end_time']);   
		     $item->source = $data['url'];   
		     $item->author = app_conf("SHOP_TITLE"); 
		        
		     $rss->addItem($item);   
		 }		 
		 
		 $rss->saveFeed($format="RSS0.91", $filename=APP_ROOT_PATH."public/runtime/app/tpl_caches/rss.xml");	
		 
	}
	
	

	
	
}
?>