<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function load_process($conn, $DATA){
        $count = 0;
        $siteCode = $DATA["siteCode"];
        $FacCode = $_SESSION["FacCode"];
        $DocNo = $DATA["DocNo"];
        $boolean = false;
        
        $Sql = "SELECT Signature AS IsSign FROM site WHERE HptCode = '$siteCode'";

        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $IsSign = $Result['IsSign'];
        $return['IsSign'] = $IsSign;

        $Sql = "SELECT
                    shelfcount.DocNo,
                    shelfcount.ScStartTime,
                    shelfcount.ScEndTime,
                    TIMEDIFF(shelfcount.ScEndTime,shelfcount.ScStartTime) AS ScUseTime,
                    shelfcount.PkStartTime,
                    shelfcount.PkEndTime,
                    TIMEDIFF(shelfcount.PkEndTime,shelfcount.PkStartTime) AS PkUseTime,
                    shelfcount.DvStartTime,
                    shelfcount.DvEndTime,
                    TIMEDIFF(shelfcount.DvEndTime,shelfcount.DvStartTime) AS DvUseTime,
                    shelfcount.IsStatus,
                    shelfcount.signature,
                    shelfcount.signStart,
                    department.HptCode
                FROM
                    shelfcount
                INNER JOIN department ON department.DepCode = shelfcount.DepCode
                WHERE shelfcount.DocNo = '$DocNo'";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['DocNo'] = $Result['DocNo'];
            $return['ScStartTime'] = $Result['ScStartTime'];
            $return['ScEndTime'] = $Result['ScEndTime'];
            $return['ScUseTime'] = $Result['ScUseTime'];
            $return['PkStartTime'] = $Result['PkStartTime'];
            $return['PkEndTime'] = $Result['PkEndTime'];
            $return['PkUseTime'] = $Result['PkUseTime'];
            $return['DvStartTime'] = $Result['DvStartTime'];
            $return['DvEndTime'] = $Result['DvEndTime'];
            $return['DvUseTime'] = $Result['DvUseTime'];
            $return['IsStatus'] = $Result['IsStatus'];
            $return['Signature'] = $Result['signature'];
            $return['signStart'] = $Result['signStart'];
            $return['HptCode'] = $Result['HptCode'];

            $count++;
            $boolean = true;
        }
        
        if($boolean){            
            $return['status'] = "success";
            $return['form'] = "load_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function insert_process($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "INSERT INTO process (DocNo) VALUES ('$DocNo') ";
    
        if($meQuery = mysqli_query($conn,$Sql)){

            $return['status'] = "success";
            $return['form'] = "insert_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "insert_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $Sql = "UPDATE process SET WashStartTime = NOW(),IsStatus = 1 WHERE DocNo = '$DocNo'";
        
        if(mysqli_query($conn,$Sql)){
            $Sql = "UPDATE $From SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
        else {
            $return['status'] = "failed";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $boolean = false;

        $Sql = "UPDATE process SET WashEndTime = NOW() WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(WashEndTime,WashStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET WashUseTime = '$UseTime',IsStatus = 2 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE $From SET IsProcess = 2 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET PkStartTime = NOW(),IsStatus = 2 WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "start_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "start_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET PkEndTime = NOW(),IsStatus = 3 WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "end_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "end_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_send($conn, $DATA){
        $UserID = $_SESSION['Userid'];
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET DvStartTime = NOW(),UserID = '$UserID' WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "start_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "start_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_send($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $DocNo = $DATA["DocNo"];

        $Sql = "SELECT Signature AS IsSign FROM site WHERE HptCode = '$siteCode'";

        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $IsSign = $Result['IsSign'];
        $return['IsSign'] = $IsSign;

        if ($IsSign == 1) {
            $Sql = "UPDATE shelfcount SET DvEndTime = NOW() WHERE DocNo = '$DocNo'";
        } else {
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
            
            $Sql = "UPDATE shelfcount SET DvEndTime = NOW(),IsStatus = 4 WHERE DocNo = '$DocNo'";
        }

        if (mysqli_query($conn,$Sql)) {
            $return['status'] = "success";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function view_detail($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $count = 0;
        $Sql = "SELECT ItemName,TotalQty 
                FROM shelfcount_detail 
                INNER JOIN item ON item.ItemCode = shelfcount_detail.ItemCode
                WHERE DocNo = '$DocNo' 
                AND TotalQty != 0";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['ItemName'][$count] = $Result['ItemName'];
            $return['TotalQty'][$count] = $Result['TotalQty'];
            $count++;
        }
        $return['cnt'] = $count;

        if ($count > 0) {
            $return['status'] = "success";
            $return['form'] = "view_detail";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "view_detail";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_process') {
            load_process($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'insert_process') {
            insert_process($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_wash') {
            start_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'stop_wash') {
            stop_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'do_end_wash') {
            do_end_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_wash') {
            end_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_pack') {
            start_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_pack') {
            end_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_send') {
            start_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_send') {
            end_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'view_detail') {
            view_detail($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        }
        
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
