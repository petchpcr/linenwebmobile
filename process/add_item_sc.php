<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_items($conn, $DATA)
{
    $count = 0;
    $DocNo = $DATA["DocNo"];
    $ItemCode = $DATA['ItemCode'];
    $return['ItemCode'] = $ItemCode;
    $Sql = "SELECT  item.ItemName,
                    item.ItemCode,
                    shelfcount_detail.ParQty,
                    shelfcount_detail.CcQty,
                    shelfcount_detail.TotalQty

            FROM    item,
                    shelfcount_detail

            WHERE   item.ItemCode = shelfcount_detail.ItemCode

            AND     shelfcount_detail.DocNo = '$DocNo'
            ORDER BY item.ItemName";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['ItemCode'] = $Result['ItemCode'];
        $return[$count]['ItemName'] = $Result['ItemName'];
        $return[$count]['ParQty'] = $Result['ParQty'];
        $return[$count]['CcQty'] = $Result['CcQty'];
        $return[$count]['TotalQty'] = $Result['TotalQty'];
        $count++;
    }

    $return['cnt'] = $count;

    $return['status'] = "success";
    $return['form'] = "load_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function choose_items($conn, $DATA)
{
    $Search = $DATA["Search"];
    $DepCode = $DATA["DepCode"];
    $count = 0;

    $Sql = "SELECT DISTINCT     par_item_stock.ItemCode,ItemName,item.UnitCode
    
            FROM                par_item_stock,item

            WHERE               DepCode='$DepCode'
            AND                 par_item_stock.ItemCode=item.ItemCode
            AND                 item.ItemName LIKE '%$Search%' 
            ORDER BY            item.ItemName ASC";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['ItemCode']    =  $Result['ItemCode'];
        $return[$count]['ItemName']    =  $Result['ItemName'];
        $return[$count]['UnitCode']    =  $Result['UnitCode'];
        $count++;
    }
    $return['cnt'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "choose_items";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "choose_items";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function get_par($conn, $DATA)
{
    $DepCode = $DATA['DepCode'];
    $new_code = $DATA['new_code'];
    $new_i_code = explode(",", $new_code);
    $new_par = array();
    $cnt_arr = sizeof($new_i_code, 0);
    $count = 0;
    foreach ($new_i_code as $value) {
        $Sql = "SELECT DISTINCT ParQty FROM par_item_stock WHERE DepCode='$DepCode' AND par_item_stock.ItemCode= '$value'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        array_push($new_par, $Result['ParQty']);
        $count++;
    }
    $return['ar_par'] = implode(",", $new_par);

    if ($count == $cnt_arr) {
        $return['status'] = "success";
        $return['form'] = "get_par";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "get_par";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function add_item($conn, $DATA)
{
    $DocNo = $DATA['DocNo'];
    $old_code = $DATA['old_code'];
    $old_qty = $DATA['old_qty'];
    $old_par = $DATA['old_par'];
    $old_order = $DATA['old_order'];
    $new_code = $DATA['new_code'];
    $new_qty = $DATA['new_qty'];
    $new_par = $DATA['new_par'];
    $new_order = $DATA['new_order'];
    $Userid = $_SESSION['Userid'];

    $ar_old_code = explode(",", $old_code);
    $ar_old_qty = explode(",", $old_qty);
    $ar_old_par = explode(",", $old_par);
    $ar_old_order = explode(",", $old_order);
    $ar_new_code = explode(",", $new_code);
    $ar_new_qty = explode(",", $new_qty);
    $ar_new_par = explode(",", $new_par);
    $ar_new_order = explode(",", $new_order);

    $Sql = "DELETE FROM shelfcount_detail WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    foreach ($ar_old_code as $i => $val) {
        $Sql = "INSERT INTO shelfcount_detail(`DocNo`,`ItemCode`,`UnitCode`,`ParQty`,`CcQty`,`TotalQty`) 
                VALUES ('$DocNo','$val',1,$ar_old_par[$i],$ar_old_qty[$i],$ar_old_order[$i]) ";
        $return[$i]['CcQty'] = $ar_old_qty[$i];
        mysqli_query($conn, $Sql);
    }

    foreach ($ar_new_code as $i => $val) {
        $Sql = "INSERT INTO shelfcount_detail(`DocNo`,`ItemCode`,`UnitCode`,`ParQty`,`CcQty`,`TotalQty`) 
                VALUES ('$DocNo','$val',1,$ar_new_par[$i],$ar_new_qty[$i],$ar_new_order[$i]) ";
        $return[$i]['CcQty'] = $ar_new_qty[$i];
        mysqli_query($conn, $Sql);
    }

    $Sql = "UPDATE shelfcount SET Modify_Code = '$Userid', Modify_Date = NOW(), IsStatus = 0 WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    $return['status'] = "success";
    $return['form'] = "add_item";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function del_doc($conn, $DATA)
{
    $DocNo = $DATA['DocNo'];
    $Sql = "DELETE FROM shelfcount WHERE DocNo = '$DocNo'";
    if (mysqli_query($conn, $Sql)) {
        $return['status'] = "success";
        $return['form'] = "del_doc";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "del_doc";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_items') {
        load_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'choose_items') {
        choose_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'get_par') {
        get_par($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_item') {
        add_item($conn, $DATA);
    } else if ($DATA['STATUS'] == 'del_doc') {
        del_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
