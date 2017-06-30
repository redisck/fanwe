<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

$payment_lang = array(
	'name'	=>	'微信支付(WAP版本)',
	'appid'	=>	'微信公众号ID',
	'appsecret'=>'微信公众号SECRT',
	'mchid'	=>	'微信支付MCHID',
  	'partnerid'	=>	'商户ID',
	'partnerkey'	=>	'商户key',
	'key'	=>	'商户支付密钥Key',
	'sslcert'=>'apiclient_cert证书路径',
	'sslkey'=>'apiclient_key证书路径',
	'type'=>'类型(V2或V3)',
	'scan' => '扫码支付',
	'scan_0'=>	'关闭',
	'scan_1'=> 	'开启',
);
$config = array(
	'appid'=>array(
		'INPUT_TYPE'=>'0',
	),//微信公众号ID
	'appsecret'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //微信公众号SECRT
	'mchid'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //微信支付MCHID
	'key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户支付密钥Key
	'scan'	=>	array(
			'INPUT_TYPE'	=>	'1',
			'VALUES'	=> 	array(0,1)
	),
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Wwxjspay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付  2:仅wap支付 3:仅app支付 4:兼容wap和app*/
    $module['online_pay'] = '2';

    /* 配送 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    $module['reg_url'] = '';
    return $module;
}

// 支付宝手机支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
require_once APP_ROOT_PATH."system/dc_payment/Wxjspay/WxPayPubHelper.php";
class Dc_Wwxjspay_payment implements payment {

	public function get_payment_code($payment_notice_id)
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);

		$order_info = $GLOBALS['db']->getRow("select location_name,is_rs,order_sn from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);
		$order_sn = $payment_notice['notice_sn'];
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
			
		if($order_info['is_rs']==1){
			$title_name=$order_info['location_name'].'预订';
		}else{
			$title_name=$order_info['location_name'].'外卖';
		}
		
		if(APP_INDEX=="index")
		{		
			$script = '<script type="text/javascript">$("#wxscan").everyTime(2000,function(){

				var query = new Object();
				query.act = "check_payment_notice";
				query.notice_id = '.$payment_notice_id.';
				$.ajax({
					url:AJAX_URL,
					dataType: "json",
					data:query,
			        type:"POST",
			        global:false,
					success:function(data)
					{
					    if(data.status)			    		   
					    {
					    	$("#wxscan").stopTime();
					    	location.reload();
					    }
					}
				});
				
			});</script>';	
			return "<img id='wxscan' src='".url("index","file#wxpay_qr_code",array("notice_id"=>$payment_notice_id))."' /> <br /> 打开微信扫码，即可支付".$script;
		}
		else
		{	 		
	
			$pay['pay_info'] = $title_name;
			$pay['pay_action'] = SITE_DOMAIN.APP_ROOT."/cgi/payment/wwxjspay/redirect.php?notice_id=".$payment_notice_id."&from=".$GLOBALS['request']['from'];
			$pay['payment_name'] = "微信支付";
			$pay['pay_money'] = $money;
			$pay['class_name'] = "Wwxjspay";
			return $pay;		
		}			
	}
	
	public function get_redirect_url($payment_notice_id)
	{
		$from = strim($_REQUEST['from']);
	
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$order_info = $GLOBALS['db']->getRow("select location_name,is_rs,order_sn from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);

		if($order_info['is_rs']==1){
			$title_name=$order_info['location_name'].'预订';
		}else{
			$title_name=$order_info['location_name'].'外卖';
		}
		
		$order_sn =$order_info['order_sn'];
		$money = round($payment_notice['money'],2);
		$money_fen=intval($money*100);
		
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo,name from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));	
		$payment_info['config'] = unserialize($payment_info['config']);
		$wx_config=$payment_info['config'];
		$data_notify_url = SITE_DOMAIN.APP_ROOT.'/callback/payment/wwxjspay_notify.php';
		$pay_action = SITE_DOMAIN.APP_ROOT."/cgi/payment/wwxjspay/redirect.php?notice_id=".$payment_notice_id."&from=".$from;
		
		
		
		$jsApi = new JsApi_pub();
		$jsApi->update_config($wx_config['appid'],$wx_config['appsecret'],$wx_config['mchid'],$wx_config['partnerid'],$wx_config['partnerkey'],$wx_config['key'],$wx_config['sslcert'],$wx_config['sslkey']);
		
		if (!isset($_REQUEST['code']))
		{
			//触发微信返回code码
			$url = $jsApi->createOauthUrlForCode(urlencode($pay_action));
			app_redirect($url);
		}
		else
		{
			//获取code码，以获取openid
			$code = strim($_REQUEST['code']);		
			$jsApi->setCode($code);
			$openid = $jsApi->getOpenId();				
		}
		
		$unifiedOrder = new UnifiedOrder_pub();
		$unifiedOrder->update_config($wx_config['appid'],$wx_config['appsecret'],$wx_config['mchid'],$wx_config['partnerid'],$wx_config['partnerkey'],$wx_config['key'],$wx_config['sslcert'],$wx_config['sslkey']);
		$unifiedOrder->setParameter("openid",$openid);//商品描述
		
		$unifiedOrder->setParameter("body",iconv_substr($title_name,0,50, 'UTF-8'));//商品描述
		$timeStamp =NOW_TIME;
		
		$unifiedOrder->setParameter("out_trade_no",$payment_notice['notice_sn']);//商户订单号
		$unifiedOrder->setParameter("total_fee",$money_fen);//总金额
		$unifiedOrder->setParameter("notify_url",$data_notify_url);//通知地址
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		
			
		$prepay_id = $unifiedOrder->getPrepayId();
		
			
		//=========步骤3：使用jsapi调起支付============
		$jsApi->setPrepayId($prepay_id);
			
		$jsApiParameters = $jsApi->getParameters();
		
		$html_text = @file_get_contents(APP_ROOT_PATH."system/dc_payment/Wxjspay/pay.html");
		$html_text = str_replace("__jsApiParameters__", $jsApiParameters, $html_text);
		$html_text = str_replace("__pay_url__", SITE_DOMAIN.wap_url("index","dc_payment#done",array("order_id"=>$payment_notice['order_id'],'pay_status'=>1)), $html_text);
// 		var_dump(htmlspecialchars($html_text));exit;


		$html_text = str_replace("__qr_code__", url("index","file#wxpay_qr_code_transapp",array("notice_id"=>$payment_notice['id'],"prepay_id"=>$prepay_id)), $html_text);

		return $html_text;
	
	}
	
	
	public function response($request)
	{	
							
	}
	
	public function notify($request){
		
		//获取配置信息
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name='Wwxjspay'");
		$payment_info['config'] = unserialize($payment_info['config']);
		$wx_config=$payment_info['config'];
		$notify = new Notify_pub();
		$notify->update_config($wx_config['appid'],$wx_config['appsecret'],$wx_config['mchid'],$wx_config['partnerid'],$wx_config['partnerkey'],$wx_config['key'],$wx_config['sslcert'],$wx_config['sslkey']);

			//进入V3
			//存储微信的回调
		
			$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
			$notify->saveData($xml);
			if($notify->checkSign() == FALSE){
				$notify->setReturnParameter("return_code","FAIL");//返回状态码
				$notify->setReturnParameter("return_msg","签名失败");//返回信息
				//$log_->log_result($log_name,"【签名失败】:\n".$xml."\n");
			}else{
				$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
				//$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
				$info=$notify->xmlToArray($xml);
				$trade_no=$info['transaction_id'];
				$order_id = intval($info['order_id']);
				$payment_notice_sn = strim($info['out_trade_no']);
				if ($order_id == 0){
					$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
					$order_id = intval($payment_notice['order_id']);
				}else{
					$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_id);
				}
		
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);
				require_once APP_ROOT_PATH."system/model/dc.php";
				$rs = dcpayment_paid($payment_notice['id']);
				if ($rs ==1 || $rs ==2)
				{
					//file_put_contents(APP_ROOT_PATH."/alipaylog/1.txt","");
					$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set outer_notice_sn = '".$trade_no."' where id = ".$payment_notice['id']);
					dcorder_paid($payment_notice['order_id']);
					//echo "验证成功<br />";
				}
			}
			$returnXml = $notify->returnXml();
		
			echo $returnXml;
		
	}
	
	public function get_display_code(){
		return "微信支付";
	}
	
	
	public function get_web_display_code()
	{
		$payment_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name='Wwxjspay'");
		if($payment_item)
		{
		    $html = "<label class='ui-radiobox' rel='payment_rdo'><input type='radio' name='payment' value='".$payment_item['id']."' />";
		    
// 			if($payment_item['logo']!='')
// 			{
// 			    $html = "<label class='ui-radiobox' style='background:url(".APP_ROOT.$payment_item['logo'].") 15px 50% no-repeat' rel='payment_rdo'><input type='radio' name='payment' value='".$payment_item['id']."' />";
// 			    $html .= "<span class='f_l' style='padding-left: 50px;'>微信扫码支付</span>";
// 			}
		    $html .= "微信扫码支付";
			
			$html.="</label>";
			return $html;
		}
		else
		{
			return '';
		}
	}
}
?>