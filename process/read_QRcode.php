<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_QRcode($conn, $DATA){
        $itemCode = $DATA["itemCode"];
        $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$itemCode'";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['ItemName'] = $Result['ItemName'];
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_QRcode";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_QRcode";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);
    
        if ($DATA['STATUS'] == 'load_QRcode') {
            load_QRcode($conn, $DATA);
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