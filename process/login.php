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
    $Sql = "SELECT
        users.ID,
        users.UserName,
        users.FName,
        users.`Password`,
        permission.PmID,
        permission.Permission,
        users.TimeOut,
        users.HptCode,
        users.FacCode
        FROM permission
        INNER JOIN users ON users.PmID = permission.PmID
        WHERE users.UserName = '$user' AND users.`Password` = '$password' AND users.IsCancel = 0";
        $return['sql'] = $Sql;
    $meQuery = mysqli_query($conn,$Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
      $_SESSION['Userid'] = $Result['ID'];
      $_SESSION['Username'] = $Result['UserName'];
      $_SESSION['FName'] = $Result['FName'];
      $_SESSION['PmID'] = $Result['PmID'];
      $_SESSION['TimeOut'] = $Result['TimeOut'];
      $_SESSION['HptCode'] = $Result['HptCode'];
      $_SESSION['FacCode'] = $Result['FacCode'];

      $boolean = true;
    }

    if($boolean){
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
