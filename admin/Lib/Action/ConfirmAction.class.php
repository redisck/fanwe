<?php

class ConfirmAction extends CommonAction {

    public function index() {
        $page_idx = intval($_REQUEST['p']) == 0 ? 1 : intval($_REQUEST['p']);
        $page_size = C('PAGE_LISTROWS');
        $limit = (($page_idx - 1) * $page_size) . "," . $page_size;

        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        }

        $id = intval($_REQUEST['id']);
        if ($id)
            $ex_condition = " and id = " . $id . " ";



        if (strim($_REQUEST['name']) != '') {
            $total = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "confirm");
            if ($total < 50000) {
                $list = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "confirm where confirm_name like '%" . strim($_REQUEST['name']) . "%' $ex_condition  $orderby limit " . $limit);
                $total = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "confirm where confirm_name like '%" . strim($_REQUEST['name']) . "%' $ex_condition");
            } else {
                $kws_div = div_str(trim($_REQUEST['name']));
                foreach ($kws_div as $k => $item) {
                    $kw[$k] = str_to_unicode_string($item);
                }
                $kw_unicode = implode(" ", $kw);
                $list = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "confirm where match(`name_match`) against('" . $kw_unicode . "' IN BOOLEAN MODE) $ex_condition $orderby limit " . $limit);
                $total = $GLOBALS['db']->getOne("select * from " . DB_PREFIX . "confirm where match(`name_match`) against('" . $kw_unicode . "' IN BOOLEAN MODE) $ex_condition");
            }
        } else {
            $list = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "confirm where 1=1 $ex_condition  $orderby limit " . $limit);
            $total = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "confirm where 1=1 $ex_condition");
        }
        $p = new Page($total, '');
        $page = $p->show();

        $sortImg = $sort; //排序图标
        $sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
        $sort = $sort == 'desc' ? 1 : 0; //排序方式
        //模板赋值显示
        $this->assign('sort', $sort);
        $this->assign('order', $order);
        $this->assign('sortImg', $sortImg);
        $this->assign('sortType', $sortAlt);

        $this->assign('list', $list);
        $this->assign("page", $page);
        $this->assign("nowPage", $p->nowPage);

        $this->display();
        return;
    }

    public function add() {
        $this->display();
    }

    public function edit() {
        $id = intval($_REQUEST ['id']);
        $condition['id'] = $id;
        $vo = M(MODULE_NAME)->where($condition)->find();
        $this->assign('vo', $vo);
        $this->display();
    }

    public function foreverdelete() {
        //彻底删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];


        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['name'];
            }
            if ($info)
                $info = implode(",", $info);


            if (M("deal")->where(array('confirm_id' => array('in', explode(',', $id))))->count() > 0) {
                $this->error(l("该商户下还有商品"), $ajax);
            }

            if (M("SupplierLocation")->where(array('confirm_id' => array('in', explode(',', $id))))->count() > 0) {
                $this->error("请先清空所有的分店数据", $ajax);
            }
            //查询子账户
            $sub_accounts = M("SupplierAccount")->field("id,account_name")->where(array('confirm_id' => array('in', explode(',', $id))))->select();
            foreach ($sub_accounts as $k => $v) {
                $f_sub_accounts[] = $v['id'];
            }

            M("SupplierAccount")->where(array('confirm_id' => array('in', explode(',', $id))))->delete();
            M("SupplierAccountAuth")->where(array('confirm_account_id' => array('in', $f_sub_accounts)))->delete();

            M("SupplierMoneyLog")->where(array('confirm_id' => array('in', explode(',', $id))))->delete();
            M("SupplierMoneySubmit")->where(array('confirm_id' => array('in', explode(',', $id))))->delete();


            $list = M(MODULE_NAME)->where($condition)->delete();

            if ($list !== false) {

                save_log($info . l("FOREVER_DELETE_SUCCESS"), 1);
                $this->success(l("FOREVER_DELETE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("FOREVER_DELETE_FAILED"), 0);
                $this->error(l("FOREVER_DELETE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function insert() {
        B('FilterString');
        $data = M("Confirm")->create();
        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/add"));

        if (!check_empty($data['confirm_name'])) {
            $this->error(L("TAGNAME_EMPTY_TIP"));
        }

        // 更新数据
        $log_info = $data['confirm_name'];
        $list = M(MODULE_NAME)->add($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info . L("INSERT_SUCCESS"), 1);
            $this->success(L("INSERT_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info . L("INSERT_FAILED"), 0);
            $this->error(L("INSERT_FAILED"));
        }
    }

    public function update() {
        B('FilterString');
        $data = M(MODULE_NAME)->create();
        $log_info = M(MODULE_NAME)->where("id=" . intval($data['id']))->getField("confirm_name");
        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/edit", array("id" => $data['id'])));
        if (!check_empty($data['confirm_name'])) {
            $this->error(L("SUPPLIER_NAME_EMPTY_TIP"));
        }

        // 更新数据
        $list = M(MODULE_NAME)->save($data);

        if (false !== $list) {
            //成功提示
            save_log($log_info . L("UPDATE_SUCCESS"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info . L("UPDATE_FAILED"), 0);
            $this->error(L("UPDATE_FAILED"), 0, $log_info . L("UPDATE_FAILED"));
        }
    }

}

?>