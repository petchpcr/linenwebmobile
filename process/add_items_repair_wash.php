<?php

use Mpdf\Tag\P;

session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_items($conn, $DATA)
{
    $count = 0;
    $DocNo = $DATA["DocNo"];
    // $Unweight = $DATA["Unweight"];

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
        // if ($Unweight == 1) {
        //     $weight = 0;
        // }
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

function choose_items($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $Search = $DATA["Search"];
    $DocNo = $DATA["DocNo"];
    $Ar_ItemCode = array();
    $count = 0;

    $Sql = "SELECT r.ItemCode 
            FROM repair_wash_detail r 
            INNER JOIN item i ON r.ItemCode = i.ItemCode 
            WHERE r.DocNo = '$DocNo' 
            GROUP BY r.ItemCode 
            ORDER BY i.ItemName";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        array_push($Ar_ItemCode,$Result['ItemCode']);
    }

    $Sql = "SELECT DISTINCT     item_stock.ItemCode,ItemName,item.UnitCode

            FROM                item_stock,item

            WHERE               item.HptCode = '$siteCode'
            AND                 item_stock.ItemCode = item.ItemCode
            AND                 item.ItemName LIKE '%$Search%' 
            AND                 item.IsClean != 1 
            ORDER BY            item.ItemName ASC";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $have = 0;
        foreach ($Ar_ItemCode as $key => $ItemCode) {
            if ($ItemCode == $Result['ItemCode']) {
                $have = 1;
            }
        }
        if ($have == 0) {
            $return[$count]['ItemCode']    =  $Result['ItemCode'];
            $return[$count]['ItemName']    =  $Result['ItemName'];
            $return[$count]['UnitCode']    =  $Result['UnitCode'];
            $count++;
        }
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

function change_value($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $item = $DATA["item"];
    $qty = $DATA["qty"];
    if ($DATA["weight"] > 0) {
        $insert = ",`Weight`";
        $value = "," . $DATA["weight"];
    } else {
        $insert = "";
        $value = "";
    }
    $Sql = "INSERT INTO repair_wash_detail(`DocNo`,`ItemCode`,`UnitCode`,`Qty`" . $insert . ") 
            VALUES ('$DocNo','$item',1,$qty" . $value . ") ";

    if (mysqli_query($conn, $Sql)) {
        $return['status'] = "success";
        $return['form'] = "change_value";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['Sql'] = $Sql;
        $return['status'] = "failed";
        $return['form'] = "change_value";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function del_items($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $item = $DATA["item"];

    $Sql = "DELETE FROM repair_wash_detail WHERE DocNo = '$DocNo' AND ItemCode = '$item'";

    if (mysqli_query($conn, $Sql)) {
        $return['status'] = "success";
        $return['form'] = "del_items";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['Sql'] = $Sql;
        $return['status'] = "failed";
        $return['form'] = "del_items";
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
    $DocNo = $DATA['DocNo'];
    $Userid = $DATA['Userid'];
    $ar_item = $DATA['ar_item'];
    $ar_weight = $DATA['ar_weight'];
    $ar_qty = $DATA['ar_qty'];
    $Total = 0;

    foreach ($ar_item as $key => $ItemCode) {
        $Sql = "UPDATE repair_wash_detail SET Qty = '$ar_qty[$key]', Weight = '$ar_weight[$key]' WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        mysqli_query($conn, $Sql);
        $Total += $ar_weight[$key];
    }

    $Sql = "UPDATE repair_wash SET Total = $Total, Modify_Code = '$Userid', Modify_Date = NOW(), IsStatus = 1 WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

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
    } else if ($DATA['STATUS'] == 'change_value') {
        change_value($conn, $DATA);
    } else if ($DATA['STATUS'] == 'del_items') {
        del_items($conn, $DATA);
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
