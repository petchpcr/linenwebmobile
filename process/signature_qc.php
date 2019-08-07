<?php
    session_start();
    require '../connect/connect.php';
    
    $DocNo = $_POST['DocNo'];
    $SigCode = $_POST['SigCode'];

    $Sql = "UPDATE clean SET IsStatus = 3,Signature = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);

?>