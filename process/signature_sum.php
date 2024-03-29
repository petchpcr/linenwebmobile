<?php

use Mpdf\Tag\P;

session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

$IsMenu = $_POST['IsMenu'];
$DocNo = $_POST['DocNo'];
$SignCode = $_POST['SignCode'];
$sign_funciton = $_POST['sign_funciton'];

$return['IsMenu'] = $IsMenu;
$return['sign_funciton'] = $sign_funciton;

if ($IsMenu == 'signdoc_dirty_detail') {
    $Sql = "UPDATE dirty SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);

    $Sql = "SELECT COUNT(*) AS cnt FROM dirty WHERE DocNo = '$DocNo' AND (SignFac IS NULL OR SignNH IS NULL)";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $cnt = $Result['cnt'];
    
    if ($cnt == 0) {
        $Sql = "UPDATE dirty SET IsStatus = 1 WHERE DocNo = '$DocNo'";
        mysqli_query($conn, $Sql);
    }
}
else if ($IsMenu == 'signdoc_newlinen_detail') {
    $Sql = "UPDATE newlinentable SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_clean_detail') {
    $Sql = "UPDATE clean SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_claim_detail') {
    $Sql = "UPDATE damage SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_rewash_detail') {
    $Sql = "UPDATE repair_wash SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_return_wash_detail') {
    $Sql = "UPDATE return_wash SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_return_doc_detail') {
    $Sql = "UPDATE return_doc SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
$return['Sql'] = $Sql;

echo json_encode($return);
mysqli_close($conn);
die;