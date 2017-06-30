<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class previewModule extends MainBaseModule
{
	private function is_manage()
	{
		$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));	
		$adm_id = intval($adm_session['adm_id']);
		return $adm_id;
	}
	public function deal()
	{		
		global_run();
		init_app_page();
		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']); //0主表 1提交表
		require_once APP_ROOT_PATH."system/model/deal.php";
		if($type==0)
		{
			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$id);		
			if($deal)
			{
				//团购图片集
				$img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".intval($deal['id'])." order by sort asc");
				
				if($img_list)
				{
					$img_list[0]['current'] = 1;
					$deal['image_list'] = $img_list;
					$deal['icon'] = $img_list[0]['img'];
				}
				if(count($deal['image_list'])<=1)
				{
					unset($deal['image_list']);
				}
				
				//开始输出库存json
				$attr_stock_list =$GLOBALS['db']->getAll("select * from ".DB_PREFIX."attr_stock where deal_id = ".$deal['id'],false);
				$attr_stock_data = array();
				foreach($attr_stock_list as $row)
				{
					$row['attr_cfg'] = unserialize($row['attr_cfg']);
					$attr_stock_data[$row['attr_key']] = $row;
				}
				$deal['deal_attr_stock_json'] = json_encode($attr_stock_data);
				
				//规格属性选择
				$deal_attr = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."goods_type_attr where goods_type_id = ".$deal['deal_goods_type']);
				foreach($deal_attr as $k=>$v)
				{
					$deal_attr[$k]['attr_list'] = $GLOBALS['db']->getAll("select id,name,price from ".DB_PREFIX."deal_attr where deal_id = ".$deal['id']." and goods_type_attr_id = ".$v['id']);
				}
				$deal['deal_attr'] = $deal_attr;
				
				//折扣
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,1);
				
				$deal['discount'] = round($deal['discount'],2);

			}
		}
		else
		{
			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where id = ".$id);		
			if($deal)
			{
				//图集
				$img_list_rs = unserialize($deal['cache_focus_imgs']);
				foreach($img_list_rs as $k=>$v)
				{
					$img_list[] = array(
						"img"=>$v	
					);
				}				
				if($img_list)
				{
					$img_list[0]['current'] = 1;
					$deal['image_list'] = $img_list;
					$deal['icon'] = $img_list[0]['img'];
				}
				if(count($deal['image_list'])<=1)
				{
					unset($deal['image_list']);
				}
				
				
				
				//开始输出库存json
				$attr_stock = unserialize($deal['cache_attr_stock']);
				foreach($attr_stock as $k=>$v)
				{
					$cfg = unserialize($v['attr_cfg']);
					$attr_key = array();
					foreach ($cfg as $kk=>$vv)
					{
						$attr_key[] = $vv;
					}		
					sort($attr_key);					
					$attr_stock[$k]['attr_key'] = implode("_", $attr_key);
				}
				$attr_stock_data = array();
				foreach($attr_stock as $row)
				{
					$row['attr_cfg'] = unserialize($row['attr_cfg']);
					$row['buy_count'] = $GLOBALS['db']->getOne("select buy_count from ".DB_PREFIX."attr_stock where attr_str = '".$row['attr_str']."'");
					$attr_stock_data[$row['attr_key']] = $row;
				}				
				$deal['deal_attr_stock_json'] = json_encode($attr_stock_data);
				
				//规格属性选择
				$deal_attr = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."goods_type_attr where goods_type_id = ".$deal['deal_goods_type']);
				$cache_deal_attr = unserialize($deal['cache_deal_attr']);
				foreach($deal_attr as $k=>$v)
				{
					foreach($cache_deal_attr as $kk=>$vv)
					{
// 						$deal_attr[$k]['attr_list'] = $GLOBALS['db']->getAll("select id,name,price from ".DB_PREFIX."deal_attr where deal_id = ".$deal['id']." and goods_type_attr_id = ".$v['id']);
						$vv['id'] = $vv['name'];
						if($v['id']==$vv['goods_type_attr_id'])
						$deal_attr[$k]['attr_list'][] = $vv;
					}
				}
				$deal['deal_attr'] = $deal_attr;
				
				//折扣
				if($deal['origin_price']>0&&floatval($deal['discount'])==0)
					$deal['discount'] = round(($deal['current_price']/$deal['origin_price'])*10,1);
				
				$deal['discount'] = round($deal['discount'],2);
				

			}
			
		}
		if($deal)
		{	
			if(!$this->is_manage())
			{
				$account_info = es_session::get('account_info');
				if($deal['supplier_id']!=$account_info['supplier_id'])
				{
					app_redirect(url("index"));
				}				
			}
			//开始解析商品标签
			for($tt=0;$tt<10;$tt++)
			{
				if(($deal['deal_tag']&pow(2,$tt))==pow(2,$tt))
				{
					$deal['deal_tags'][] = $tt;
				}
			}
			
			if($deal['is_shop']==1)
			{
				if($deal['buy_type']==1)
					$GLOBALS['tmpl']->assign("cate_tree_type",2); //积分商城商品下拉菜单加载积分分类
				else
				{
					$GLOBALS['tmpl']->assign("cate_tree_type",1);
				}
				$GLOBALS['tmpl']->assign("search_type",5);
			}		
	
			
			
			//$GLOBALS['tmpl']->assign("drop_nav","no_drop"); //首页下拉菜单不输出
			//$GLOBALS['tmpl']->assign("wrap_type","1"); //首页宽屏展示			
			$deal['description'] = format_html_content_image($deal['description'],720);
			$deal['notes'] = format_html_content_image($deal['notes'],720);
			if($deal['notice']==1) //未开团不允许预告的团购，预览状态下可见
			$deal['time_status'] = DEAL_NOTICE; //未开始
			$GLOBALS['tmpl']->assign("deal",$deal);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
			
		
			

			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($deal['supplier_info']['name'])
			{
				$page_title.="[".$deal['supplier_info']['name']."]";
				$page_keyword.=$deal['supplier_info']['name'].",";
				$page_description.=$deal['supplier_info']['name'].",";
			}
			$page_title.= $deal['sub_name'];
			$page_keyword.=$deal['sub_name'].",";
			$page_description.=$deal['sub_name'].",";
			
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));			
			
			if($deal['cate_id'])
			{
				$deal['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']);
				$deal['cate_url'] = url("index","tuan",array("cid"=>$deal['cate_id']));				
			}
			elseif($deal['shop_cate_id'])
			{
				$deal['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".$deal['shop_cate_id']);
				if($deal['buy_type']==1)
				$deal['cate_url'] = url("index","scores",array("cid"=>$deal['shop_cate_id']));
				else
				$deal['cate_url'] = url("index","cate",array("cid"=>$deal['shop_cate_id']));
			}			
			if($deal['cate_name'])
			{
				$page_title.=" - ".$deal['cate_name'];
				$page_keyword.=$deal['cate_name'].",";
				$page_description.=$deal['cate_name'].",";
				$site_nav[] = array('name'=>$deal['cate_name'],'url'=>$deal['cate_url']);
			}
			$site_nav[] = array('name'=>$deal['sub_name'],'url'=>$deal['url']);				
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
			//输出促销
			if($deal['allow_promote']==1)
			{
				$promote = load_auto_cache("cache_promote");
				$GLOBALS['tmpl']->assign("promote",$promote);
			}
			
			if($deal['seo_title'])$page_title = $deal['seo_title'];
			if($deal['seo_keyword'])$page_keyword = $deal['seo_keyword'];
			if($deal['seo_description'])$page_description = $deal['seo_description'];
			
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
			
			$GLOBALS['tmpl']->assign("preview",true);
			$GLOBALS['tmpl']->display("deal.html");
		}
		else
		{
			app_redirect_preview();
		}	
		
	}
	
	
	public function event()
	{
		global_run();
		init_app_page();
		
		$GLOBALS['tmpl']->assign("no_nav",true);
		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']); //0主表 1提交表
		require_once APP_ROOT_PATH."system/model/event.php";
		if($type==0)
		{
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$id);
		}
		else
		{
			$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where id = ".$id);
		}
		
		
		if($event)
		{
				
			if(!$this->is_manage())
			{
				$account_info = es_session::get('account_info');
				if($event['supplier_id']!=$account_info['supplier_id'])
				{
					app_redirect(url("index"));
				}
			}
			

			$event['content'] = format_html_content_image($event['content'],720);
			$event['submitted_data'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where event_id = ".$event['id']." and user_id = '".$GLOBALS['user_info']['id']."'");
		
			$GLOBALS['tmpl']->assign("event",$event);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
		

			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($event['supplier_info']['name'])
			{
				$page_title.="[".$event['supplier_info']['name']."]";
				$page_keyword.=$event['supplier_info']['name'].",";
				$page_description.=$event['supplier_info']['name'].",";
			}
			$page_title.= $event['name'];
			$page_keyword.=$event['name'].",";
			$page_description.=$event['name'].",";
		
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
		
			if($event['cate_id'])
			{
				$event['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event['cate_id']);
				$event['cate_url'] = url("index","events",array("cid"=>$event['cate_id']));
			}
				
			if($event['cate_name'])
			{
				$page_title.=" - ".$event['cate_name'];
				$page_keyword.=$event['cate_name'].",";
				$page_description.=$event['cate_name'].",";
				$site_nav[] = array('name'=>$event['cate_name'],'url'=>$event['cate_url']);
			}
			$site_nav[] = array('name'=>$event['name'],'url'=>$event['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		
		
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
		
			$GLOBALS['tmpl']->assign("preview",true);
			$GLOBALS['tmpl']->display("event.html");
				
		}
		else
		{
			app_redirect_preview();
		}
	}
	

	public function youhui()
	{
		global_run();
		init_app_page();

		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']); //0主表 1提交表
		require_once APP_ROOT_PATH."system/model/youhui.php";
		if($type==0)
		{
			$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$id);
		}
		else
		{
			$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_biz_submit where id = ".$id);
		}
		
		if($youhui)
		{
				
			if(!$this->is_manage())
			{
				$account_info = es_session::get('account_info');
				if($youhui['supplier_id']!=$account_info['supplier_id'])
				{
					app_redirect(url("index"));
				}
			}
				
			$youhui['description'] = format_html_content_image($youhui['description'],720);
			$youhui['use_notice'] = format_html_content_image($youhui['use_notice'],720);
			$GLOBALS['tmpl']->assign("youhui",$youhui);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
		
				
			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($youhui['supplier_info']['name'])
			{
				$page_title.="[".$youhui['supplier_info']['name']."]";
				$page_keyword.=$youhui['supplier_info']['name'].",";
				$page_description.=$youhui['supplier_info']['name'].",";
			}
			$page_title.= $youhui['name'];
			$page_keyword.=$youhui['name'].",";
			$page_description.=$youhui['name'].",";
		
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
		
			if($youhui['deal_cate_id'])
			{
				$youhui['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$youhui['deal_cate_id']);
				$youhui['cate_url'] = url("index","youhuis",array("cid"=>$youhui['deal_cate_id']));
			}
				
			if($youhui['cate_name'])
			{
				$page_title.=" - ".$youhui['cate_name'];
				$page_keyword.=$youhui['cate_name'].",";
				$page_description.=$youhui['cate_name'].",";
				$site_nav[] = array('name'=>$youhui['cate_name'],'url'=>$youhui['cate_url']);
			}
			$site_nav[] = array('name'=>$youhui['name'],'url'=>$youhui['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		
		
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
		
			$GLOBALS['tmpl']->assign("preview",true);
			$GLOBALS['tmpl']->display("youhui.html");
				
		}
		else
		{
			app_redirect_preview();
		}
	}
	
	
	public function store()
	{
		global_run();
		init_app_page();

		
		$id = intval($_REQUEST['id']);
		$type = intval($_REQUEST['type']); //0主表 1提交表
		require_once APP_ROOT_PATH."system/model/supplier.php";
		if($type==0)
		{
			$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$id);
			
			if($store_info)
			{
				//开始输出商户图库数据json
				$store_images = $GLOBALS['db']->getAll("select brief,image from ".DB_PREFIX."supplier_location_images where supplier_location_id = ".$store_info['id']." and status = 1 order by sort limit ".MAX_SP_IMAGE);
				foreach($store_images as $k=>$v)
				{
					$store_images[$k]['image'] = format_image_path(get_spec_image($v['image'],600,450,1));
				}
				$GLOBALS['tmpl']->assign("store_images_json",json_encode($store_images));
				$GLOBALS['tmpl']->assign("store_images_count",count($store_images));
			}
		}
		else
		{
			$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_biz_submit where id = ".$id);
			
			if($store_info)
			{
				//开始输出商户图库数据json
				$store_images_rs = unserialize($store_info['cache_supplier_location_images']);
				foreach($store_images_rs as $k=>$v)
				{
					$store_images[$k]['image'] = format_image_path(get_spec_image($v,600,450,1));
				}
				$GLOBALS['tmpl']->assign("store_images_json",json_encode($store_images));
				$GLOBALS['tmpl']->assign("store_images_count",count($store_images));
			}
		}
		
		if($store_info)
		{

			if(!$this->is_manage())
			{
				$account_info = es_session::get('account_info');
				if($store_info['supplier_id']!=$account_info['supplier_id'])
				{
					app_redirect(url("index"));
				}
			}
			
			$store_info['good_rate_precent'] = round($store_info['good_rate']*100,1);
			$store_info['ref_avg_price'] = round($store_info['ref_avg_price'],2);
			$store_info['brief'] = format_html_content_image($store_info['brief'],720);
			$GLOBALS['tmpl']->assign("store_info",$store_info);
				
				
			
				
				
			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			$page_title.= $store_info['name'];
			$page_keyword.=$store_info['name'].",";
			$page_description.=$store_info['name'].",";
		
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
		
			if($store_info['deal_cate_id'])
			{
				$store_info['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$store_info['deal_cate_id']);
				$store_info['cate_url'] = url("index","stores",array("cid"=>$store_info['deal_cate_id']));
			}
			if($store_info['cate_name'])
			{
				$page_title.=" - ".$store_info['cate_name'];
				$page_keyword.=$store_info['cate_name'].",";
				$page_description.=$store_info['cate_name'].",";
				$site_nav[] = array('name'=>$store_info['cate_name'],'url'=>$store_info['cate_url']);
			}
			$site_nav[] = array('name'=>$store_info['name'],'url'=>$store_info['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		
		
			if($store_info['seo_title'])$page_title = $store_info['seo_title'];
			if($store_info['seo_keyword'])$page_keyword = $store_info['seo_keyword'];
			if($store_info['seo_description'])$page_description = $store_info['seo_description'];
		
			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);				

		}
		else
		{
			app_redirect_preview();
		}
		$GLOBALS['tmpl']->assign("preview",true);
		$GLOBALS['tmpl']->display("store.html");
	}
	
}
?>