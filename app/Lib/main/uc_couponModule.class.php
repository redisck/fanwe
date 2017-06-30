<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_couponModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
	
		$did = intval($_REQUEST['did']);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = $GLOBALS['user_info']['id'];
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		$order_item_table = get_user_order_item_table_name($user_id);
		$order_table = get_user_order_table_name($user_id);
		if($did>0)
		{
			$order_deal_item = $GLOBALS['db']->getRow("select doi.* from ".$order_item_table." as doi left join ".$order_table." as do on doi.order_id = do.id where doi.id = ".$did." and doi.is_coupon = 1 and do.user_id = ".$user_id);
			$deal = load_auto_cache("deal",array("id"=>$order_deal_item['deal_id']));
			$order_deal_item['url'] = $deal['url'];
		}
		
		if($order_deal_item)
		{
			$sql = "select doi.sub_name,doi.name,doi.number,c.* from ".DB_PREFIX."deal_coupon as c left join ".
					$order_item_table." as doi on doi.id = c.order_deal_id where c.is_valid > 0 and ".
					" c.user_id = ".$user_id." and doi.id = ".$order_deal_item['id']." order by c.id desc limit ".$limit;
			$sql_count = "select count(*) from ".DB_PREFIX."deal_coupon as c where c.is_valid > 0 and ".
					" c.user_id = ".$user_id." and c.order_deal_id = ".$order_deal_item['id'];
			$GLOBALS['tmpl']->assign("deal",$order_deal_item);
		}
		else
		{
			$sql = "select doi.sub_name,doi.name,doi.number,c.* from ".DB_PREFIX."deal_coupon as c left join ".
					DB_PREFIX."deal_order_item as doi on doi.id = c.order_deal_id where c.is_valid > 0 and ".
					" c.user_id = ".$user_id." order by c.id desc limit ".$limit;
			$sql_count = "select count(*) from ".DB_PREFIX."deal_coupon as c where c.is_valid > 0 and ".
					" c.user_id = ".$user_id;
		}
	
		$list = $GLOBALS['db']->getAll($sql);

		foreach($list as $k=>$v)
		{
			$list[$k]['deal'] = load_auto_cache("deal",array("id"=>$v['deal_id']));
		}
		$count = $GLOBALS['db']->getOne($sql_count);
	
		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
		
		$GLOBALS['tmpl']->assign("page_title","我的团购券");
		assign_uc_nav_list();
		$GLOBALS['tmpl']->display("uc/uc_coupon_index.html");
	}
	
	public function send()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			$t = strim($_REQUEST['t']);
			$id = intval($_REQUEST['id']);
			$coupon = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$id." and user_id = ".$GLOBALS['user_info']['id']." and is_valid = 1");
			$deal_info = load_auto_cache("deal",array("id"=>$coupon['deal_id']));
			if($coupon)
			{
				if($coupon['refund_status']==1)
				{
					$data['status'] = 0;
					$data['info'] = "团购券退款审核中";
					ajax_return($data);
				}
				elseif($coupon['refund_status']==2)
				{
					$data['status'] = 0;
					$data['info'] = "团购券已退款";
					ajax_return($data);
				}
				elseif($coupon['confirm_time']>0)
				{
					$data['status'] = 0;
					$data['info'] = "团购券已使用";
					ajax_return($data);
				}
				elseif($coupon['end_time']>0&&$coupon['end_time']<NOW_TIME)
				{
					$data['status'] = 0;
					$data['info'] = "团购券已过期";
					ajax_return($data);
				}
				else
				{
					if($t=="sms")
					{
						if($deal_info['forbid_sms']==1||app_conf("SMS_ON")==0||app_conf("SMS_SEND_COUPON")==0)
						{
							$data['status'] = 0;
							$data['info'] = "不支持短信发送";
							ajax_return($data);
						}
						elseif($coupon['sms_count']>=app_conf("SMS_COUPON_LIMIT"))
						{
							$data['status'] = 0;
							$data['info'] = "短信发送已超过".app_conf("SMS_COUPON_LIMIT")."次";
							ajax_return($data);
						}
						elseif($GLOBALS['user_info']['mobile']=="")
						{
							$data['status'] = 0;
							$data['info'] = "请先设置手机号";
							$data['jump'] = url("index","uc_account");
							ajax_return($data);
						}
						else
						{
							send_deal_coupon_sms($id);
							$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set sms_count = sms_count + 1 where id = ".$id);
							$data['status'] = 1;
							$data['info'] = "短信成功发送到".$GLOBALS['user_info']['mobile']."，请注意查收。";
							ajax_return($data);
						}
						
					}
					elseif($t=="mail")
					{
						if(app_conf("MAIL_ON")==0||app_conf("MAIL_SEND_COUPON")==0)
						{
							$data['status'] = 0;
							$data['info'] = "不支持邮件发送";
							ajax_return($data);
						}
						elseif($coupon['mail_count']>=app_conf("MAIL_COUPON_LIMIT"))
						{
							$data['status'] = 0;
							$data['info'] = "邮件发送已超过".app_conf("MAIL_COUPON_LIMIT")."次";
							ajax_return($data);
						}
						elseif($GLOBALS['user_info']['email']=="")
						{
							$data['status'] = 0;
							$data['info'] = "请先设置邮箱";
							$data['jump'] = url("index","uc_account");
							ajax_return($data);
						}
						else
						{
							send_deal_coupon_mail($id);
							$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set mail_count = mail_count + 1 where id = ".$id);
							$data['status'] = 1;
							$data['info'] = "邮件成功发送到".$GLOBALS['user_info']['email']."，请注意查收。";
							ajax_return($data);
						}
					}
					else
					{
						$data['status'] = 0;
						$data['info'] = "非法操作";
						ajax_return($data);
					}
				}
			}
			else 
			{
				$data['status'] = 0;
				$data['info'] = "团购券不存在";
				ajax_return($data);
			}
		}
	}	
}
?>