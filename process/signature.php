<?php
    session_start();
    require '../connect/connect.php';
    
    $DocNo = $_POST['DocNo'];
    $SigCode = $_POST['SigCode'];

    $Sql = "UPDATE process SET Signature = '$SigCode' WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn,$Sql);
    mysqli_close($conn);
?>