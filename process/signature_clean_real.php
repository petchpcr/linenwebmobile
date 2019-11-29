<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

$DocNo = $_POST['DocNo'];
$SignCode = $_POST['SignCode'];
$sign_funciton = $_POST['sign_funciton'];
$return['sign_funciton'] = $sign_funciton;

if ($sign_funciton == 'sign_fac') {
    $Sql = "UPDATE clean SET SignFac = '$SignCode',SignFacTime = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    
    echo json_encode($return);
    mysqli_close($conn);
    die;

}
else if ($sign_funciton == 'sign_nh') {
    $Sql = "UPDATE clean SET SignNH = '$SignCode',SignNHTime = NOW() WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    
    echo json_encode($return);
    mysqli_close($conn);
    die;
}