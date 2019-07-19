<?php
    session_start();
    require '../connect/connect.php';

    function choose_items($conn, $DATA){
        $DepCode = $DATA["DepCode"];
        $Search = $DATA["Search"];
        $count = 0;
        $boolean = false;
        $Sql = "SELECT department.DepName,site.HptName
                FROM department 
                INNER JOIN site ON department.HptCode = site.HptCode 
                WHERE department.DepCode = '$DepCode'";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return['DepName']	=  $Result['DepName'];
            $return['HptName']	=  $Result['HptName'];
        }

        $Sql = "SELECT DISTINCT     item_stock.ItemCode,ItemName,item.UnitCode

                FROM                item_stock,item

                WHERE               DepCode='$DepCode'
                AND                 item_stock.ItemCode=item.ItemCode
                AND                 item.ItemName LIKE '%$Search%' ";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['ItemCode']	=  $Result['ItemCode'];
            $return[$count]['ItemName']	=  $Result['ItemName'];
            $return[$count]['UnitCode']	=  $Result['UnitCode'];
            $count++;
            $boolean = true;
        }

        if ($boolean) {
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
        $boolean = false;

        $Sql = "SELECT dirty_detail.ItemCode,
                        item.ItemName,
                        dirty_detail.UnitCode,
                        dirty_detail.Qty,
                        dirty_detail.Weight 
                FROM dirty_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND	  item.ItemCode = dirty_detail.ItemCode";
        $return['sql'] = $Sql;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ItemCode'] = $Result['ItemCode'];
            $return[$count]['ItemName'] = $Result['ItemName'];
            $return[$count]['UnitCode'] = $Result['UnitCode'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $count++;
            $boolean = true;
        }
        $return['boolean'] = $boolean;
        if ($boolean) {
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

    function logout($conn, $DATA){

        $logout = $DATA["Confirm"];
        
        if ($logout == 1) {
            unset($_SESSION['Userid']);
            unset($_SESSION['Username']);
            unset($_SESSION['FName']);
            unset($_SESSION['PmID']);
            unset($_SESSION['TimeOut']);
            unset($_SESSION['HptCode']);
            unset($_SESSION['FacCode']);
            session_destroy();

            $return['status'] = "success";
            $return['form'] = "logout";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "logout";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_items') {
            load_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'choose_items') {
            choose_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_doc') {
            load_doc($conn, $DATA);
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
?>