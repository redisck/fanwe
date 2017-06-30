<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class DealAction extends CommonAction{
	public $page = 1;
	
	private $RELATE_GOODS_NUM = 6;//可以关联商品的个数
	
	public function __construct(){
		parent::__construct();
		
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		if(isset($_REQUEST['isajax'])){
			$this->isajax = (int)$_REQUEST['isajax'] > 0 ? 1 : 0;
		}
		if(isset($_REQUEST['page'])){
			$this->page = max(1,(int)$_REQUEST['page']);
		}
		$this->assign('pager_num_now',$this->page);
	}
	
	public function index()
	{
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(strim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.strim($_REQUEST['name']).'%');			
		}

		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		

		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['cate_id'] = array("in",$cate_ids);
		}
		
		
		if(strim($_REQUEST['supplier_name'])!='')
		{
			if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier"))<50000)
			$sql  ="select group_concat(id) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['supplier_name'])."%'";
			else 
			{
				$kws_div = div_str(trim($_REQUEST['supplier_name']));
				foreach($kws_div as $k=>$item)
				{
					$kw[$k] = str_to_unicode_string($item);
				}
				$kw_unicode = implode(" ",$kw);
				$sql = "select group_concat(id) from ".DB_PREFIX."supplier where (match(name_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			}
			$ids = $GLOBALS['db']->getOne($sql);
			$map['supplier_id'] = array("in",$ids);
		}
		$map['publish_wait'] = 0;
		$map['is_shop'] = 0;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',0);
		
		
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//对于商户请求操作
		$id = intval($_REQUEST['id']);
		$edit_type = intval($_REQUEST['edit_type']);

		if($id>0 && $edit_type==2){//商户申请新增团购
		    unset($data['id']);
		}
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(intval($data['return_score'])<0)
		{
			$this->error("积分返还不能为负数");
		}
		if(floatval($data['return_money'])<0)
		{
			$this->error("现金返还不能为负数");
		}
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		$city_info = M("DealCity")->where("id=".intval($data['city_id']))->find();
		if($city_info['pid']==0){
			$this->error("只能选择城市，不能选择省份");
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据

		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = strim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = strim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = strim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = strim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}

		if($data['coupon_time_type']==1)
		{
			$data['coupon_begin_time'] = 0;
			$data['coupon_end_time'] = 0;
		}
		else
		{
			$data['coupon_day'] = 0;
		}
		
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$log_info = $data['name'];
		$data['create_time'] = NOW_TIME;
		$data['update_time'] = NOW_TIME;
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		
		$deal_tags = $_REQUEST['deal_tag'];
		$deal_tag = 0;
		foreach($deal_tags as $t)
		{
			$t2 = pow(2,$t);
			//根据tag计算免预约
			if($t==1)
			{
				$data['auto_order'] = 1;
			}
			$deal_tag = $deal_tag|$t2;
		}
		$data['deal_tag'] = $deal_tag;
			
		//团购产品都要发券不配送
		$data['is_delivery'] = 0;
		
		$data['auto_order'] = 0;
		$data['any_refund'] = 0;
		$data['expire_refund'] = 0;
		$data['is_refund'] = 0;
		$data['is_lottery'] = 0;
		
		foreach($deal_tags as $t)
		{
			if($t==0)
			{
				$data['is_lottery'] = 1;
			}
			//根据tag计算免预约
			if($t==1)
			{
				$data['auto_order'] = 1;
			}
			//随时退
			if($t==6)
			{
				$data['any_refund'] = 1;
			}
			//过期退
			if($t==5)
			{
				$data['expire_refund'] = 1;
			}
		}
		if($data['any_refund']==1||$data['expire_refund']==1)
		{
			$data['is_refund'] = 1;
		}
		
		if(count($_REQUEST['stock_cfg_num'])>0)
		{
			$data['max_bought'] = -1;
		}
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_add_balance_price = $_REQUEST['deal_add_balance_price'];
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
			
			//开始创建筛选项
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $list;
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						$filter_row = array();
						$filter_row['name'] = $filter_item;
						$filter_row['filter_group_id'] = $filter_group_id;
						M("Filter")->add($filter_row);
					}
				}
			}
		
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $list;
				M("DealCateTypeDealLink")->add($link_data);
			}
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $list;
				M("DealLocationLink")->add($link_data);
			}
			
			//成功提示
			syn_deal_status($list);
			syn_deal_match($list);
			syn_attr_stock_key($list);
			 
			if($id>0 && $edit_type == 2){ //商户提交审核
			    //同步商户数据表
			    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("deal_id"=>$list,"admin_check_status"=>1),"UPDATE","id=".$id);
			}
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"tuan");
			}
			
			//增加商品关联购买
			if(!empty($_REQUEST['relate_goods_id'])){
				$saveArray = array(
					'good_id'		=> intval($list),
					'relate_ids'	=> implode(',', $_REQUEST['relate_goods_id']),
					'is_shop'		=> 0,
				);
				$rs = M('Relate_goods')->add($saveArray);
				//if(!$rs){}
			}
			
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		$this->assign ( 'vo', $vo );
		
		
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		foreach($delivery_list as $k=>$v)
		{
			$delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");			
			$delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();	
		}
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		//输出规格库存的配置
		$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$this->assign("attr_cfg_json",$attr_cfg_json);	
		$this->assign("attr_stock_json",$attr_stock_json);
			
		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',0);
		//关联商品
		$tempInfo = M('Relate_goods')->where('good_id='.$id.' and is_shop=0')->find();
		if( $tempInfo ){
			$relate_goods = array();
			$tmpList = M('Deal')->where('id in ('.$tempInfo['relate_ids'].')')->field('id,name,img')->select();
			foreach($tmpList as $item){
				$durl = url("index","preview#deal",array('id'=>$item['id'],'type'=>0));
				$relate_goods[] = array(
						'id'		=>	$item['id'],
						'name'		=>	$item['name'],
						'url'		=>	$durl,
						'share_url'	=>	SITE_DOMAIN.$durl,
						'img'		=>	$item['img']
				);
			}

			$this->assign("relate_goods",$relate_goods);
		}
		
		$this->display ();
	}
	
	/**
	 * 商户提交数据审核编辑
	 */
	public function biz_apply_edit(){
	    $id = intval($_REQUEST['id']);
	    $condition['is_delete'] = 0;
	    $condition['id'] = $id;
	    $vo = M("DealSubmit")->where($condition)->find();
	    $vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
	    $vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
	    $vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
	    $vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
	    $vo['is_coupon'] = 1;
	    $this->assign ( 'vo', $vo );
	    
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
	    $cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
	    $cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
	    $this->assign("cate_tree",$cate_tree);

	    // 选中子分类
	    $select_sub_cate = implode(",", unserialize($vo['cache_deal_cate_type_id']));
	    //选中门店
	    $select_location = implode(",", unserialize($vo['cache_location_id']));

	    $shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
	    $shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
	    $this->assign("shop_cate_tree",$shop_cate_tree);
	    
	    $brand_list = M("Brand")->findAll();
	    $this->assign("brand_list",$brand_list);
	    
	    
	    
	    //输出团购城市
	    $city_list = M("DealCity")->where('is_delete = 0')->findAll();
	    $city_list = D("DealCity")->toFormatTree($city_list,'name');
	    $this->assign("city_list",$city_list);
	    
	    $supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
	    $this->assign("supplier_info",$supplier_info);
	    
	    $goods_type_list = M("GoodsType")->findAll();
	    $this->assign("goods_type_list",$goods_type_list);
	    
	    //输出图片集
	    //$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
	    $img_list = unserialize($vo['cache_focus_imgs']);

	    $this->assign("img_list",$img_list);
	    
	    
	    $weight_list = M("WeightUnit")->findAll();
	    $this->assign("weight_list",$weight_list);
	    
	    
	    //输出配送方式列表
	    $delivery_list = M("Delivery")->where("is_effect=1")->findAll();
	    foreach($delivery_list as $k=>$v)
	    {
	        $delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");
	        $delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();
	    }
	    $this->assign("delivery_list",$delivery_list);
	    
	    //输出支付方式
	    $payment_list = M("Payment")->where("is_effect=1")->findAll();
	    foreach($payment_list as $k=>$v)
	    {
	        $payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();
	    }
	    $this->assign("payment_list",$payment_list);
	    
	    
	    //输出规格库存的配置
	    //$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
	    // 输出规格库存的配置
            $attr_stock = unserialize($vo['cache_attr_stock']);
	    $attr_cfg_json = "{";
	    $attr_stock_json = "{";
	    
	    foreach($attr_stock as $k=>$v)
	    {
	        $attr_cfg_json.=$k.":"."{";
	        $attr_stock_json.=$k.":"."{";
	        foreach($v as $key=>$vvv)
	        {
	            if($key!='attr_cfg')
	                $attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
	        }
	        $attr_stock_json = substr($attr_stock_json,0,-1);
	        $attr_stock_json.="},";
	        	
	        $attr_cfg_data = unserialize($v['attr_cfg']);
	        foreach($attr_cfg_data as $attr_id=>$vv)
	        {
	            $attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
	        }
	        $attr_cfg_json = substr($attr_cfg_json,0,-1);
	        $attr_cfg_json.="},";
	    }
	    if($attr_stock)
	    {
	        $attr_cfg_json = substr($attr_cfg_json,0,-1);
	        $attr_stock_json = substr($attr_stock_json,0,-1);
	    }
	    
	    $attr_cfg_json .= "}";
	    $attr_stock_json .= "}";

		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',0);
		//关联商品
		//$tempInfo = M('Relate_goods')->where('good_id='.$id.' and is_shop=0')->find();
		$tempInfo = unserialize($vo['cache_relate']);
		if( $tempInfo ){
			$relate_goods = array();
			$tmpList = M('Deal')->where('id in ('.$tempInfo['relate_ids'].')')->field('id,name,img')->select();
			foreach($tmpList as $item){
				$durl = url("index","preview#deal",array('id'=>$item['id'],'type'=>0));
				$relate_goods[] = array(
						'id'		=>	$item['id'],
						'name'		=>	$item['name'],
						'url'		=>	$durl,
						'share_url'	=>	SITE_DOMAIN.$durl,
						'img'		=>	$item['img']
				);
			}

			$this->assign("relate_goods",$relate_goods);
		}	    
	    
	    
	    $this->assign("select_sub_cate",$select_sub_cate);
	    $this->assign("select_location",$select_location);
	    $this->assign("attr_cfg_json",$attr_cfg_json);
	    $this->assign("attr_stock_json",$attr_stock_json);
	    $this->display();
	}
	
	public function biz_apply_shop_edit(){
	    $id = intval($_REQUEST['id']);
	    $condition['is_delete'] = 0;
	    $condition['id'] = $id;
	    $vo = M("DealSubmit")->where($condition)->find();
	    $vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
	    $vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';

	    $this->assign ( 'vo', $vo );
	     
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
	    $cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
	    $cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
	    $this->assign("cate_tree",$cate_tree);
	

	    //选中门店
	    $select_location = array();
	    $select_location = implode(",", unserialize($vo['cache_location_id']));
	     
	    $shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
	    $shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
	    $this->assign("shop_cate_tree",$shop_cate_tree);
	     
	    $brand_list = M("Brand")->findAll();
	    $this->assign("brand_list",$brand_list);
	     
	     
	    $supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
	    $this->assign("supplier_info",$supplier_info);
	     
	    $goods_type_list = M("GoodsType")->findAll();
	    $this->assign("goods_type_list",$goods_type_list);
	     
	    //输出图片集
	    $img_list = unserialize($vo['cache_focus_imgs']);
	
	    $this->assign("img_list",$img_list);
	     
	     
	    $weight_list = M("WeightUnit")->findAll();
	    $this->assign("weight_list",$weight_list);


	    // 输出规格库存的配置
	    $attr_stock = unserialize($vo['cache_attr_stock']);
	    $attr_cfg_json = "{";
	    $attr_stock_json = "{";
	     
	    foreach($attr_stock as $k=>$v)
	    {
	        $attr_cfg_json.=$k.":"."{";
	        $attr_stock_json.=$k.":"."{";
	        foreach($v as $key=>$vvv)
	        {
	            if($key!='attr_cfg')
	                $attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
	        }
	        $attr_stock_json = substr($attr_stock_json,0,-1);
	        $attr_stock_json.="},";
	
	        $attr_cfg_data = unserialize($v['attr_cfg']);
	        foreach($attr_cfg_data as $attr_id=>$vv)
	        {
	            $attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
	        }
	        $attr_cfg_json = substr($attr_cfg_json,0,-1);
	        $attr_cfg_json.="},";
	    }
	    if($attr_stock)
	    {
	        $attr_cfg_json = substr($attr_cfg_json,0,-1);
	        $attr_stock_json = substr($attr_stock_json,0,-1);
	    }
	     
	    $attr_cfg_json .= "}";
	    $attr_stock_json .= "}";


	    $this->assign("select_location",$select_location);
	    $this->assign("attr_cfg_json",$attr_cfg_json);
	    $this->assign("attr_stock_json",$attr_stock_json);

		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',0);
		//关联商品
		//$tempInfo = M('Relate_goods')->where('good_id='.$id.' and is_shop=0')->find();
		$tempInfo = unserialize($vo['cache_relate']);
		if( $tempInfo ){
			$relate_goods = array();
			$tmpList = M('Deal')->where('id in ('.$tempInfo['relate_ids'].')')->field('id,name,img')->select();
			foreach($tmpList as $item){
				$durl = url("index","preview#deal",array('id'=>$item['id'],'type'=>0));
				$relate_goods[] = array(
						'id'		=>	$item['id'],
						'name'		=>	$item['name'],
						'url'		=>	$durl,
						'share_url'	=>	SITE_DOMAIN.$durl,
						'img'		=>	$item['img']
				);
			}

			$this->assign("relate_goods",$relate_goods);
		}	    
	    
	    //输出配送方式列表
	    $delivery_list = M("Delivery")->where("is_effect=1")->findAll();
	    foreach($delivery_list as $k=>$v)
	    {
	        $delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['deal_id']." and delivery_id = ".$v['id'])->getField("free_count");
	        $delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['deal_id']." and delivery_id = ".$v['id'])->count();
	    }
	    $this->assign("delivery_list",$delivery_list);
	    //输出支付方式
	    $payment_list = M("Payment")->where("is_effect=1")->findAll();
	    foreach($payment_list as $k=>$v)
	    {
	        $payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['deal_id']." and payment_id = ".$v['id'])->count();
	    }
	    $this->assign("payment_list",$payment_list);
	    
	    $this->display();
	}
	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//对于商户请求操作
		if(intval($_REQUEST['edit_type']) == 2 && intval($_REQUEST['deal_id'])>0){ //商户提交修改审核
		    $deal_submit_id = intval($_REQUEST['id']);
		    $data['id'] = intval($_REQUEST['deal_id']);
		}
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		
		if(intval($data['return_score'])<0)
		{
			$this->error("积分返还不能为负数");
		}
		if(floatval($data['return_money'])<0)
		{
			$this->error("现金返还不能为负数");
		}
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}		
		if($data['cate_id']==0)
		{
			$this->error(L("DEAL_CATE_EMPTY_TIP"));
		}
		if($data['city_id']==0)
		{
			$this->error(L("DEAL_CITY_EMPTY_TIP"));
		}
		$city_info = M("DealCity")->where("id=".intval($data['city_id']))->find();
		if($city_info['pid']==0){
			$this->error("只能选择城市，不能选择省份");
		}
		if($data['min_bought']<0)
		{
			$this->error(L("DEAL_MIN_BOUGHT_ERROR_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']!=0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		
		$data['notice'] = intval($_REQUEST['notice']);
		$data['begin_time'] = strim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = strim($data['end_time'])==''?0:to_timespan($data['end_time']);
		$data['coupon_begin_time'] = strim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = strim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		if($data['coupon_time_type']==1)
		{
			$data['coupon_begin_time'] = 0;
			$data['coupon_end_time'] = 0;
		}
		else
		{
			$data['coupon_day'] = 0;
		}
		$data['buyin_app'] = intval($_REQUEST['buyin_app']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$data['update_time'] = NOW_TIME;
		$data['publish_wait'] = 0;
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		
		$deal_tags = $_REQUEST['deal_tag'];
		$deal_tag = 0;
		foreach($deal_tags as $t)
		{
			$t2 = pow(2,$t);
			$deal_tag = $deal_tag|$t2;
		}
		$data['deal_tag'] = $deal_tag;
		
		
		//团购产品都要发券不配送
		$data['is_delivery'] = 0;
		
		$data['auto_order'] = 0;
		$data['any_refund'] = 0;
		$data['expire_refund'] = 0;
		$data['is_refund'] = 0;
		$data['is_lottery'] = 0;
		
		foreach($deal_tags as $t)
		{
			if($t==0)
			{
				$data['is_lottery'] = 1;
			}
			//根据tag计算免预约
			if($t==1)
			{
				$data['auto_order'] = 1;
			}
			//随时退
			if($t==6)
			{
				$data['any_refund'] = 1;
			}
			//过期退
			if($t==5)
			{
				$data['expire_refund'] = 1;
			}
		}
		if($data['any_refund']==1||$data['expire_refund']==1)
		{
			$data['is_refund'] = 1;
		}

		if(count($_REQUEST['stock_cfg_num'])>0)
		{
			$data['max_bought'] = -1;
		}
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			
			//同步团购券
			
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set expire_refund = ".$data['expire_refund'].",any_refund = ".$data['any_refund'].",supplier_id=".$data['supplier_id']." where deal_id = ".$data['id']);
			
			if($data['coupon_time_type']==0)
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set end_time=".$data['coupon_end_time'].",begin_time=".$data['coupon_begin_time']." where deal_id = ".$data['id']);
				
			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			//M("DealAttr")->where("deal_id=".$data['id'])->delete();
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_add_balance_price = $_REQUEST['deal_add_balance_price'];
			$deal_attr_stock_hd		= $_REQUEST['deal_attr_stock_hd'];
			$new_ids = array(0);
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_attr where name = '".$v."' and goods_type_attr_id = '".$goods_type_attr_id."' and deal_id = '".$data['id']."'");
						if($deal_attr_item)
						{
							$deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
							$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
							$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
							$GLOBALS['db']->autoExecute(DB_PREFIX."deal_attr",$deal_attr_item,"UPDATE","id=".$deal_attr_item['id']);
							$new_ids[] = $deal_attr_item['id'];
						}
						else
						{
							$deal_attr_item['deal_id'] = $data['id'];
							$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
							$deal_attr_item['name'] = $v;
							$deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
							$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
							$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
							$new_ids[] = M("DealAttr")->add($deal_attr_item);
						}
					}
				}
			}
			$sql = "delete from ".DB_PREFIX."deal_attr where id not in (".implode(",",$new_ids).") and deal_id = '".$data['id']."'";
			$GLOBALS['db']->query($sql);
			
			//开始创建属性库存
			//M("AttrStock")->where("deal_id=".$data['id'])->delete();
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			$new_ids = array(0);
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."attr_stock where deal_id = '".$data['id']."' and attr_str = '".$attr_str[$row]."'");
				if($stock_data)
				{
					$stock_data['stock_cfg'] = $v;
					$GLOBALS['db']->autoExecute(DB_PREFIX."attr_stock",$stock_data,"UPDATE","id=".$stock_data['id']);
					$new_ids[] = $stock_data['id'];
				}
				else
				{
					$stock_data = array();
					$stock_data['deal_id'] = $data['id'];
					$stock_data['stock_cfg'] = $v;
					$stock_data['attr_str'] = $attr_str[$row];
					$attr_cfg_data = array();
					foreach($attr_cfg as $attr_id=>$cfg)
					{
						$attr_cfg_data[$attr_id] = $cfg[$row];
					}
					$stock_data['attr_cfg'] = serialize($attr_cfg_data);
// 					$sql = "select sum(oi.number) from ".DB_PREFIX."deal_order_item as oi left join ".
// 							DB_PREFIX."deal as d on d.id = oi.deal_id left join ".
// 							DB_PREFIX."deal_order as do on oi.order_id = do.id where".
// 							" do.pay_status = 2 and do.is_delete = 0 and d.id = ".$data['id'].
// 							" and oi.attr_str like '%".$attr_str[$row]."%'";
											
// 					$stock_data['buy_count'] = intval($GLOBALS['db']->getOne($sql));
					$new_ids[] = M("AttrStock")->add($stock_data);
				}
			}
			$sql = "delete from ".DB_PREFIX."attr_stock where id not in (".implode(",",$new_ids).") and deal_id = '".$data['id']."'";
			$GLOBALS['db']->query($sql);

			M("FreeDelivery")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $data['id'];
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			M("DealDelivery")->where("deal_id=".$data['id'])->delete();
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $data['id'];
					M("DealDelivery")->add($delivery_conf);
			}
			
			
		//开始创建筛选项
			M("DealFilter")->where("deal_id=".$data['id'])->delete();
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $data['id'];
				M("DealFilter")->add($filter_data);
				
				$filter_array = preg_split("/[ ,]/i",$filter_value);
				foreach($filter_array as $filter_item)
				{
					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
					if(!$filter_row)
					{
						$filter_row = array();
						$filter_row['name'] = $filter_item;
						$filter_row['filter_group_id'] = $filter_group_id;
						M("Filter")->add($filter_row);
					}
				}
			}
			M("DealCateTypeDealLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['deal_cate_type_id'] as $type_id)
			{
				$link_data = array();
				$link_data['deal_cate_type_id'] = $type_id;
				$link_data['deal_id'] = $data['id'];
				M("DealCateTypeDealLink")->add($link_data);
			}
			
			M("DealLocationLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $data['id'];
				M("DealLocationLink")->add($link_data);
			}
			//成功提示
			syn_deal_status($data['id']);
			foreach($_REQUEST['location_id'] as $location_id)
			{
				recount_supplier_data_count($location_id,"tuan");
			}
			syn_deal_match($data['id']);
			
			syn_attr_stock_key($data['id']);
			
			//对于商户请求操作
			if(intval($_REQUEST['edit_type']) == 2 && $deal_submit_id>0){ //商户提交修改审核
			    /*同步商户发布表状态*/
			     $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("admin_check_status"=>1),"UPDATE","id=".$deal_submit_id); // 1 通过 2 拒绝',
			}
			
			//增加商品关联购买
			if(!empty($_REQUEST['relate_goods_id'])){
				$saveArray = array(
						'relate_ids'	=> implode(',', $_REQUEST['relate_goods_id']),
						'is_shop'		=> 0,
				);
				if( M('Relate_goods')->where('good_id='.intval($data['id']))->find() ){
					$rs = M('Relate_goods')->where('good_id='.intval($data['id']))->save($saveArray);
				}else{
					$saveArray['good_id'] = intval($data['id']);
					$rs = M('Relate_goods')->add($saveArray);
				}
				//if(!$rs){}
			}else{//没有关联商品
				$rs = M('Relate_goods')->where('good_id='.intval($data['id']))->delete();
			}
			
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",1);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					 
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					$locations = M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
					
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->setField("is_delete",0);
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					 					
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					 
					 
					$locations = M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
					
					save_log($info.l("RESTORE_SUCCESS"),1);
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				//删除的验证
				if(M("DealOrder")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("DEAL_ORDER_NOT_EMPTY"),$ajax);
				}
				M("DealCoupon")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealDelivery")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealPayment")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealAttr")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("AttrStock")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealCateTypeDealLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				M("DealLocationLink")->where(array ('deal_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				//删除关联商品
				M("Relate_goods")->where(array ('good_id' => array ('in', explode ( ',', $id ) ) ))->delete();
				
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
					 
					 
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
					
				if ($list!==false) {

					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	/**
	 * 删除商户提交数据
	 */
	public function biz_submit_del() {
	    //彻底删除指定记录
	    $ajax = intval($_REQUEST['ajax']);
	    $id = $_REQUEST ['id'];
	    if (isset ( $id )) {
	        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );

	        $rel_data = M("DealSubmit")->where($condition)->findAll();
	        foreach($rel_data as $data)
	        {
	            $info[] = $data['name'];
	
	
	        }
	        if($info) $info = implode(",",$info);
	        $list = M("DealSubmit")->where ( $condition )->delete();
	        	
	        if ($list!==false) {
	            save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
	            $this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
	        } else {
	            save_log($info.l("FOREVER_DELETE_FAILED"),0);
	            $this->error (l("FOREVER_DELETE_FAILED"),$ajax);
	        }
	    } else {
	        $this->error (l("INVALID_OPERATION"),$ajax);
	    }
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField('name');
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		 
		save_log($log_info.l("SORT_SUCCESS"),1);
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		M(MODULE_NAME)->where("id=".$id)->setField("update_time",NOW_TIME);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		 
		$locations = M("DealLocationLink")->where(array ('deal_id' => $id ))->findAll();
					foreach($locations as $location)
					{
						recount_supplier_data_count($location['location_id'],"daijin");
						recount_supplier_data_count($location['location_id'],"tuan");
					}
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function attr_html()
	{
		$deal_goods_type = intval($_REQUEST['deal_goods_type']);
		$id = intval($_REQUEST['id']);
		$edit_type = intval($_REQUEST['edit_type']);
		
		$edit_type = $edit_type==0?1:$edit_type;
		
		$is_data = false;
		if($edit_type == 1 && $GLOBALS['db']->getOne("select deal_goods_type from ".DB_PREFIX."deal where id = ".$id)==$deal_goods_type){
		    $is_data = true;
		}elseif($edit_type==2 && $GLOBALS['db']->getOne("select deal_goods_type from ".DB_PREFIX."deal_submit where id = ".$id)==$deal_goods_type){
		    $is_data = true;
		}
		
		if($id>0 && $is_data)
		{		
		    $goods_type_attr = null;
		    if ($edit_type == 1){
			     $goods_type_attr = M()->query("select a.name as attr_name,a.is_checked as is_checked,a.price as price,a.add_balance_price,b.* from ".conf("DB_PREFIX")."deal_attr as a left join ".conf("DB_PREFIX")."goods_type_attr as b on a.goods_type_attr_id = b.id where a.deal_id=".$id." order by a.id asc");
		    }else{
		        //商品分类属性
		        $goods_type_attr_data = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."goods_type_attr where goods_type_id = ".$deal_goods_type);
		        foreach($goods_type_attr_data as $k=>$v){
		            $f_goods_type_attr[$v['id']] = $v;
		        }
		        //团购已经选择的分类属性值
		        $deal_attr_data = unserialize($GLOBALS['db']->getOne("select cache_deal_attr from ".DB_PREFIX."deal_submit where id=".$id));
		        
		        
		        
		        foreach($deal_attr_data as $k=>$v){
		            $temp_data = array();
		            $temp_data['attr_name'] = $v['name'];
		            $temp_data['is_checked'] = $v['is_checked'];
		            $temp_data['price'] = $v['price'];
		            $temp_data['add_balance_price'] = $v['add_balance_price'];
		            $temp_data['id'] = $v['goods_type_attr_id'];
		            $temp_data['name'] = $f_goods_type_attr[$v['goods_type_attr_id']]['name'];
		            $temp_data['input_type'] = 0;
		            $temp_data['preset_value'] = '';
		            $temp_data['goods_type_id'] = $v['goods_type_attr_id'];
		            $temp_data['supplier_id'] = $GLOBALS['account_info']['supplier_id'];
		        
		        
		            $goods_type_attr[] = $temp_data;
		        }
		    }
			$goods_type_attr_id = 0;
			if($goods_type_attr)
			{
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
					if($goods_type_attr_id!=$v['id'])
					{
						$goods_type_attr[$k]['is_first'] = 1;
					}
					else
					{
						$goods_type_attr[$k]['is_first'] = 0;
					}
					$goods_type_attr_id = $v['id'];
				}	
			}
			else 
			{
				$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
				foreach($goods_type_attr as $k=>$v)
				{
					$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
					$goods_type_attr[$k]['is_first'] = 1;
				}
			}		
		}
		else
		{
			$goods_type_attr = M("GoodsTypeAttr")->where("goods_type_id=".$deal_goods_type)->findAll();
			foreach($goods_type_attr as $k=>$v)
			{
				$goods_type_attr[$k]['attr_list'] = preg_split("/[ ,]/i",$v['preset_value']);
				$goods_type_attr[$k]['is_first'] = 1;
			}		
		}
		$this->assign("goods_type_attr",$goods_type_attr);		
		$this->display();
	}
	
	public function show_detail()
	{
		$id = intval($_REQUEST['id']);
		
		$deal_info = M("Deal")->getById($id);
		$this->assign("deal_info",$deal_info);
		//购买的单数
		$real_user_count = intval($GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_user_count",$real_user_count);
		
		$real_buy_count =  intval($GLOBALS['db']->getOne("select sum(doi.number) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".$id." and do.pay_status = 2"));
		$this->assign("real_buy_count",$real_buy_count);
		
		$real_coupon_count = intval(M("DealCoupon")->where("deal_id=".$id." and is_valid=1")->count());
		$this->assign("real_coupon_count",$real_coupon_count);

		//总收款，不计退款
		$pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 group by pn.id");
		$pay_total = 0;
		foreach($pay_total_rows as $money)
		{
			$pay_total = $pay_total + floatval($money['money']);
		}		
		$this->assign("pay_total",$pay_total);

		//每个支付方式下的收款
		$payment_list = M("Payment")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_pay_total = 0;
			$payment_pay_total_rows = $GLOBALS['db']->getAll("select pn.money from ".DB_PREFIX."payment_notice as pn left join ".DB_PREFIX."deal_order as do on pn.order_id = do.id left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." and pn.is_paid = 1 and pn.payment_id = ".$v['id']." group by pn.id");
			foreach($payment_pay_total_rows as $money)
			{
				$payment_pay_total = $payment_pay_total + floatval($money['money']);
			}	
			$payment_list[$k]['pay_total'] = $payment_pay_total;
		}
		$this->assign("payment_list",$payment_list);
		
		
		//订单实收
		$order_total = 0;
		$order_total_rows = $GLOBALS['db']->getAll("select do.pay_amount as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($order_total_rows as $money)
		{
				$order_total = $order_total + floatval($money['money']);
		}	
		$this->assign("order_total",$order_total);
		
		//额外退款的订单
		$extra_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.extra_status > 0 and doi.deal_id = ".$id);
		$this->assign("extra_count",$extra_count);
		
		//额外退款的订单
		$aftersale_count = $GLOBALS['db']->getOne("select count(distinct(do.id)) from ".DB_PREFIX."deal_order as do left join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.after_sale > 0 and doi.deal_id = ".$id);
		$this->assign("aftersale_count",$aftersale_count);
		
		//售后退款
		$refund_money = 0;
		$refund_total_rows = $GLOBALS['db']->getAll("select do.refund_money as money from ".DB_PREFIX."deal_order as do inner join ".DB_PREFIX."deal_order_item as doi on do.id = doi.order_id where do.pay_status = 2 and doi.deal_id = ".$id." group by do.id");
		foreach($refund_total_rows as $money)
		{
				$refund_money = $refund_money + floatval($money['money']);
		}
		$this->assign("refund_money",$refund_money);
		$this->display();
	}
	
	
	public function shop()
	{
		//分类
		$cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("ShopCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		//输出品牌
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);
		
		//开始加载搜索条件
		if(intval($_REQUEST['id'])>0)
		$map['id'] = intval($_REQUEST['id']);
		$map['is_delete'] = 0;
		if(strim($_REQUEST['name'])!='')
		{
			$map['name'] = array('like','%'.strim($_REQUEST['name']).'%');			
		}
		if(intval($_REQUEST['city_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_city");
			$city_ids = $child->getChildIds(intval($_REQUEST['city_id']));
			$city_ids[] = intval($_REQUEST['city_id']);
			$map['city_id'] = array("in",$city_ids);
		}
		
		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("shop_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$map['shop_cate_id'] = array("in",$cate_ids);
		}
		if(intval($_REQUEST['brand_id'])>0)
		$map['brand_id'] = intval($_REQUEST['brand_id']);
		
		$map['publish_wait'] = 0;
		$map['is_shop'] = 1;
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	
	
	public function shop_add()
	{
		$this->assign("new_sort", M("Deal")->where("is_delete=0")->max("sort")+1);
		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		$this->assign("payment_list",$payment_list);
		
		//输出商品活动
		$event_list = M("deal_event")->findAll();
		$this->assign("event_list",$event_list);
		//var_dump($event_list);die;
		
		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',1);
		
		$this->display();
	}
	
	public function shop_insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//对于商户请求操作
		$id = intval($_REQUEST['id']);
		$edit_type = intval($_REQUEST['edit_type']);
		
		if($id>0 && $edit_type==2){//商户申请新增团购
		    unset($data['id']);
		}
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/shop_add"));
		
		if($data['buy_type']==0)
		{
			if(intval($data['return_score'])<0)
			{
				$this->error("积分返还不能为负数");
			}
			if(floatval($data['return_money'])<0)
			{
				$this->error("现金返还不能为负数");
			}
		}
		else
		{
			$data['return_score'] = "-".abs($_REQUEST['deal_score']);
			if(intval($_REQUEST['deal_score'])==0)
			{
				$this->error("请输入所需的积分");
			}
		}
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['shop_cate_id']==0)
		{
			$this->error(L("SHOP_CATE_EMPTY_TIP"));
		}		

		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		// 更新数据

		if($data['brand_promote']==1)
		{
			//品牌促销
			$brand_info = M("Brand")->getById($data['brand_id']);
			if($brand_info['brand_promote']==1)
			{
				$data['begin_time'] = $brand_info['begin_time'];
				$data['end_time'] = $brand_info['end_time'];
			}
		}
		else
		{
			$data['begin_time'] = strim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
			$data['end_time'] = strim($data['end_time'])==''?0:to_timespan($data['end_time']);
		}
		$data['coupon_begin_time'] = strim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
		$data['coupon_end_time'] = strim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}

		$log_info = $data['name'];
		$data['is_shop'] = 1;
		$data['create_time'] = NOW_TIME;
		$data['update_time'] = NOW_TIME;
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		if($data['buy_type']!=1)
		{
			$deal_tags = $_REQUEST['deal_tag'];
			$deal_tag = 0;
			foreach($deal_tags as $t)
			{
				$t2 = pow(2,$t);
				$deal_tag = $deal_tag|$t2;
			}
			$data['deal_tag'] = $deal_tag;
		}
		else
		{
			$data['deal_tag'] = 0;
		}
		
		$data['is_lottery'] = 0;
		foreach($deal_tags as $t)
		{
			if($t==0)
			{
				$data['is_lottery'] = 1;
			}			
		}
		
		if($data['buy_type']==1)
		{
			$data['cart_type'] = 3;
			$data['is_refund'] = 0;
			$data['is_lottery'] = 0;
			$data['deal_tag'] = 0;
		}
		if(count($_REQUEST['stock_cfg_num'])>0)
		{
			$data['max_bought'] = -1;
		}
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//开始处理图片
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $list;
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_add_balance_price = $_REQUEST['deal_add_balance_price'];
			$deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];			
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $list;
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			
			
		
			
			
			//开始创建属性库存
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $list;
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				M("AttrStock")->add($stock_data);
			}
			
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $list;
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $list;
					M("DealPayment")->add($payment_conf);
				}
			}
			
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $list;
					M("DealDelivery")->add($delivery_conf);
			}
		//开始创建筛选项
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $list;
				M("DealFilter")->add($filter_data);
				
//				$filter_array = preg_split("/[ ,]/i",$filter_value);
//				foreach($filter_array as $filter_item)
//				{
//					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
//					if(!$filter_row)
//					{
//						if(strim($filter_item)!='')
//						{
//							$filter_row = array();
//							$filter_row['name'] = $filter_item;
//							$filter_row['filter_group_id'] = $filter_group_id;
//							M("Filter")->add($filter_row);
//						}
//					}
//				}
			}
			
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $list;
				M("DealLocationLink")->add($link_data);
			}
			
			//成功提示
			syn_deal_status($list);
			syn_deal_match($list);
			syn_attr_stock_key($list);
			
			if($id>0 && $edit_type == 2){ //商户提交审核
			    //同步商户数据表
			    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("deal_id"=>$list,"admin_check_status"=>1),"UPDATE","id=".$id);
			}
			
			//增加商品关联购买(非积分商品)
			if( (intval($_REQUEST['relate_goods_flag'])==1) && !empty($_REQUEST['relate_goods_id']) ){
				$saveArray = array(
						'good_id'		=> intval($list),
						'relate_ids'	=> implode(',', $_REQUEST['relate_goods_id']),
						'is_shop'		=> 1,
				);
				$rs = M('Relate_goods')->add($saveArray);
				//if(!$rs){}
			}
			
			
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("INSERT_FAILED").$dbErr,0);
			$this->error(L("INSERT_FAILED").$dbErr);
		}
	}	
	
	public function shop_edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['begin_time'] = $vo['begin_time']!=0?to_date($vo['begin_time']):'';
		$vo['end_time'] = $vo['end_time']!=0?to_date($vo['end_time']):'';
		$vo['coupon_begin_time'] = $vo['coupon_begin_time']!=0?to_date($vo['coupon_begin_time']):'';
		$vo['coupon_end_time'] = $vo['coupon_end_time']!=0?to_date($vo['coupon_end_time']):'';
		if($vo['buy_type']==1)
		$vo['deal_score'] = abs($vo['return_score']);
		$this->assign ( 'vo', $vo );
		

		
		$shop_cate_tree = M("ShopCate")->where('is_delete = 0')->findAll();
		$shop_cate_tree = D("ShopCate")->toFormatTree($shop_cate_tree,'name');
		$this->assign("shop_cate_tree",$shop_cate_tree);
		
		//输出商品活动
		$event_list = M("deal_event")->findAll();
		$this->assign("event_list",$event_list);
		
		//输出团购城市
		$city_list = M("DealCity")->where('is_delete = 0')->findAll();
		$city_list = D("DealCity")->toFormatTree($city_list,'name');
		$this->assign("city_list",$city_list);
		
		$supplier_info = M("Supplier")->where("id=".$vo['supplier_id'])->find();
		$this->assign("supplier_info",$supplier_info);
		
		$goods_type_list = M("GoodsType")->findAll();
		$this->assign("goods_type_list",$goods_type_list);
		
		$brand_list = M("Brand")->findAll();
		$this->assign("brand_list",$brand_list);	
		
		//输出图片集
		$img_list = M("DealGallery")->where("deal_id=".$vo['id'])->order("sort asc")->findAll();
		$imgs = array();
		foreach($img_list as $k=>$v)
		{
			$imgs[$v['sort']] = $v['img']; 
		}
		$this->assign("img_list",$imgs);
		
		
		$weight_list = M("WeightUnit")->findAll();
		$this->assign("weight_list",$weight_list);
		
		
		//输出配送方式列表
		$delivery_list = M("Delivery")->where("is_effect=1")->findAll();
		foreach($delivery_list as $k=>$v)
		{
			$delivery_list[$k]['free_count'] = M("FreeDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->getField("free_count");			
			$delivery_list[$k]['checked'] = M("DealDelivery")->where("deal_id=".$vo['id']." and delivery_id = ".$v['id'])->count();	
		}
		$this->assign("delivery_list",$delivery_list);
		
		//输出支付方式
		$payment_list = M("Payment")->where("is_effect=1")->findAll();
		foreach($payment_list as $k=>$v)
		{
			$payment_list[$k]['checked'] = M("DealPayment")->where("deal_id=".$vo['id']." and payment_id = ".$v['id'])->count();			
		}
		$this->assign("payment_list",$payment_list);
		
		
		//输出规格库存的配置
		$attr_stock = M("AttrStock")->where("deal_id=".intval($vo['id']))->order("id asc")->findAll();
		$attr_cfg_json = "{";
		$attr_stock_json = "{";
		
		foreach($attr_stock as $k=>$v)
		{
			$attr_cfg_json.=$k.":"."{";
			$attr_stock_json.=$k.":"."{";
			foreach($v as $key=>$vvv)
			{
				if($key!='attr_cfg')
				$attr_stock_json.="\"".$key."\":"."\"".$vvv."\",";
			}
			$attr_stock_json = substr($attr_stock_json,0,-1);
			$attr_stock_json.="},";	
			
			$attr_cfg_data = unserialize($v['attr_cfg']);	
			foreach($attr_cfg_data as $attr_id=>$vv)
			{
				$attr_cfg_json.=$attr_id.":"."\"".$vv."\",";
			}	
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_cfg_json.="},";		
		}
		if($attr_stock)
		{
			$attr_cfg_json = substr($attr_cfg_json,0,-1);
			$attr_stock_json = substr($attr_stock_json,0,-1);
		}
		
		$attr_cfg_json .= "}";
		$attr_stock_json .= "}";
		
		
		$this->assign("attr_cfg_json",$attr_cfg_json);	
		$this->assign("attr_stock_json",$attr_stock_json);	
		
		//商品或团购 可以关联商品数量
		$this->assign('relate_goods_num',$this->RELATE_GOODS_NUM);
		$this->assign('is_shop',1);
		//关联商品
		$tempInfo = M('Relate_goods')->where('good_id='.$id.' and is_shop=1')->find();
		if( $tempInfo ){
			$relate_goods = array();
			$tmpList = M('Deal')->where('id in ('.$tempInfo['relate_ids'].')')->field('id,name,img')->select();
			foreach($tmpList as $item){
				$durl = url("index","deal#".$item['id'],array('preview'=>1));
				$relate_goods[] = array(
						'id'		=>	$item['id'],
						'name'		=>	$item['name'],
						'url'		=>	$durl,
						'share_url'	=>	SITE_DOMAIN.$durl,
						'img'		=>	$item['img']
				);
			}

			$this->assign("relate_goods",$relate_goods);
		}
		
		$this->display ();
	}
	
	
	public function shop_update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//对于商户请求操作
		if(intval($_REQUEST['edit_type']) == 2 && intval($_REQUEST['deal_id'])>0){ //商户提交修改审核
		    $deal_submit_id = intval($_REQUEST['id']);
		    $data['id'] = intval($_REQUEST['deal_id']);
		}
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/shop_edit",array("id"=>$data['id'])));
		if($data['buy_type']==0)
		{
			if(intval($data['return_score'])<0)
			{
				$this->error("积分返还不能为负数");
			}
			if(floatval($data['return_money'])<0)
			{
				$this->error("现金返还不能为负数");
			}
		}
		else
		{
			$data['return_score'] = "-".abs($_REQUEST['deal_score']);
			if(intval($_REQUEST['deal_score'])==0)
			{
				$this->error("请输入所需的积分");
			}
		}
		
		if(!check_empty($data['name']))
		{
			$this->error(L("DEAL_NAME_EMPTY_TIP"));
		}	
		if(!check_empty($data['sub_name']))
		{
			$this->error(L("DEAL_SUB_NAME_EMPTY_TIP"));
		}	
		if($data['shop_cate_id']==0)
		{
			$this->error(L("SHOP_CATE_EMPTY_TIP"));
		}
		if($data['user_min_bought']<0)
		{
			$this->error(L("DEAL_USER_MIN_BOUGHT_ERROR_TIP"));
		}		
		if($data['user_max_bought']<0)
		{
			$this->error(L("DEAL_USER_MAX_BOUGHT_ERROR_TIP"));
		}
		if($data['user_max_bought']<$data['user_min_bought']&&$data['user_max_bought']>0)
		{
			$this->error(L("DEAL_USER_MAX_MIN_BOUGHT_ERROR_TIP"));
		}
		
		if($data['brand_promote']==1)
		{
			//品牌促销
			$brand_info = M("Brand")->getById($data['brand_id']);
			if($brand_info['brand_promote']==1)
			{
				$data['begin_time'] = $brand_info['begin_time'];
				$data['end_time'] = $brand_info['end_time'];
			}
		}
		else
		{
			$data['begin_time'] = strim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
			$data['end_time'] = strim($data['end_time'])==''?0:to_timespan($data['end_time']);
		}

		  $data['coupon_begin_time'] = strim($data['coupon_begin_time'])==''?0:to_timespan($data['coupon_begin_time']);
	     $data['coupon_end_time'] = strim($data['coupon_end_time'])==''?0:to_timespan($data['coupon_end_time']);
		//将第一张图片设为团购图片
		$imgs = $_REQUEST['img'];
		foreach($imgs as $k=>$v)
		{
				if($v!='')
				{
					$data['img'] = $v;
					break;
				}
		}
		$data['update_time'] = NOW_TIME;
		$data['publish_wait'] = 0;
		if(intval($data['is_coupon'])==1&&intval($data['is_refund'])==1)
		{
			$data['expire_refund'] = intval($_REQUEST['expire_refund']);
			$data['any_refund'] = intval($_REQUEST['any_refund']);
		}
		else
		{
			$data['expire_refund'] = 0;
			$data['any_refund'] = 0;
		}
		
		if($_REQUEST['deal_attr']&&count($_REQUEST['deal_attr'])>0)
		{
			$data['multi_attr'] = 1;
		}
		else
		{
			$data['multi_attr'] = 0;
		}
		
		if($data['buy_type']!=1)
		{
			$deal_tags = $_REQUEST['deal_tag'];
			$deal_tag = 0;
			foreach($deal_tags as $t)
			{
				$t2 = pow(2,$t);
				$deal_tag = $deal_tag|$t2;
			}
			$data['deal_tag'] = $deal_tag;
		}
		else
		{
			$data['deal_tag'] = 0;
		}
		
		$data['is_lottery'] = 0;
		foreach($deal_tags as $t)
		{
			if($t==0)
			{
				$data['is_lottery'] = 1;
			}
		}
		
		if($data['buy_type']==1)
		{
			$data['cart_type'] = 3;
			$data['is_refund'] = 0;
			$data['deal_tag'] = 0;
			$data['is_lottery'] = 0;
		}
		$data['buyin_app'] = intval($_REQUEST['buyin_app']);
		// 更新数据
		if(count($_REQUEST['stock_cfg_num'])>0)
		{
			$data['max_bought'] = -1;
		}
		$list=M(MODULE_NAME)->save ($data);
			if (false !== $list) {
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set expire_refund = ".$data['expire_refund'].",any_refund = ".$data['any_refund'].",supplier_id=".$data['supplier_id'].",end_time=".$data['coupon_end_time'].",begin_time=".$data['coupon_begin_time']." where deal_id = ".$data['id']);
				
			//开始处理图片
			M("DealGallery")->where("deal_id=".$data['id'])->delete();
			$imgs = $_REQUEST['img'];
			foreach($imgs as $k=>$v)
			{
				if($v!='')
				{
					$img_data['deal_id'] = $data['id'];
					$img_data['img'] = $v;
					$img_data['sort'] = $k;
					M("DealGallery")->add($img_data);
				}
			}
			//end 处理图片
			
			//开始处理属性
			M("DealAttr")->where("deal_id=".$data['id'])->delete();
			$deal_attr = $_REQUEST['deal_attr'];
			$deal_attr_price = $_REQUEST['deal_attr_price'];	
			$deal_add_balance_price = $_REQUEST['deal_add_balance_price'];
			$deal_attr_stock_hd		= $_REQUEST['deal_attr_stock_hd'];
			foreach($deal_attr as $goods_type_attr_id=>$arr)
			{
				foreach($arr as $k=>$v)
				{
					if($v!='')
					{
						$deal_attr_item['deal_id'] = $data['id'];
						$deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
						$deal_attr_item['name'] = $v;
						$deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
						$deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
						$deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
						M("DealAttr")->add($deal_attr_item);
					}
				}
			}
			//开始创建属性库存
			M("AttrStock")->where("deal_id=".$data['id'])->delete();
			$stock_cfg = $_REQUEST['stock_cfg_num'];
			$attr_cfg = $_REQUEST['stock_attr'];
			$attr_str = $_REQUEST['stock_cfg'];
			foreach($stock_cfg as $row=>$v)
			{
				$stock_data = array();
				$stock_data['deal_id'] = $data['id'];
				$stock_data['stock_cfg'] = $v;
				$stock_data['attr_str'] = $attr_str[$row];
				$attr_cfg_data = array();
				foreach($attr_cfg as $attr_id=>$cfg)
				{
					$attr_cfg_data[$attr_id] = $cfg[$row];
				}
				$stock_data['attr_cfg'] = serialize($attr_cfg_data);
				$sql = "select sum(oi.number) from ".DB_PREFIX."deal_order_item as oi left join ".
						DB_PREFIX."deal as d on d.id = oi.deal_id left join ".
						DB_PREFIX."deal_order as do on oi.order_id = do.id where".
						" do.pay_status = 2 and do.is_delete = 0 and d.id = ".$data['id'].
						" and oi.attr_str like '%".$attr_str[$row]."%'";
										
				$stock_data['buy_count'] = intval($GLOBALS['db']->getOne($sql));
				M("AttrStock")->add($stock_data);
			}

			M("FreeDelivery")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['free_delivery'])==1)
			{
				$delivery_ids = $_REQUEST['delivery_id'];
				$free_counts = $_REQUEST['free_count'];
				foreach($delivery_ids as $k=>$v)
				{
					$free_conf = array();
					$free_conf['delivery_id'] = $delivery_ids[$k];
					$free_conf['free_count'] = $free_counts[$k];
					$free_conf['deal_id'] = $data['id'];
					M("FreeDelivery")->add($free_conf);
				}
			}
			
			M("DealPayment")->where("deal_id=".$data['id'])->delete();
			if(intval($_REQUEST['define_payment'])==1)
			{
				$payment_ids = $_REQUEST['payment_id'];
				foreach($payment_ids as $k=>$v)
				{
					$payment_conf = array();
					$payment_conf['payment_id'] = $payment_ids[$k];
					$payment_conf['deal_id'] = $data['id'];
					M("DealPayment")->add($payment_conf);
				}
			}
			
			M("DealDelivery")->where("deal_id=".$data['id'])->delete();
			$delivery_ids = $_REQUEST['forbid_delivery_id'];
			foreach($delivery_ids as $k=>$v)
			{
					$delivery_conf = array();
					$delivery_conf['delivery_id'] = $delivery_ids[$k];
					$delivery_conf['deal_id'] = $data['id'];
					M("DealDelivery")->add($delivery_conf);
			}
			
			//开始创建筛选项
			M("DealFilter")->where("deal_id=".$data['id'])->delete();
			$filter = $_REQUEST['filter'];
			foreach($filter as $filter_group_id=>$filter_value)
			{
				$filter_data = array();
				$filter_data['filter'] = $filter_value;
				$filter_data['filter_group_id'] = $filter_group_id;
				$filter_data['deal_id'] = $data['id'];
				M("DealFilter")->add($filter_data);
				
//				$filter_array = preg_split("/[ ,]/i",$filter_value);
//				foreach($filter_array as $filter_item)
//				{
//					$filter_row = M("Filter")->where("filter_group_id = ".$filter_group_id." and name = '".$filter_item."'")->find();
//					if(!$filter_row)
//					{
//						if(strim($filter_item)!='')
//						{
//							$filter_row = array();
//							$filter_row['name'] = $filter_item;
//							$filter_row['filter_group_id'] = $filter_group_id;
//							M("Filter")->add($filter_row);
//						}
//
//					}
//				}
			}
			
			M("DealLocationLink")->where("deal_id=".$data['id'])->delete();
			foreach($_REQUEST['location_id'] as $location_id)
			{
				$link_data = array();
				$link_data['location_id'] = $location_id;
				$link_data['deal_id'] = $data['id'];
				M("DealLocationLink")->add($link_data);
			}
			//成功提示
			syn_deal_status($data['id']);
			syn_deal_match($data['id']);
			syn_attr_stock_key($data['id']);
			 
			//对于商户请求操作
			if(intval($_REQUEST['edit_type']) == 2 && $deal_submit_id>0){ //商户提交修改审核
			    /*同步商户发布表状态*/
			    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("admin_check_status"=>1),"UPDATE","id=".$deal_submit_id); // 1 通过 2 拒绝',
			}
			
			//增加商品关联购买(非积分商品)
			if( intval($_REQUEST['relate_goods_flag'])==1 ){
				if(!empty($_REQUEST['relate_goods_id'])){
					$saveArray = array(
						'relate_ids'	=> implode(',', $_REQUEST['relate_goods_id']),
						'is_shop'		=> 1,
					);
					if( M('Relate_goods')->where('good_id='.intval($data['id']))->find() ){
						$rs = M('Relate_goods')->where('good_id='.intval($data['id']))->save($saveArray);
					}else{
						$saveArray['good_id'] = intval($data['id']);
						$rs = M('Relate_goods')->add($saveArray);
					}
					//if(!$rs){}
				}else{//没有关联商品
					$rs = M('Relate_goods')->where('good_id='.intval($data['id']))->delete();
				}
			}
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$dbErr = M()->getDbError();
			save_log($log_info.L("UPDATE_FAILED").$dbErr,0);
			$this->error(L("UPDATE_FAILED").$dbErr,0);
		}
	}
	
	public function filter_html()
	{
		$shop_cate_id = intval($_REQUEST['shop_cate_id']);
		$edit_type = intval($_REQUEST['edit_type']);
		$id = intval($_REQUEST['id']);
		
		$ids = $this->get_parent_ids($shop_cate_id);
		$filter_group = M("FilterGroup")->where(array("cate_id"=>array("in",$ids)))->findAll();
		
		if($edit_type == 1){ //管理员添加数据
    		
    		foreach($filter_group as $k=>$v)
    		{
    			$filter_group[$k]['value'] = M("DealFilter")->where("filter_group_id = ".$v['id']." and deal_id = ".$id)->getField("filter");
    		}
		
		}elseif ($edit_type == 2){//商户提交数据

		    $cache_deal_filter = $GLOBALS['db']->getOne("select cache_deal_filter from ".DB_PREFIX."deal_submit where id = ".$id);
		    $cache_deal_filter = unserialize($cache_deal_filter);
		    foreach($filter_group as $k=>$v)
		    {
		        $filter_group[$k]['value'] = $cache_deal_filter[$v['id']]['filter'];
		    }  
		}
		$this->assign("filter_group",$filter_group);
		$this->display();
	}
	
	//获取当前分类的所有父分类包含本分类的ID
	private $cate_ids = array();
	private function get_parent_ids($shop_cate_id)
	{
		$pid = $shop_cate_id;
		do{
			$pid = M("ShopCate")->where("id=".$pid)->getField("pid");
			if($pid>0)
			$this->cate_ids[] = $pid;
		}while($pid!=0);

		$this->cate_ids[] = $shop_cate_id;

		return $this->cate_ids;
	}
	
	
	//可购买优惠券列表 is_shop = 2
	
	
	function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['cate_id']);
        $edit_type = intval($_REQUEST['edit_type']);
        $id = intval($_REQUEST['id']);
        
       
        $sub_cate_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_cate_type as c left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = c.id where l.cate_id = ".$cate_id);
        if($edit_type == 1){ //管理员添加数据
            $sub_cate_arr_data = $GLOBALS['db']->getAll("select deal_cate_type_id from ".DB_PREFIX."deal_cate_type_deal_link where deal_id = ".$id);
            foreach ($sub_cate_arr_data as $k=>$v){
                $sub_cate_arr[] = $v['deal_cate_type_id'];
            }
        
        }elseif ($edit_type == 2){//商户提交数据
            $select_sub_cate = $GLOBALS['db']->getOne("select cache_deal_cate_type_id from ".DB_PREFIX."deal_submit where id = ".$id);
            $sub_cate_arr = unserialize($select_sub_cate);
        
        }
        
        //处理选择状态
        foreach ($sub_cate_list as $k=>$v){
            if(in_array($v['id'], $sub_cate_arr)){
                $sub_cate_list[$k]['checked'] =1 ;
            }
        }

		$this->assign("sub_cate_list",$sub_cate_list);
		
		if($sub_cate_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	function load_supplier_location()
	{
		$supplier_id = intval($_REQUEST['supplier_id']);
		$id = intval($_REQUEST['id']);
		$edit_type = intval($_REQUEST['edit_type'])==0?1:intval($_REQUEST['edit_type']);
		
		$supplier_location_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id);
		if($edit_type == 1){ // 管理员提交数据
		    $select_location = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."deal_location_link where deal_id = ".$id);
		    foreach ($select_location as $k=>$v){
		        $supplier_location_arr[] = $v['location_id'];
		    }
		}elseif ($edit_type == 2){ // 商户提交数据
		    $select_location = $GLOBALS['db']->getOne("select cache_location_id from ".DB_PREFIX."deal_submit where id = ".$id);
		    $supplier_location_arr = unserialize($select_location);
		}
		
		foreach($supplier_location_list as $k=>$v)
		{
		    if(in_array($v['id'], $supplier_location_arr)){
                $supplier_location_list[$k]['checked'] =1 ;
            }
			
		}

		$this->assign("supplier_location_list",$supplier_location_list);
		
		if($supplier_location_list)
		$result['status'] = 1;
		else
		$result['status'] = 0;
		$result['html'] = $this->fetch();
		$this->ajaxReturn($result['html'],"",$result['status']);
	}
	
	
	
	public function shop_publish()
	{
	    if(isset($_REQUEST['admin_check_status']) && $_REQUEST['admin_check_status']==0){
	        $map['admin_check_status'] = intval($_REQUEST['admin_check_status']);
	    }
	    $map['is_shop']=1;
	    if (method_exists ( $this, '_filter' )) {
	        $this->_filter ( $map );
	    }
	    $name="DealSubmit";
	    $model = D ($name);
	    if (! empty ( $model )) {
	        $this->_list ( $model, $map );
	    }
	    $this->assign("show_status_check_btn",U("Deal/shop_publish",array("admin_check_status"=>0)));
	    $this->display ("publish");
	    return;
	}
	
	public function tuan_publish()
	{

	    if(isset($_REQUEST['admin_check_status']) && $_REQUEST['admin_check_status']==0){
	        $map['admin_check_status'] = intval($_REQUEST['admin_check_status']);
	    }
	    $map['is_shop']=0;
	    if (method_exists ( $this, '_filter' )) {
	        $this->_filter ( $map );
	    }
	    $name="DealSubmit";
	    $model = D ($name);
	    if (! empty ( $model )) {
	        $this->_list ( $model, $map );
	    }
	    $this->assign("show_status_check_btn",U("Deal/tuan_publish",array("admin_check_status"=>0)));
	    $this->display ("publish");
	    return;
	}
	
	/**
	 * 拒绝商户申请
	 */
	public function refused_apply(){
	    $id = intval($_REQUEST['id']);
	    $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where id = ".$id);
	    if($deal_submit_info['admin_check_status'] == 0){
	        //更新商户表状态为拒绝
	        
	        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("admin_check_status"=>2),"UPDATE","id=".$id);
	        $result['status'] = 1;
	        $result['info'] = "已经拒绝用户申请";
	    }else{
	        $result['status'] = 0;
	        $result['info'] = "申请不存在";
	    }
	    ajax_return($result);
	}
	
	/**
	 * 下架申请
	 */
	public function downline(){
	    $id = intval($_REQUEST['id']);
	    $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where id = ".$id);
	    if($deal_submit_info && $deal_submit_info['biz_apply_status']==3){
	        //更新商户表状态为拒绝
	        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",array("admin_check_status"=>1),"UPDATE","id=".$id);
	        //更新团购数据表
	        $GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("is_effect"=>0),"UPDATE","id=".$deal_submit_info['deal_id']);
	        $result['status'] = 1;
	        $result['info'] = "商品已经成功下架";
	    }else{
	        $result['status'] = 0;
	        $result['info'] = "申请不存在";
	    }
	    ajax_return($result);
	}
	
	/**
	 * 商品列表
	 */
	public function ajaxGoodsList(){
		$keywords = strim($_REQUEST['keywords']);
		$currId	  = intval($_REQUEST['id']);
		
		
		$where = array();
		//关联商品必须为普通商品(积分兑换商品和秒杀商品都不在列)
		$where['buy_type'] = 0;
		//只能在app端展示的除外
		$where['buyin_app'] = 0;
		
		$where['is_delete'] = 0;
		$where['publish_wait'] = 0;
		$where['is_shop'] = intval($_REQUEST['is_shop']);

 		if($keywords){
 			$where['name'] = array('like','%'.strim($keywords).'%');
 		}
		
		if($currId >0 ){
			$where['id'] = array('neq',$currId);
		}
		
 		$list = array();
 		$m = M('Deal');
 		$count = $m->where($where)->count();

 		$pager = buildPage(MODULE_NAME.'/'.ACTION_NAME,$_REQUEST,$count,$this->page,10,U("Deal/ajaxGoodsList","page"));
 		if($count > 0){
 			$tmpList =  $m->where($where)->order('id desc')->limit($pager['limit'])->findAll();
 			foreach($tmpList as $item){
 				if(intval($where['is_shop'])==1){//商品
 					$durl = url("index","deal#".$item['id'],array('preview'=>1));
 				}else{//团购
 					$durl = url("index","preview#deal",array('id'=>$item['id'],'type'=>0));
 				}
 				$list[] = array(
 					'id'		=>	$item['id'],
 					'name'		=>	$item['name'],
 					'url'		=>	$durl,
 					'share_url'	=>	SITE_DOMAIN.$durl,
 					'img'		=>	$item['icon']
 				);
 			}
 		}
 		$this->assign('keywords',$keywords);
		$this->assign("list",$list);
		$this->assign('pager',$pager);
		$this->assign('currId',$currId);
		
		$this->display();
	}	
	
	
	
	
	
}
?>