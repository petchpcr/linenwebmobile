<?php
function logout($conn, $DATA){
  $logout = $DATA["Confirm"];
  $Sql = "UPDATE users SET IsActive = 0 WHERE ID = ".$_SESSION['Userid'];

  if ($logout == 1  && mysqli_query($conn,$Sql)) {
      unset($_SESSION['Userid']);
      unset($_SESSION['Username']);
      unset($_SESSION['FName']);
      unset($_SESSION['PmID']);
      unset($_SESSION['TimeOut']);
      unset($_SESSION['HptCode']);
      unset($_SESSION['FacCode']);
      session_destroy();
      
      $return['status'] = "success";
      $return['form'] = "logout";
      echo json_encode($return);
      mysqli_close($conn);
      die;
  } else {
      $return['status'] = "failed";
      $return['form'] = "logout";
      echo json_encode($return);
      mysqli_close($conn);
      die;
  }
}
?>
