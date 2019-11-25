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
    $Sql = "SELECT c.DocNo,f.DepName 
            FROM damage c
            INNER JOIN department f ON f.DepCode = c.DepCode 
            WHERE c.DocDate LIKE '%$search%' 
            AND f.HptCode = '$siteCode'
            AND (c.SignFac IS NULL OR c.SignNH IS NULL) 
            AND c.IsStatus = 1";

    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['DocNo'][$count] = $Result['DocNo'];
        $return['DepName'][$count] = $Result['DepName'];
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

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
