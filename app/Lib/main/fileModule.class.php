<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class fileModule extends MainBaseModule
{
	
	/**
	 * 通用上传，上传到attachments目录，按日期划分
	 * 错误返回 error!=0,message错误消息, error=1000表示未登录
	 * 正确时返回 error=0, url: ./public格式的文件相对路径  path:物理路径 name:文件名
	 */
	public function upload()
	{	
		global_run();
		
		if(empty($GLOBALS['user_info']))
		{
			$data['error'] = 1000;  //未登录
			$data['msg'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		
		//上传处理
		//创建comment目录
		if (!is_dir(APP_ROOT_PATH."public/attachment")) { 
	             @mkdir(APP_ROOT_PATH."public/attachment");
	             @chmod(APP_ROOT_PATH."public/attachment", 0777);
	        }
		
	    $dir = to_date(NOW_TIME,"Ym");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	        
	    $dir = $dir."/".to_date(NOW_TIME,"d");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	     
	    $dir = $dir."/".to_date(NOW_TIME,"H");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	        
	    if(app_conf("IS_WATER_MARK")==1)
	    $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),1,1);
	    else
		$img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),0,1);	
		if(intval($img_result['error'])!=0)	
		{
			ajax_return($img_result);
		}
		else 
		{
			if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
        	{
        		syn_to_remote_image_server($img_result['file']['url']);
        	}
			
		}	
		
		$data_result['error'] = 0;
		$data_result['url'] = $img_result['file']['url'];
		$data_result['path'] = $img_result['file']['path'];
		$data_result['name'] = $img_result['file']['name'];
		ajax_return($data_result);
		
	}
	
	
	/**
	 * 分享点评的上传，上传到comment目录，按日期划分
	 * 错误返回 error!=0,message错误消息, error=1000表示未登录
	 * 正确时返回 error=0, url: ./public格式的文件相对路径  path:物理路径 name:文件名
	 * thumb->preview 100x100的小图 url,path
	 */
	public function upload_topic()
	{

		global_run();
	
		if(empty($GLOBALS['user_info']))
		{
			$data['error'] = 1000;  //未登录
			$data['msg'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
	
		//上传处理
		//创建comment目录
		if (!is_dir(APP_ROOT_PATH."public/comment")) {
			@mkdir(APP_ROOT_PATH."public/comment");
			@chmod(APP_ROOT_PATH."public/comment", 0777);
		}
	
		$dir = to_date(NOW_TIME,"Ym");
		if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
			@mkdir(APP_ROOT_PATH."public/comment/".$dir);
			@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		}
		 
		$dir = $dir."/".to_date(NOW_TIME,"d");
		if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
			@mkdir(APP_ROOT_PATH."public/comment/".$dir);
			@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		}
	
		$dir = $dir."/".to_date(NOW_TIME,"H");
		if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
			@mkdir(APP_ROOT_PATH."public/comment/".$dir);
			@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
		}
		 
		if(app_conf("IS_WATER_MARK")==1)
			$img_result = save_image_upload($_FILES,"file","comment/".$dir,$whs=array('preview'=>array(50,50,1,0)),1,1);
		else
			$img_result = save_image_upload($_FILES,"file","comment/".$dir,$whs=array('preview'=>array(50,50,1,0)),0,1);
		if(intval($img_result['error'])!=0)
		{
			ajax_return($img_result);
		}
		else
		{
			if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
			{
				syn_to_remote_image_server($img_result['file']['url']);
				syn_to_remote_image_server($img_result['file']['thumb']['preview']['url']);
			}
				
		}
	
		$data_result['error'] = 0;
		$data_result['url'] = $img_result['file']['url'];
		$data_result['path'] = $img_result['file']['path'];
		$data_result['name'] = $img_result['file']['name'];
		$data_result['thumb'] = $img_result['file']['thumb'];
		
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$info = $image->getImageInfo($img_result['file']['path']);
		
		$image_data['width'] = intval($info[0]);
		$image_data['height'] = intval($info[1]);
		$image_data['name'] = valid_str($_FILES['file']['name']);
		$image_data['filesize'] = filesize($img_result['file']['path']);
		$image_data['create_time'] = NOW_TIME;
		$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
		$image_data['user_name'] = strim($GLOBALS['user_info']['user_name']);
		$image_data['path'] = $img_result['file']['thumb']['preview']['url'];
		$image_data['o_path'] = $img_result['file']['url'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);
		
		$data_result['id'] = intval($GLOBALS['db']->insert_id());
		
		ajax_return($data_result);
	
	}
	
	/**
	 * 上传头像， 错误返回 error!=0,message错误消息 error=1000表示未登录
	 * 正确时返回error = 0, small_url,middle_url,big_url
	 */
	function upload_avatar(){
		global_run();
		
		if(empty($GLOBALS['user_info']))
		{
			$data['error'] = 1000;  //未登录
			$data['msg'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		
		//创建avatar临时目录
		if (!is_dir(APP_ROOT_PATH."public/avatar")) {
			@mkdir(APP_ROOT_PATH."public/avatar");
			@chmod(APP_ROOT_PATH."public/avatar", 0777);
		}
		if (!is_dir(APP_ROOT_PATH."public/avatar/temp")) {
			@mkdir(APP_ROOT_PATH."public/avatar/temp");
			@chmod(APP_ROOT_PATH."public/avatar/temp", 0777);
		}
		$upd_id = $id = intval($GLOBALS['user_info']['id']);
	
		if (is_animated_gif($_FILES['file']['tmp_name']))
		{
			$rs = save_image_upload($_FILES,"file","avatar/temp",$whs=array());
				
			$im = get_spec_gif_anmation($rs['file']['path'],48,48);
			$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_small.jpg";
			file_put_contents($file_name,$im);
			$img_result['file']['thumb']['small']['path'] = $file_name;
	
			$im = get_spec_gif_anmation($rs['file']['path'],120,120);
			$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_middle.jpg";
			file_put_contents($file_name,$im);
			$img_result['file']['thumb']['middle']['path'] = $file_name;
	
			$im = get_spec_gif_anmation($rs['file']['path'],200,200);
			$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_big.jpg";
			file_put_contents($file_name,$im);
			$img_result['file']['thumb']['big']['path'] = $file_name;
		}
		else{
			$img_result = save_image_upload($_FILES,"file","avatar/temp",$whs=array('small'=>array(48,48,1,0),'middle'=>array(120,120,1,0),'big'=>array(200,200,1,0)));
		}
		
		
		if(intval($img_result['error'])!=0)
		{
			ajax_return($img_result);
		}
			
		//开始移动图片到相应位置
	
		$uid = sprintf("%09d", $id);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$path = $dir1.'/'.$dir2.'/'.$dir3;
	
		//创建相应的目录
		if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1)) {
			@mkdir(APP_ROOT_PATH."public/avatar/".$dir1);
			@chmod(APP_ROOT_PATH."public/avatar/".$dir1, 0777);
		}
		if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2)) {
			@mkdir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2);
			@chmod(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2, 0777);
		}
		if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3)) {
			@mkdir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3);
			@chmod(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3, 0777);
		}
	
		$id = str_pad($id, 2, "0", STR_PAD_LEFT);
		$id = substr($id,-2);
		$avatar_file_big = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_big.jpg";
		$avatar_file_middle = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_middle.jpg";
		$avatar_file_small = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_small.jpg";
	
	
		@file_put_contents($avatar_file_big, file_get_contents($img_result['file']['thumb']['big']['path']));
		@file_put_contents($avatar_file_middle, file_get_contents($img_result['file']['thumb']['middle']['path']));
		@file_put_contents($avatar_file_small, file_get_contents($img_result['file']['thumb']['small']['path']));
		@unlink($img_result['file']['thumb']['big']['path']);
		@unlink($img_result['file']['thumb']['middle']['path']);
		@unlink($img_result['file']['thumb']['small']['path']);
		@unlink($img_result['file']['path']);
		
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{			
			syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_big.jpg");
			syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_middle.jpg");
			syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_small.jpg");
		}
	
		//上传成功更新用户头像的动态缓存
		update_avatar($upd_id);
		$data['error'] = 0;
		$data['small_url'] = get_user_avatar($upd_id,"small");
		$data['middle_url'] = get_user_avatar($upd_id,"middle");
		$data['big_url'] = get_user_avatar($upd_id,"big");
		ajax_return($data);
	}
	
	function qr_code()
	{
		$verify = rand(100000, 999999);
		$url = SITE_DOMAIN.wap_url("index","index",array("sess_id"=>es_session::id(),"sess_verify"=>$verify));
		es_session::set("sess_verify", $verify);
		$GLOBALS['tmpl']->assign("url",$url);
		
		gen_qrcode($url,app_conf("QRCODE_SIZE"),true);
	}
	
	
	function wxpay_qr_code()
	{
		$payment_notice_id = intval($_REQUEST['notice_id']);
		$url = SITE_DOMAIN.APP_ROOT."/cgi/payment/wwxjspay/redirect.php?notice_id=".$payment_notice_id."&from=".$GLOBALS['request']['from'];
		gen_qrcode($url,app_conf("QRCODE_SIZE"),true);
	}
	
	/**
	 * 微信端为跨号支付发起的二维码
	 */
	function wxpay_qr_code_transapp()
	{
		
		$payment_notice_id = intval($_REQUEST['notice_id']);
		$prepay_id = strim($_REQUEST['prepay_id']);

		
		require_once APP_ROOT_PATH."system/payment/Wxjspay/WxPayPubHelper.php";
		require_once APP_ROOT_PATH."system/payment/Wxjspay/WxPay.NativePay.php";
		require_once APP_ROOT_PATH."system/model/cart.php";
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		$payment_notice_id = make_payment_notice($payment_notice['money'],$payment_notice['order_id'],$payment_notice['payment_id']);
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		
		$sql = "select name ".
				"from ".DB_PREFIX."deal_order_item ".
				"where order_id =". intval($payment_notice['order_id']);
		$title_name = $GLOBALS['db']->getOne($sql);
		
		$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
		$money = round($payment_notice['money'],2);
		$money_fen=intval($money*100);
		
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo,name from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
		$wx_config=$payment_info['config'];
		$data_notify_url = SITE_DOMAIN.APP_ROOT.'/callback/payment/wwxjspay_notify.php';

		//二维码
		$notify = new NativePay();
		
		$input = new WxPayUnifiedOrder();
		$input->SetBody(iconv_substr($title_name,0,50, 'UTF-8'));
		$input->SetOut_trade_no($payment_notice['notice_sn']);
		$input->SetTotal_fee($money_fen);
		$input->SetNotify_url($data_notify_url);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($prepay_id);
// 		logger::write(print_r($input,1));
		$result = $notify->GetPayUrl($input);
// 		logger::write(print_r($result,1));
		$url = $result["code_url"];
		gen_qrcode($url,app_conf("QRCODE_SIZE"),true);
	}
	
}
?>