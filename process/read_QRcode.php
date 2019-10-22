<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function load_QRcode($conn, $DATA){
        $itemCode = $DATA["itemCode"];
        $Sql = "SELECT item.ItemName,
                        item_category.CategoryName AS CateSub,
                        item_main_category.MainCategoryName AS CateMain,
                        item_size.SizeName  
                        
        FROM item 
        INNER JOIN item_category ON item_category.CategoryCode = item.CategoryCode 
        INNER JOIN item_main_category ON item_main_category.MainCategoryCode = item_category.MainCategoryCode 
        INNER JOIN item_size ON item_size.SizeCode = item.SizeCode  
        WHERE ItemCode = '$itemCode'";
        $boolean = false;
        // $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['ItemName'] = $Result['ItemName'];
            $return['CateSub'] = $Result['CateSub'];
            $return['CateMain'] = $Result['CateMain'];
            $return['SizeName'] = $Result['SizeName'];
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_QRcode";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed_QRcode";
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
