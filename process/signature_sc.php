<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

$DocNo = $_POST['DocNo'];
$SignCode = $_POST['SignCode'];
$SignFnc = $_POST['SignFnc'];
$return['SignFnc'] = $SignFnc;

if ($SignFnc == 'start_send') {
    $Sql = "UPDATE shelfcount SET DvStartTime = NOW(),signStart = '$SignCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    
    echo json_encode($return);
    mysqli_close($conn);
    die;

} else if ($SignFnc == 'end_send') {
    $Sql = "UPDATE shelfcount SET signature = '$SignCode',IsStatus = 4  WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    $count = 0;
    $cnk = 0;
    $Sql1 = "SELECT department.HptCode, shelfcount.DepCode 
    FROM shelfcount  INNER JOIN department ON shelfcount.DepCode = department.DepCode  WHERE shelfcount.DocNo = '$DocNo'";
    $meQuery1 = mysqli_query($conn, $Sql1);
    while ($Result1 = mysqli_fetch_assoc($meQuery1)) {
        $HptCode = $Result1['HptCode'];
        $SCDepCode = $Result1['DepCode'];
        $return['HptCode'] = $HptCode;
        $return['SCDepCode'] = $SCDepCode;
    }
    $Sql2 = "SELECT department.DepCode  FROM department WHERE department.HptCode = '$HptCode' AND department.IsDefault = 1 AND department.IsStatus = 0";
    $meQuery2 = mysqli_query($conn, $Sql2);
    while ($Result2 = mysqli_fetch_assoc($meQuery2)) {
        $DepCode = $Result2['DepCode'];
        $return['DepCode'] = $DepCode;
    }

    $Sql3 = "SELECT
        shelfcount_detail.Id,
        item.ItemName,
        shelfcount_detail.ItemCode,
        shelfcount_detail.ParQty,
        shelfcount_detail.CcQty,
        shelfcount_detail.TotalQty
        FROM item
        INNER JOIN item_unit ON item.UnitCode = item_unit.UnitCode
        INNER JOIN shelfcount_detail ON shelfcount_detail.ItemCode = item.ItemCode
        INNER JOIN shelfcount ON shelfcount.DocNo = shelfcount_detail.DocNo
        WHERE shelfcount_detail.DocNo = '$DocNo'
        ORDER BY shelfcount_detail.Id DESC";

    $return['Sql3'] = $Sql3;
    $meQuery3 = mysqli_query($conn, $Sql3);
    while ($Result3 = mysqli_fetch_assoc($meQuery3)) {
        $ItemCode = $Result3['ItemCode'];
        $Oder = $Result3['TotalQty'];
        $return['ItemCode'] = $ItemCode;
        $return['Oder'] = $Oder;

        $Sql4 = "SELECT par_item_stock.TotalQty  
        FROM par_item_stock 
        INNER JOIN department ON department.DepCode = par_item_stock.DepCode
        INNER JOIN site ON site.HptCode = department.HptCode
        WHERE par_item_stock.ItemCode = '$ItemCode'
        AND site.HptCode = '$HptCode' AND department.IsDefault = 1 LIMIT 1";
        $return['Sql4'] = $Sql4;
        $meQuery4 = mysqli_query($conn, $Sql4);
        while ($Result4 = mysqli_fetch_assoc($meQuery4)) {
            $QtyCenter = $Result4['TotalQty'] == null ? 0 : $Result4['TotalQty'];
            $return['QtyCenter'] = $QtyCenter;
            // if ($QtyCenter > $Oder || $QtyCenter == 0) {
                $return['test'] = 1;
                $updateQty = "UPDATE par_item_stock SET TotalQty = TotalQty + $Oder WHERE ItemCode = '$ItemCode' AND DepCode = '$SCDepCode'";
                // mysqli_query($conn, $updateQty);
                $return['updateQty'] = $updateQty;
                if (mysqli_query($conn, $updateQty)) {
                    $return['ok'] = 2;
                }
            // }
            // else{
            //     $return['test'] = 00;
            // }
        }
    }
    echo json_encode($return);
    mysqli_close($conn);
    die;

}