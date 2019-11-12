<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_doc($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $count = 0;
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    $return['search'] = $DATA["search"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $Sql = "SELECT c.DocNo,f.FacName 
            FROM clean c
            INNER JOIN factory f ON f.FacCode = c.FacCode 
            WHERE c.DocDate = '$search' 
            AND (c.SignFac IS NULL OR c.SignNH IS NULL) 
            AND c.IsStatus = 1";

    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['DocNo'][$count] = $Result['DocNo'];
        $return['FacName'][$count] = $Result['FacName'];
        $count++;
    }
    $return['cnt'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
    } else {
        $return['status'] = "failed";
    }

    $return['form'] = "load_doc";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function add_kpi($conn, $DATA)
{
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    $return['search'] = $DATA["search"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $Sql = "    SELECT          CONCAT('KD1',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE('$search')),3,4),LPAD(MONTH(DATE('$search')),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE('$search') AS DocDate,
                                CURRENT_TIME() AS RecNow

                FROM            kpi_dirty1

                WHERE           DocNo Like CONCAT('KD1',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE('$search')),3,4),LPAD(MONTH(DATE('$search')),2,0),'%')

                ORDER BY        DocNo DESC LIMIT 1";


    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $DocNo = $Result['DocNo'];
    $return['DocNo'] = $DocNo;

    $Sql = "INSERT INTO kpi_dirty1(DocNo,DocDate,HptCode) VALUES('$DocNo',DATE('$search'),'$siteCode')";

    if (mysqli_query($conn, $Sql)) {
        $return['status'] = "success";
        $return['form'] = "add_kpi";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "add_kpi";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_kpi') {
        add_kpi($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
