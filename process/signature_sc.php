<?php
    session_start();
    require '../connect/connect.php';
    
    $DocNo = $_POST['DocNo'];
    $SigCode = $_POST['SigCode'];

    $Sql = "UPDATE shelfcount SET Signature = '$SigCode',IsStatus = 3  WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);

?>