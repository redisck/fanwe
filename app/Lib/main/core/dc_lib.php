<?php

function dc_global_run()
{
	//刷新外卖订餐购物车
	require_once APP_ROOT_PATH."system/model/dc.php";
	refresh_dccart_list();
}

?>