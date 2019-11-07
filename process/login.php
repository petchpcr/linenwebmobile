<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set("Asia/Bangkok");

function checklogin($conn, $DATA)
{
  // if (isset($DATA)) {
  $user = $DATA['USERNAME'];
  $password = $DATA['PASSWORD'];
  // $password = md5($DATA['PASSWORD']);
  $boolean = false;
  $Sql = "SELECT  users.UserName,
                  users.ThName,
                  users.EngName,
                  users.ID,
                  users.PmID,
                  users.HptCode,
                  users.FacCode,
                  users.TimeOut,
                  IFNULL(users.lang,'th') AS lang,
                  permission.Permission

            FROM    users INNER JOIN permission ON users.PmID = permission.PmID
            WHERE   users.UserName = '$user'
            AND     users.Password = '$password' 
            AND     users.IsCancel = 0
            AND     users.IsActive = 0";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $_SESSION['Userid'] = $Result['ID'];
    $Userid = $Result['ID'];
    $_SESSION['Username'] = $Result['UserName'];
    $_SESSION['PmID'] = $Result['PmID'];
    $_SESSION['Permission'] = $Result['Permission'];
    $_SESSION['lang'] = $Result['lang'];
    $_SESSION['TimeOut'] = $Result['TimeOut'];
    $_SESSION['HptCode'] = $Result['HptCode'];
    $_SESSION['FacCode'] = $Result['FacCode'];
    $return['FacCode'] = $Result['FacCode'];
    $return['PmID'] = $Result['PmID'];
    if ($Result['lang'] == 'en') {
      $_SESSION['FName'] = $Result['EngName'];
    } else {
      $_SESSION['FName'] = $Result['ThName'];
    }

    $boolean = true;
  }
  $Sql = "UPDATE users SET IsActive = 1 WHERE ID = $Userid";
  if ($boolean && mysqli_query($conn, $Sql)) {
    $return['status'] = "success";
    $return['form'] = "checklogin";
    $return['msg'] = 'Login success';
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "checklogin";
    $return['msg'] = "Not found username or password";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
  // }
}

function clear_active($conn, $DATA)
{
  $user = $DATA['USERNAME'];
  $password = $DATA['PASSWORD'];
  // $password = md5($DATA['PASSWORD']);

  $Sql = "SELECT COUNT(*) AS cnt
          FROM    users INNER JOIN permission ON users.PmID = permission.PmID
          WHERE   users.UserName = '$user'
          AND     users.Password = '$password' 
          AND     users.IsCancel = 0

          AND       (users.PmID=2 OR users.PmID=3 OR users.PmID=4)";
  $meQuery = mysqli_query($conn, $Sql);
  $Result = mysqli_fetch_assoc($meQuery);
  $count = $Result['cnt'];

  if ($count == 1) {
    $Sql = "UPDATE users SET IsActive = 0 WHERE UserName = '$user' AND Password = '$password'";
    mysqli_query($conn, $Sql);
    $return['status'] = "success";
    $return['form'] = "clear_active";
    echo json_encode($return);
    mysqli_close($conn);
    die;

  } else {
    $return['status'] = "failed";
    $return['msg'] = "Not found username or password";
    $return['form'] = "clear_active";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
}

if (isset($_POST['DATA'])) {
  $data = $_POST['DATA'];
  $DATA = json_decode(str_replace('\"', '"', $data), true);

  if ($DATA['STATUS'] == 'checklogin') {
    checklogin($conn, $DATA);
  } else if ($DATA['STATUS'] == 'clear_active') {
    clear_active($conn, $DATA);
  }
} else {
  $return['status'] = "error";
  $return['msg'] = 'ไม่มีข้อมูลนำเข้า';
  echo json_encode($return);
  mysqli_close($conn);
  die;
}
