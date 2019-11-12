<?php
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
    $Sql = "UPDATE claim SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
else if ($IsMenu == 'signdoc_rewash_detail') {
    $Sql = "UPDATE rewash SET $sign_funciton = '$SignCode',$sign_funciton"."Time = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
}
$return['Sql'] = $Sql;

echo json_encode($return);
mysqli_close($conn);
die;