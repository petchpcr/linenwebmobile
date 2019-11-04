<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

$DocNo = $_POST['DocNo'];
$SigCode = $_POST['SigCode'];
$fnc = $_POST['fnc'];
$return['fnc'] = $fnc;

if ($fnc == 'sign_fac') {
    $Sql = "UPDATE dirty SET SignFac = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    
    echo json_encode($return);
    mysqli_close($conn);
    die;

}
else if ($fnc == 'sign_nh') {
    $Sql = "UPDATE dirty SET SignNH = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn, $Sql);
    
    echo json_encode($return);
    mysqli_close($conn);
    die;
}