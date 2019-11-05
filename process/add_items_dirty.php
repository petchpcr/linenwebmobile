<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function choose_items($conn, $DATA){
        $Search = $DATA["Search"];
        $siteCode = $DATA["siteCode"];
        $count = 0;

        $Sql = "SELECT DISTINCT ItemCode,ItemName  
                FROM    item 
                WHERE   IsActive = 1 
                AND     (IsDirtyBag = 1 OR IsDirtyBag = 2)
                AND     (HptCode = '$siteCode' OR HptCode = '0')
                AND     ItemName LIKE '%$Search%'
                ORDER BY ItemName ASC";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['ItemCode']	=  $Result['ItemCode'];
            $return[$count]['ItemName']	=  $Result['ItemName'];
            $return[$count]['UnitCode']	=  $Result['UnitCode'];
            $count++;
        }
        $return['Sql'] = $Sql;
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

    function load_items($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $return['FromDelRound'] = $DATA["FromDelRound"];

        $Sql = "SELECT SignFac,SignNH FROM dirty WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $return['SignFac'] = $Result['SignFac'];
        $return['SignNH'] = $Result['SignNH'];

        $Sql = "SELECT dirty_detail.ItemCode,
                        item.ItemName,
                        dirty_detail.RequestName,
                        dirty_detail.UnitCode,
                        dirty_detail.Qty,
                        dirty_detail.Weight,
                        dirty_detail.DepCode  
                FROM dirty_detail
                LEFT JOIN item ON item.ItemCode = dirty_detail.ItemCode 
                WHERE DocNo = '$DocNo'
                ORDER BY ItemName ASC";
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            if ($Result['ItemCode'] == 'HDL') {
                $return[$count]['ItemCode'] = $Result['RequestName'];
            } else {
                $return[$count]['ItemCode'] = $Result['ItemCode'];
            }
            $return[$count]['ItemName'] = $Result['ItemName'];
            $return[$count]['UnitCode'] = $Result['UnitCode'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $return[$count]['DepCode'] = $Result['DepCode'];
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

    function load_dep($conn, $DATA){
        $siteCode = $DATA['siteCode'];
        $Search = $DATA['Search'];
        $return['dep_search'] = $DATA['dep_search'];
        $count = 0;

        $Sql = "SELECT DepCode,DepName FROM department 
        WHERE HptCode = '$siteCode' 
        AND     DepName LIKE '%$Search%'
        ORDER BY DepName ASC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['DepCode'] = $Result['DepCode'];
            $return[$count]['DepName'] = $Result['DepName'];
            $count++;
        }
        $return['count'] = $count;
        
        if ($count > 0) {
            $return['status'] = "success";
            $return['form'] = "load_dep";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_dep";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function add_item($conn, $DATA){
        $NotBack = $DATA['NotBack'];
        $return['NotBack'] = $NotBack;
        if ($NotBack == 1) {
            $zero = -1;
        } else {
            $zero = 0;
        }
        $DocNo = $DATA['DocNo'];
        $mul_qty = $DATA['mul_qty'];
        $mul_weight = $DATA['mul_weight'];
        $Userid = $_SESSION['Userid'];
        // $Sql = "DELETE FROM dirty_detail WHERE DocNo = '$DocNo'";
        // mysqli_query($conn,$Sql);
        $count = 0;
        
        foreach($mul_qty as $DepCode => $item){
            foreach($item as $ItemCode => $qty){
                $Sql = "SELECT id FROM dirty_detail WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
                $meQuery = mysqli_query($conn,$Sql);
                $Result = mysqli_fetch_assoc($meQuery);
                $id = $Result['id'];
                
                $weight = $mul_weight[$DepCode][$ItemCode];

                $Sql = "SELECT COUNT(*) AS cnt1 FROM dirty_detail WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
                $meQuery = mysqli_query($conn,$Sql);
                $Result = mysqli_fetch_assoc($meQuery);
                $cnt1 = $Result['cnt1'];
                if ($cnt1 > 0) { // ถ้ามีอยู่แล้ว
                    if ($qty > $zero) {
                        $Sql = "UPDATE dirty_detail SET Qty = $qty, Weight = '$weight' WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
                        mysqli_query($conn,$Sql);
                    } else {
                        $Sql = "DELETE FROM dirty_detail_round WHERE RowID = '$id'";
                        mysqli_query($conn,$Sql);

                        $Sql = "DELETE FROM dirty_detail WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
                        mysqli_query($conn,$Sql);
                    }

                } else { // ถ้าไม่มี
                    $Sql = "INSERT INTO dirty_detail(`DocNo`,`ItemCode`,`DepCode`,`UnitCode`,`Weight`,`Qty`) 
                            VALUES ('$DocNo','$ItemCode','$DepCode',1,$weight,$qty) ";
                    mysqli_query($conn,$Sql);
                    $return[$count]['Sql'] = $Sql;
                    $count++;
                }
                
                    
                
            }
        }

        $Sql = "SELECT SUM(Weight) AS total FROM dirty_detail WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $total = $Result['total'];

        $Sql = "UPDATE dirty SET Total = $total, Modify_Code = '$Userid', Modify_Date = NOW(), IsStatus = 1 WHERE DocNo = '$DocNo'";
        $return['Last Update'] = $Sql;
        mysqli_query($conn,$Sql);

        $return['status'] = "success";
        $return['form'] = "add_item";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }

    function del_back($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Menu = $DATA["Menu"];
        $return['Menu'] = $Menu;
        $Sql = "DELETE FROM $Menu WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);
        $Sql = "DELETE FROM ".$Menu."_detail WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $return['status'] = "success";
        $return['form'] = "del_back";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
    
    function item_handler($conn, $DATA) {
        $DocNo = $DATA['DocNo'];
        $RequestName = $DATA['RequestName'];
        $return['RequestName'] = $RequestName;
        $now_dep = $DATA['now_dep'];
        $return['now_dep'] = $now_dep;
        
        $count = "SELECT COUNT(*) as cnt FROM dirty_detail WHERE DocNo = '$DocNo' AND DepCode = '$now_dep' AND RequestName = '$RequestName'";
        $meQuery = mysqli_query($conn, $count);
        $Result = mysqli_fetch_assoc($meQuery);
        if($Result['cnt'] == 0){
            $Insert = "INSERT dirty_detail (DocNo, RequestName,ItemCode, UnitCode, DepCode, Qty)VALUES('$DocNo', '$RequestName','HDL', 1, '$now_dep', 1)";
            mysqli_query($conn, $Insert);
        }

        $return['status'] = "success";
        $return['form'] = "item_handler";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }

    function edit_round($conn, $DATA) {
        $DocNo = $DATA["DocNo"];
        $dep = $DATA["dep"];
        $return['dep'] = $dep;
        $item = $DATA["item"];
        $return['item'] = $item;
        $count = 0;
        
        $Sql = "SELECT id,RowID,Qty,Weight FROM dirty_detail_round WHERE DocNo = '$DocNo' AND DepCode = '$dep' AND ItemCode = '$item'";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['id'] = $Result['id'];
            $return[$count]['RowID'] = $Result['RowID'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $count++;
        }
        $return['Sql'] = $Sql;
        $return['cnt'] = $count;
        
        if ($count > 0) {
            $return['status'] = "success";
            $return['form'] = "edit_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "edit_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function add_round($conn, $DATA) {
        $DocNo = $DATA["DocNo"];
        $dep = $DATA["dep"];
        $item = $DATA["item"];
        $HDL = $DATA["HDL"];
        $qty = $DATA["qty"];
        $weight = $DATA["weight"];

        $return['dep'] = $dep;
        $return['item'] = $item;

        if ($HDL == 1) {
            $Sql = "SELECT count(*) AS cnt_id FROM dirty_detail WHERE DocNo = '$DocNo' AND DepCode = '$dep' AND ItemCode = 'HDL' AND RequestName = '$item'";
        } else {
            $Sql = "SELECT count(*) AS cnt_id FROM dirty_detail WHERE DocNo = '$DocNo' AND DepCode = '$dep' AND ItemCode = '$item'";
        }

        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $return['Sql'] = $Sql;
        $return['cnt_id'] = $Result['cnt_id'];
        if ($Result['cnt_id'] == 0) {
            $Sql = "INSERT INTO dirty_detail(`DocNo`,`ItemCode`,`DepCode`,`UnitCode`,`Weight`,`Qty`) 
            VALUES ('$DocNo','$item','$dep',1,0,0) ";
            mysqli_query($conn,$Sql);
        }

        if ($HDL == 1) {
            $Sql = "SELECT id FROM dirty_detail WHERE DocNo = '$DocNo' AND DepCode = '$dep' AND ItemCode = 'HDL' AND RequestName = '$item'";
        } else {
            $Sql = "SELECT id FROM dirty_detail WHERE DocNo = '$DocNo' AND DepCode = '$dep' AND ItemCode = '$item'";
        }
        
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $RowID = $Result['id'];

        $Sql = "INSERT INTO dirty_detail_round(`DocNo`,`ItemCode`,`DepCode`,`RowID`,`Weight`,`Qty`) 
                VALUES ('$DocNo','$item','$dep',$RowID,$weight,$qty) ";
        if(mysqli_query($conn,$Sql)){
            $Sql2 = "SELECT SUM(Qty) AS sum_qty,SUM(Weight) AS sum_weight FROM dirty_detail_round WHERE RowID = '$RowID'";
            $meQuery = mysqli_query($conn,$Sql2);
            $Result = mysqli_fetch_assoc($meQuery);
            $sum_qty = $Result['sum_qty'];
            $sum_weight = $Result['sum_weight'];
            $return['sum_qty'] = $sum_qty;
            $return['Sql2'] = $Sql2;

            $Sql = "UPDATE dirty_detail SET Qty = $sum_qty, Weight = '$sum_weight' WHERE id = '$RowID'";
        }

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "add_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "add_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function del_round($conn, $DATA) {
        $id = $DATA["id"];
        $RowID = $DATA["RowID"];
        $dep = $DATA["dep"];
        $return['dep'] = $dep;
        $item = $DATA["item"];
        $return['item'] = $item;

        $Sql = "DELETE FROM dirty_detail_round WHERE id = '$id'";
        mysqli_query($conn,$Sql);

        $Sql2 = "SELECT SUM(Qty) AS sum_qty,SUM(Weight) AS sum_weight FROM dirty_detail_round WHERE RowID = '$RowID'";
        $meQuery = mysqli_query($conn,$Sql2);
        $Result = mysqli_fetch_assoc($meQuery);
        $sum_qty = $Result['sum_qty'];
        $sum_weight = $Result['sum_weight'];
        $return['sum_weight'] = $sum_weight;
        $return['Sql2'] = $Sql2;
        

        if ($sum_qty == null || $sum_qty == 0) {
            $Sql = "DELETE FROM dirty_detail WHERE id = '$RowID'";
        } else {
            $Sql = "UPDATE dirty_detail SET Qty = $sum_qty, Weight = '$sum_weight' WHERE id = '$RowID'";
        }
        $return['Sql'] = $Sql;
        

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "del_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "del_round";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }
    
    function test($conn, $DATA) {
        $round = $DATA["round"];

        $return['round'] = $round['dep']['item'][0][0];
        $return['status'] = "success";
        $return['form'] = "test";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }

    

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_items') {
            load_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_dep') {
            load_dep($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'choose_items') {
            choose_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'add_item') {
            add_item($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'del_back') {
            del_back($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'item_handler') {
            item_handler($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'edit_round') {
            edit_round($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'add_round') {
            add_round($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'del_round') {
            del_round($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'test') {
            test($conn, $DATA);
        }
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
