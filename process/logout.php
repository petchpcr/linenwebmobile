<?php
function logout($conn, $DATA){
  $logout = $DATA["Confirm"];
  $Sql = "UPDATE users SET IsActive = 0 WHERE ID = ".$_SESSION['Userid'];
  if ($logout == 1 || $logout == 2) {
    mysqli_query($conn,$Sql);
  }else if($logout == 3){
    $Sql2 = "SELECT IsActive FROM users WHERE ID = ".$_SESSION['Userid'];
    $meQuery = mysqli_query($conn,$Sql2);
    $Result = mysqli_fetch_assoc($meQuery);
    $IsActive = $Result['IsActive'];
    if($IsActive==0){
      $Sql2 = "UPDATE users SET IsActive = 1 WHERE ID = ".$_SESSION['Userid'];
      mysqli_query($conn,$Sql2);
    }else{
      $logout=1;
    }
  }

  if ($logout == 1) {
    unset($_SESSION['Userid']);
    unset($_SESSION['Username']);
    unset($_SESSION['FName']);
    unset($_SESSION['PmID']);
    unset($_SESSION['Permission']);
    unset($_SESSION['TimeOut']);
    unset($_SESSION['HptCode']);
    unset($_SESSION['FacCode']);
    session_destroy();
    $return['status'] = "success";
    $return['form'] = "logout";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }

}
?>
