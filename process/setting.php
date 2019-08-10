<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_site_fac($conn) {
        $boolean1 = false;
        $boolean2 = false;
        $cnt_Hpt = 0;
        $cnt_Fac = 0;
        $Sql = "SELECT HptCode,HptName FROM site WHERE IsStatus = 0";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$cnt_Hpt]['HptCode'] = $Result['HptCode'];
            $return[$cnt_Hpt]['HptName'] = $Result['HptName'];
            $cnt_Hpt++;
            $boolean1 = true;
        }
        $return['cnt_Hpt'] = $cnt_Hpt;

        $Sql = "SELECT FacCode,FacName FROM factory WHERE IsCancel = 0";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$cnt_Fac]['FacCode'] = $Result['FacCode'];
            $return[$cnt_Fac]['FacName'] = $Result['FacName'];
            $cnt_Fac++;
            $boolean2 = true;
        }
        $return['cnt_Fac'] = $cnt_Fac;

        if ($boolean1 && $boolean2) {
            $return['status'] = "success";
            $return['form'] = "load_site_fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_site_fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function load_site($conn) {
        $count = 0;
        $Sql = "SELECT HptCode,HptName,picture FROM site WHERE IsStatus = 0";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['HptCode'] = $Result['HptCode'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['picture'] = $Result['picture'];
            $count++;
        }
        $return['count'] = $count;

        if ($count > 0) {
            $return['status'] = "success";
            $return['form'] = "load_site";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_site";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function load_site_time($conn) {
        
        $FacCode = $_SESSION['FacCode'];;
        $count = 0;
        $Sql = "SELECT site.HptName,SendTime FROM delivery_fac_nhealth,site WHERE FacCode = '$FacCode' AND site.HptCode = delivery_fac_nhealth.HptCode";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['HptCode'] = $Result['HptName'];
            $return[$count]['SendTime'] = $Result['SendTime'];
            $count++;
        }
        $return['count'] = $count;

        if ($count > 0) {
            $return['status'] = "success";
            $return['form'] = "load_site_time";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_site";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function show_fac($conn, $DATA) {
        $HptCode = $DATA["HptCode"];
        $return['HptCode'] = $HptCode;
        $count = 0;

        $Sql = "SELECT factory.FacName,
                        delivery_fac_nhealth.HptCode,
                        delivery_fac_nhealth.SendTime,
                        delivery_fac_nhealth.FacCode  
                FROM delivery_fac_nhealth 
                INNER JOIN factory ON factory.FacCode = delivery_fac_nhealth.FacCode
                WHERE factory.IsCancel = 0
                AND delivery_fac_nhealth.HptCode = '$HptCode'";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['HptCode'] = $Result['HptCode'];
            $return[$count]['FacName'] = $Result['FacName'];
            $return[$count]['FacCode'] = $Result['FacCode'];
            $return[$count]['SendTime'] = $Result['SendTime'];
            $count++;
        }
        $return['count'] = $count;

        if($count > 0){
            $return['status'] = "success";
            $return['form'] = "show_fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "show_fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function AddFacNhealth($conn, $DATA) {
        $FacCode = $DATA["FacCode"];
        $HptCode = $DATA["HptCode"];
        $SendTime = $DATA["SendTime"];
        $Sql = "SELECT COUNT(SendTime) AS cntSendTime FROM delivery_fac_nhealth WHERE FacCode = $FacCode AND HptCode = '$HptCode'";
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $have = $Result["cntSendTime"];

        if ($have == 0) {
            $Sql = "INSERT INTO	delivery_fac_nhealth(HptCode,FacCode,SendTime) VALUES ('$HptCode',$FacCode,$SendTime)";
            mysqli_query($conn, $Sql);

            $return['status'] = "success";
            $return['form'] = "AddFacNhealth";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "AddFacNhealth";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }

    }

    function EditFacNhealth($conn, $DATA) {
        $count = 0;
        $str_FacCode = $DATA["str_FacCode"];
        $str_HptCode = $DATA["str_HptCode"];
        $str_sentTime = $DATA["str_sentTime"];

        $arr_FacCode = explode(",", $str_FacCode);
        $arr_HptCode = explode(",", $str_HptCode);
        $arr_sentTime = explode(",", $str_sentTime);

        $cnt_arr = sizeof($arr_FacCode, 0);

        for ($i = 0; $i < $cnt_arr; $i++) {
            $SendTime = $arr_sentTime[$i];
            if ($arr_sentTime[$i] == null || $arr_sentTime[$i] == "") {
                $SendTime = 0;
            }
            $Sql = "UPDATE delivery_fac_nhealth SET SendTime = $SendTime WHERE HptCode = '$arr_HptCode[$i]' AND FacCode = '$arr_FacCode[$i]'";
            if(mysqli_query($conn, $Sql)){
                $count++;
            }
        }

        if($count == $cnt_arr){
            $return['status'] = "success";
            $return['form'] = "EditFacNhealth";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "EditFacNhealth";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function save_lang($conn, $DATA) {
        $lang = $DATA["lang"];
        $Userid = $DATA["Userid"];
        $Sql = "UPDATE users SET lang = '$lang' WHERE `ID` = $Userid";
        if(mysqli_query($conn, $Sql)){
            $_SESSION['lang']=$lang;
            $return['status'] = "success";
            $return['form'] = "save_lang";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "save_lang";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_site_fac') {
            load_site_fac($conn);
        }
        else if ($DATA['STATUS'] == 'load_site') {
            load_site($conn);
        }
        else if ($DATA['STATUS'] == 'load_site_time') {
            load_site_time($conn);
        }
        else if ($DATA['STATUS'] == 'show_fac') {
            show_fac($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'AddFacNhealth') {
            AddFacNhealth($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'EditFacNhealth') {
            EditFacNhealth($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'save_lang') {
            save_lang($conn, $DATA);
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