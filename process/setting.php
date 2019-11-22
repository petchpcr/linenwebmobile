<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_site_fac($conn)
{
    $HptCode = $_SESSION['HptCode'];
    $cnt_Fac = 0;
    if ($_SESSION['lang'] == "th") {
        $FacName = "FacNameTH";
    } else if ($_SESSION['lang'] == "en") {
        $FacName = "FacName";
    }

    $Sql = "SELECT FacCode,$FacName AS Fname 
            FROM factory 
            WHERE IsCancel = 0 
            AND HptCode = '$HptCode'";
    $meQuery = mysqli_query($conn, $Sql);
    $return['Faode'] = $Sql;
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$cnt_Fac]['FacCode'] = $Result['FacCode'];
        $return[$cnt_Fac]['FacName'] = $Result['Fname'];
        $cnt_Fac++;
    }
    $return['cnt_Fac'] = $cnt_Fac;
    // $return['Sql'] = $Sql;

    $return['status'] = "success";
    $return['form'] = "load_site_fac";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function load_site($conn) {
    $FacCode = $_SESSION['FacCode'];
    $count = 0;
    if ($_SESSION['lang'] == "th") {
        $Hname = "HptNameTH";
    } else if ($_SESSION['lang'] == "en") {
        $Hname = "HptName";
    }

    $Sql = "SELECT DISTINCT site.HptCode,site.$Hname AS Hname 
            FROM delivery_fac_nhealth,site 
            WHERE delivery_fac_nhealth.FacCode = '$FacCode' 
            AND site.HptCode = delivery_fac_nhealth.HptCode";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['HptCode'][$count] = $Result['HptCode'];
        $return['HptName'][$count] = $Result['Hname'];
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

function load_site_time($conn)
{
    $FacCode = $_SESSION['FacCode'];
    $count = 0;
    $Sql = "SELECT site.HptName,SendTime 
            FROM delivery_fac_nhealth,site 
            WHERE FacCode = '$FacCode' 
            AND site.HptCode = delivery_fac_nhealth.HptCode";
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

function show_fac($conn, $DATA)
{
    $HptCode = $DATA["HptCode"];
    $Fcode = array();
    $Fname = array();

    $count = 0;
    if ($_SESSION['lang'] == "th") {
        $FacName = "FacNameTH";
    } else if ($_SESSION['lang'] == "en") {
        $FacName = "FacName";
    }

    $Sql = "SELECT factory.$FacName AS Fname,
                        delivery_fac_nhealth.FacCode  
                FROM delivery_fac_nhealth 
                INNER JOIN factory ON factory.FacCode = delivery_fac_nhealth.FacCode
                WHERE factory.IsCancel = 0
                AND delivery_fac_nhealth.HptCode = '$HptCode'
                ORDER BY delivery_fac_nhealth.FacCode  ASC";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $n = $Result['Fname'];
        $c = $Result['FacCode'];
        if (!(in_array($c, $Fcode))) {
            array_push($Fname, $n);
            array_push($Fcode, $c);
            $count++;
        }
    }

    $return['count'] = $count;
    $return['Fname'] = $Fname;
    $return['Fcode'] = $Fcode;
    if ($count > 0) {
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

function show_fac_time($conn, $DATA)
{
    $HptCode = $_SESSION["HptCode"];
    $FacCode = $DATA["FacCode"];
    $return['FacCode'] = $FacCode;

    $count = 0;
    if ($_SESSION['lang'] == "th") {
        $FacName = "FacNameTH";
    } else if ($_SESSION['lang'] == "en") {
        $FacName = "FacName";
    }

    $Sql = "SELECT factory.$FacName AS Fname,
                    delivery_fac_nhealth.SendTime 
                FROM delivery_fac_nhealth 
                INNER JOIN factory ON factory.FacCode = delivery_fac_nhealth.FacCode
                WHERE factory.IsCancel = 0
                AND delivery_fac_nhealth.HptCode = '$HptCode'
                AND delivery_fac_nhealth.FacCode = '$FacCode'
                ORDER BY delivery_fac_nhealth.SendTime ASC";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['FacName'][$count] = $Result['Fname'];
        $return['SendTime'][$count] = $Result['SendTime'];
        $count++;
    }
    $return['count'] = $count;
    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "show_fac_time";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "show_fac_time";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function show_hpt_time($conn, $DATA)
{
    $FacCode = $_SESSION["FacCode"];
    $HptCode = $DATA["HptCode"];
    $return['HptCode'] = $HptCode;

    $count = 0;
    if ($_SESSION['lang'] == "th") {
        $Hname = "HptNameTH";
    } else if ($_SESSION['lang'] == "en") {
        $Hname = "HptName";
    }

    $Sql = "SELECT site.$Hname AS Hname,
                    delivery_fac_nhealth.SendTime 
                FROM delivery_fac_nhealth 
                INNER JOIN site ON site.HptCode = delivery_fac_nhealth.HptCode
                WHERE site.IsStatus = 0
                AND delivery_fac_nhealth.HptCode = '$HptCode'
                AND delivery_fac_nhealth.FacCode = '$FacCode'
                ORDER BY delivery_fac_nhealth.SendTime ASC";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['HptName'][$count] = $Result['Hname'];
        $return['SendTime'][$count] = $Result['SendTime'];
        $count++;
    }
    $return['count'] = $count;
    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "show_hpt_time";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "show_hpt_time";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function save_edit_factime($conn, $DATA)
{
    $HptCode = $_SESSION["HptCode"];
    $Del_fac = $DATA["Del_fac"];
    $Del_time = $DATA["Del_time"];
    $FacCode = $DATA["ar_fac"];
    $Time = $DATA["ar_time"];
    $New_Time = $DATA["ar_newtime"];
    $cntDel = sizeof($Del_fac);
    $cntEdit = sizeof($FacCode);
    $cnt_del = 0;
    $cnt_edit = 0;

    for ($i = 0; $i < $cntDel; $i++) {
        $Sql = "DELETE FROM delivery_fac_nhealth 
                WHERE HptCode = '$HptCode' 
                AND FacCode = '$Del_fac[$i]'
                AND SendTime = '$Del_time[$i]'";
        if (mysqli_query($conn, $Sql)) {
            $cnt_del++;
        }
    }

    for ($i = 0; $i < $cntEdit; $i++) {
        $Sql = "UPDATE delivery_fac_nhealth 
                SET SendTime = '$New_Time[$i]' 
                WHERE HptCode = '$HptCode' 
                AND FacCode = '$FacCode[$i]'
                AND SendTime = '$Time[$i]'";

        if (mysqli_query($conn, $Sql)) {
            $cnt_edit++;
        }
    }

    $return['Delete'] = $cnt_del . " of " . $cntDel;
    $return['Edit'] = $cnt_edit . " of " . $cntEdit;
    $return['status'] = "success";
    $return['form'] = "save_edit_factime";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function AddFacNhealth($conn, $DATA)
{
    $FacCode = $DATA["FacCode"];
    $HptCode = $_SESSION["HptCode"];
    $SendTime = $DATA["SendTime"];
    $Sql = "SELECT COUNT(SendTime) AS cntSendTime FROM delivery_fac_nhealth WHERE FacCode = '$FacCode' AND HptCode = '$HptCode' AND SendTime = '$SendTime'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $have = $Result["cntSendTime"];

    if ($have == 0) {
        $Sql = "INSERT INTO	delivery_fac_nhealth(HptCode,FacCode,SendTime) VALUES ('$HptCode','$FacCode','$SendTime')";
        mysqli_query($conn, $Sql);

        $return['Sql'] = $Sql;
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

function EditFacNhealth($conn, $DATA)
{
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
        if (mysqli_query($conn, $Sql)) {
            $count++;
        }
    }

    if ($count == $cnt_arr) {
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

function save_lang($conn, $DATA)
{
    $lang = $DATA["lang"];
    $Userid = $DATA["Userid"];
    $Sql = "UPDATE users SET lang = '$lang' WHERE `ID` = $Userid";
    if (mysqli_query($conn, $Sql)) {
        $_SESSION['lang'] = $lang;
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

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_site_fac') {
        load_site_fac($conn);
    } else if ($DATA['STATUS'] == 'load_site') {
        load_site($conn);
    } else if ($DATA['STATUS'] == 'load_site_time') {
        load_site_time($conn);
    } else if ($DATA['STATUS'] == 'show_fac') {
        show_fac($conn, $DATA);
    } else if ($DATA['STATUS'] == 'show_fac_time') {
        show_fac_time($conn, $DATA);
    } else if ($DATA['STATUS'] == 'show_hpt_time') {
        show_hpt_time($conn, $DATA);
    } else if ($DATA['STATUS'] == 'save_edit_factime') {
        save_edit_factime($conn, $DATA);
    } else if ($DATA['STATUS'] == 'AddFacNhealth') {
        AddFacNhealth($conn, $DATA);
    } else if ($DATA['STATUS'] == 'EditFacNhealth') {
        EditFacNhealth($conn, $DATA);
    } else if ($DATA['STATUS'] == 'save_lang') {
        save_lang($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
