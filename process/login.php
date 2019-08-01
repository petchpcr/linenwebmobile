<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

function checklogin($conn,$DATA)
{
  if (isset($DATA)) {
    $user = $DATA['USERNAME'];
    $password = $DATA['PASSWORD'];
    $boolean = false;
    $Sql = "SELECT    UserName,FName,ID,PmID,IFNULL(lang,'th') AS lang,HptCode,FacCode,TimeOut
            FROM      users
            WHERE     UserName = '$user'
            AND       Password = '$password' 
            AND       IsCancel = 0
            AND       IsActive = 0
            AND       (PmID=2 OR PmID=3 OR PmID=4)";

    //$return['sql'] = $Sql;
    $meQuery = mysqli_query($conn,$Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
      $_SESSION['Userid'] = $Result['ID'];
      $Userid = $Result['ID'];
      $_SESSION['Username'] = $Result['UserName'];
      $_SESSION['FName'] = $Result['FName'];
      $_SESSION['PmID'] = $Result['PmID'];
      $_SESSION['lang'] = $Result['lang'];
      $_SESSION['TimeOut'] = $Result['TimeOut'];
      $_SESSION['HptCode'] = $Result['HptCode'];
      $_SESSION['FacCode'] = $Result['FacCode'];
      $return['FacCode'] = $Result['FacCode'];
      $return['PmID'] = $Result['PmID'];

      $boolean = true;
    }
    $Sql = "UPDATE users SET IsActive = 1 WHERE ID = $Userid";
    if($boolean && mysqli_query($conn,$Sql)){
      $return['status'] = "success";
      $return['msg'] = 'Login success' ;
      echo json_encode($return);
      mysqli_close($conn);
      die;
    }else{
      $return['status'] = "failed";
      $return['msg'] = "Not found username or password";
      echo json_encode($return);
      mysqli_close($conn);
      die;
    }
  }
}

if(isset($_POST['DATA']))
{
  $data = $_POST['DATA'];
  $DATA = json_decode(str_replace ('\"','"', $data), true);
  checklogin($conn,$DATA);
}else{
	$return['status'] = "error";
	$return['msg'] = 'ไม่มีข้อมูลนำเข้า';
	echo json_encode($return);
	mysqli_close($conn);
  die;
}
?>
