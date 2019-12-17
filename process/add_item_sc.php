<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_dep($conn, $DATA)
{
    $DepCode = $DATA["DepCode"];
    $Sql = "SELECT DepName FROM department WHERE DepCode = '$DepCode'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['Sql'] = $Sql;
    $return['DepName'] = $Result['DepName'];

    $return['status'] = "success";
    $return['form'] = "load_dep";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

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

    // if ($count > 0) {
        $return['status'] = "success";
    // } else {
    //     $return['status'] = "failed";
    // }
    $return['form'] = "load_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function Add_all_items($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $DepCode = $DATA["DepCode"];
    $siteCode = $DATA["siteCode"];
    $count = 0;

    $Sql = "SELECT COUNT(*) AS cntHave FROM shelfcount_detail WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $cntHave = $Result['cntHave'];

    if ($cntHave == 0) {
        $Sql = "SELECT
                par_item_stock.RowID,
                site.HptName,
                department.DepName,
                item_category.CategoryName,
                item.ItemCode,
                item.ItemName,
                item.UnitCode,
                item_unit.UnitName,
                par_item_stock.ParQty,
                par_item_stock.TotalQty,
                item.Weight
                FROM site
                INNER JOIN department ON site.HptCode = department.HptCode
                INNER JOIN par_item_stock ON department.DepCode = par_item_stock.DepCode
                INNER JOIN item ON par_item_stock.ItemCode = item.ItemCode
                INNER JOIN item_category ON item.CategoryCode= item_category.CategoryCode
                INNER JOIN item_unit ON item.UnitCode = item_unit.UnitCode
                WHERE  par_item_stock.DepCode = '$DepCode' 
                AND par_item_stock.HptCode = '$siteCode' 
                AND item.IsActive = 1
                GROUP BY item.ItemCode
                ORDER BY item.ItemName ASC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $ItemCode = $Result['ItemCode'];
            $ParQty = $Result['ParQty'];
            
            $aSql = "INSERT INTO shelfcount_detail(`DocNo`,`ItemCode`,`UnitCode`,`ParQty`,`CcQty`,`TotalQty`) 
                    VALUES ('$DocNo','$ItemCode',1,$ParQty,0,0) ";
            mysqli_query($conn, $aSql);

            $count++;
        }
    }
    
    $return['cnt'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
    } else {
        $return['Sql'] = $Sql;
        $return['status'] = "failed";
    }
    $return['form'] = "Add_all_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function choose_items($conn, $DATA)
{
    $Search = $DATA["Search"];
    $DepCode = $DATA["DepCode"];
    $DocNo = $DATA["DocNo"];
    $Ar_ItemCode = array();
    $count = 0;

    $Sql = "SELECT sh.ItemCode 
            FROM shelfcount_detail sh 
            INNER JOIN item i ON sh.ItemCode = i.ItemCode 
            WHERE sh.DocNo = '$DocNo' 
            GROUP BY sh.ItemCode 
            ORDER BY i.ItemName";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        array_push($Ar_ItemCode,$Result['ItemCode']);
    }

    $Sql = "SELECT DISTINCT     par_item_stock.ItemCode,ItemName,item.UnitCode
    
            FROM                par_item_stock,item

            WHERE               DepCode='$DepCode'
            AND                 par_item_stock.ItemCode=item.ItemCode
            AND                 item.ItemName LIKE '%$Search%' 
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

function select_chk($conn, $DATA)
{
    $DocNo = $DATA['DocNo'];
    $DepCode = $DATA['DepCode'];
    $new_i_code = $DATA['new_i_code'];
    $new_i_qty = $DATA['new_i_qty'];
    $count = 0;

    foreach ($new_i_code as $i => $ItemCode) {
        $Sql = "SELECT DISTINCT ParQty FROM par_item_stock WHERE DepCode='$DepCode' AND par_item_stock.ItemCode= '$ItemCode'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $ParQty = $Result['ParQty'];

        $Sql = "INSERT INTO shelfcount_detail(`DocNo`,`ItemCode`,`UnitCode`,`ParQty`,`CcQty`,`TotalQty`) 
                VALUES ('$DocNo','$ItemCode',1,$ParQty,$new_i_qty[$i],0) ";
                
        if (mysqli_query($conn, $Sql)){
            $count++;
        }
    }
    $return['cnt'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "select_chk";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "select_chk";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function add_item($conn, $DATA)
{
    $DocNo = $DATA['DocNo'];
    $ar_item = $DATA['ar_item'];
    $ar_qty = $DATA['ar_qty'];
    $Userid = $_SESSION['Userid'];

    foreach ($ar_item as $i => $ItemCode) {
        $Sql = "UPDATE shelfcount_detail SET CcQty = $ar_qty[$i] WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        mysqli_query($conn, $Sql);
    }

    $Sql = "UPDATE shelfcount SET Modify_Code = '$Userid', Modify_Date = NOW(), ScEndTime = NOW(), IsStatus = 0, IsMobile = 1 WHERE DocNo = '$DocNo'";
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
    $Sql = "DELETE FROM shelfcount_detail WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
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

    if ($DATA['STATUS'] == 'load_dep') {
        load_dep($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_items') {
        load_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'Add_all_items') {
        Add_all_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'choose_items') {
        choose_items($conn, $DATA);
    } else if ($DATA['STATUS'] == 'get_par') {
        get_par($conn, $DATA);
    } else if ($DATA['STATUS'] == 'select_chk') {
        select_chk($conn, $DATA);
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
