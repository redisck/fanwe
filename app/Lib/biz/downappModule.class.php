<?php 
/**
 * 商户手机端应用下载
 * @author jobin.lin
 *
 */
class downappModule extends BizBaseModule{
    public function index(){
        $down_url="";
        //商家app下载地址连接
        if (isios()){
           // $down_url = app_conf("BIZ_APPLE_PATH");
            $down_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'ios_biz_down_url'");
        }else{
            //$down_url = app_conf("BIZ_ANDROID_PATH");
        	$down_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'android_biz_filename'");
        }
        app_redirect($down_url);
    }
}

?>