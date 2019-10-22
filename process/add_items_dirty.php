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
                AND     ItemName LIKE '%$Search%'";
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

        $Sql = "SELECT dirty_detail.ItemCode,
                        item.ItemName,
                        dirty_detail.UnitCode,
                        dirty_detail.Qty,
                        dirty_detail.Weight,
                        dirty_detail.DepCode  
                FROM dirty_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND	  item.ItemCode = dirty_detail.ItemCode
                ORDER BY ItemName ASC";
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ItemCode'] = $Result['ItemCode'];
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
        $count = 0;

        $Sql = "SELECT DepCode,DepName FROM department WHERE HptCode = '$siteCode'";

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
        $DocNo = $DATA['DocNo'];
        $mul_qty = $DATA['mul_qty'];
        $mul_weight = $DATA['mul_weight'];
        $Userid = $_SESSION['Userid'];
        $Sql = "DELETE FROM dirty_detail WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);
        $count = 0;
        
        foreach($mul_qty as $ikey => $item){
            foreach($item as $dkey => $qty){
                if ($qty > 0) {
                    $weight = $mul_weight[$ikey][$dkey];
                    $Sql = "INSERT INTO dirty_detail(`DocNo`,`ItemCode`,`DepCode`,`UnitCode`,`Weight`,`Qty`) 
                            VALUES ('$DocNo','$ikey','$dkey',1,$weight,$qty) ";
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
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
