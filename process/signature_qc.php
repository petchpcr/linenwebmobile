<?php
    session_start();
    require '../connect/connect.php';
    date_default_timezone_set("Asia/Bangkok");
    
    $DocNo = $_POST['DocNo'];
    $SigCode = $_POST['SigCode'];

    $Sql = "UPDATE clean SET Signature = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);
    //update_status($DocNo);
    $Sql = "UPDATE clean SET IsStatus = 4 WHERE DocNo= '$cleanRefDocNo'";
    mysqli_query($conn,$Sql);

    function update_status($thisDocNo){
        $Sql = "SELECT RefDocNo FROM clean WHERE DocNo = '$thisDocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $rewashRefDocNo = $Result['RefDocNo'];

        $c = 0;
        $Sql = "SELECT RefDocNo FROM rewash WHERE DocNo = '$rewashRefDocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $cleanRefDocNo = $Result['RefDocNo'];
            $c++;
        }
        
        if($c > 0){
            $s=true;
            $Sql = "SELECT IF(IsStatus=3, TRUE, FALSE) as s FROM rewash WHERE RefDocNo = '$cleanRefDocNo'
                    UNION ALL
                    SELECT IF(IsStatus=3, TRUE, FALSE) as s FROM claim WHERE RefDocNo = '$cleanRefDocNo'";
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                if($Result['s']==0){
                    $s=false;
                }
            }

            if($s){
                $Sql = "UPDATE clean SET IsStatus = 4 WHERE DocNo= '$cleanRefDocNo'";
                mysqli_query($conn,$Sql);
            }
            update_status($cleanRefDocNo);
        }

    }

?>