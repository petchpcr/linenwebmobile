<?php

use Mpdf\Tag\P;

session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function choose_items($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $DepCode = $DATA["DepCode"];
    $Search = $DATA["Search"];
    $refDoc = $DATA["refDoc"];

    $Sql = "SELECT department.DepName,site.HptName
        FROM department 
        INNER JOIN site ON department.HptCode = site.HptCode 
        WHERE department.DepCode = '$DepCode'";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['DepName']    =  $Result['DepName'];
        $return['HptName']    =  $Result['HptName'];
    }

    $count = 0;
    $Sql = "SELECT DISTINCT     item_stock.ItemCode,ItemName,item.UnitCode

                FROM                item_stock,item

                WHERE               item.HptCode = '$siteCode'
                AND                 item_stock.ItemCode = item.ItemCode
                AND                 item.ItemName LIKE '%$Search%' 
                AND                 item.IsClean != 1 
                ORDER BY            item.ItemName ASC";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['ItemCode']    =  $Result['ItemCode'];
        $return[$count]['ItemName']    =  $Result['ItemName'];
        $return[$count]['UnitCode']    =  $Result['UnitCode'];
        $count++;
    }
    $return['cnt'] = $count;
    $return['Sql'] = $Sql;

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

function load_items($conn, $DATA)
{
    $count = 0;
    $DocNo = $DATA["DocNo"];
    $refDoc = $DATA["refDoc"];
    $Unweight = $DATA["Unweight"];

    $Sql = "SELECT SignFac,SignNH FROM repair_wash WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['SignFac'] = $Result['SignFac'];
    $return['SignNH'] = $Result['SignNH'];

    $Sql = "SELECT repair_wash_detail.ItemCode,
                        item.ItemName,
                        repair_wash_detail.UnitCode,
                        repair_wash_detail.Qty,
                        repair_wash_detail.Weight  
                FROM repair_wash_detail,
                     item 
                WHERE DocNo = '$DocNo' 
                AND	  item.ItemCode = repair_wash_detail.ItemCode
                ORDER BY ItemName ASC";
    $return['Sql'] = $Sql;

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['ItemCode'] = $Result['ItemCode'];
        $return[$count]['ItemName'] = $Result['ItemName'];
        $return[$count]['UnitCode'] = $Result['UnitCode'];
        $return[$count]['Qty'] = $Result['Qty'];
        $weight = $Result['Weight'];
        if ($Unweight == 1) {
            $weight = 0;
        }
        $return[$count]['Weight'] = $weight;
        $count++;
    }
    $return['count'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "load_items";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "load_items";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function check_signature($conn, $DATA) {
    $DocNo = $DATA["DocNo"];

    $Sql = "SELECT SignFac,SignNH FROM repair_wash WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['SignFac'] = $Result['SignFac'];
    $return['SignNH'] = $Result['SignNH'];
    $boolean = true;

    if ($boolean) {
        $return['status'] = "success";
        $return['form'] = "check_signature";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "check_signature";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function add_item($conn, $DATA)
{
    $lastsave = $DATA['lastsave'];
    $DocNo = $DATA['DocNo'];
    $Userid = $DATA['Userid'];
    $refDocNo = $DATA['refDocNo'];
    $arr_old_i = $DATA['old_i'];
    $arr_old_qty = $DATA['old_qty'];
    $arr_old_unit = $DATA['old_unit'];
    $arr_old_weight = $DATA['old_weight'];
    $arr_new_i = $DATA['new_i'];
    $arr_new_qty = $DATA['new_qty'];
    $arr_new_unit = $DATA['new_unit'];
    $arr_new_weight = $DATA['new_weight'];
    $arr_del_i = $DATA['del_i'];

    $old_i = explode(",", $arr_old_i);
    $old_qty = explode(",", $arr_old_qty);
    $old_unit = explode(",", $arr_old_unit);
    $old_weight = explode(",", $arr_old_weight);
    $new_i = explode(",", $arr_new_i);
    $new_qty = explode(",", $arr_new_qty);
    $new_unit = explode(",", $arr_new_unit);
    $new_weight = explode(",", $arr_new_weight);
    $del_i = explode(",", $arr_del_i);

    $cnt_old = sizeof($old_i, 0);
    $cnt_new = sizeof($new_i, 0);
    $cnt_del = sizeof($del_i, 0);

    for ($i = 0; $i < $cnt_del; $i++) {
        $Sql = "DELETE FROM repair_wash_detail WHERE DocNo = '$DocNo' AND ItemCode = '$del_i[$i]'";
        mysqli_query($conn, $Sql);
    }

    for ($i = 0; $i < $cnt_old; $i++) {
        $Sql = "UPDATE repair_wash_detail SET Weight = $old_weight[$i],Qty=$old_qty[$i] WHERE DocNo = '$DocNo' AND ItemCode = '$old_i[$i]'";
        mysqli_query($conn, $Sql);
    }

    for ($i = 0; $i < $cnt_new; $i++) {
        $Sql = "INSERT INTO repair_wash_detail(`DocNo`,`ItemCode`,`UnitCode`,`Qty`,`Weight`) 
                    VALUES ('$DocNo','$new_i[$i]',$new_unit[$i],$new_qty[$i],$new_weight[$i]) ";
        $return[$i]['Weight'] = $new_weight[$i];
        mysqli_query($conn, $Sql);
    }

    $Sql = "SELECT SUM(Weight) AS total FROM repair_wash_detail WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $total = $Result['total'];

    $Sql = "UPDATE repair_wash SET Total = $total, Modify_Code = '$Userid', Modify_Date = NOW(), IsStatus = 1,RefDocNo = '$refDocNo' WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    $return['lastsave'] = $lastsave;
    $return['status'] = "success";
    $return['form'] = "add_item";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function del_back($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $RefDocNo = $DATA["refDoc"];
    
    $Sql = "DELETE FROM repair_wash WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    $Sql = "DELETE FROM repair_wash_detail WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    $Sql = "UPDATE clean SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
    mysqli_query($conn, $Sql);

    $return['Sql'] = $Sql;
    $return['status'] = "success";
    $return['form'] = "del_back";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_items') {
        load_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'check_signature') {
        check_signature($conn, $DATA);
    } else if ($DATA['STATUS'] == 'choose_items') {
        choose_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_item') {
        add_item($conn, $DATA);
    } else if ($DATA['STATUS'] == 'del_back') {
        del_back($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
