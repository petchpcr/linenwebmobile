<?php
    session_start();
    require '../connect/connect.php';
    
    $DocNo = $_POST['DocNo'];
    $SigCode = $_POST['SigCode'];

    $Sql = "UPDATE process SET IsStatus = 4,Signature = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);

    $Sql = "UPDATE dirty SET IsStatus = 3 WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);
?>