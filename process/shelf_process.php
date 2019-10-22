<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function load_process($conn, $DATA){
        $count = 0;
        $siteCode = $DATA["siteCode"];
        $FacCode = $_SESSION["FacCode"];
        $DocNo = $DATA["DocNo"];
        $boolean = false;
        $Sql = "SELECT
                    shelfcount.DocNo,
                    shelfcount.ScStartTime,
                    shelfcount.ScEndTime,
                    TIMEDIFF(shelfcount.ScEndTime,shelfcount.ScStartTime) AS ScUseTime,
                    shelfcount.PkStartTime,
                    shelfcount.PkEndTime,
                    TIMEDIFF(shelfcount.PkEndTime,shelfcount.PkStartTime) AS PkUseTime,
                    shelfcount.DvStartTime,
                    shelfcount.DvEndTime,
                    TIMEDIFF(shelfcount.DvEndTime,shelfcount.DvStartTime) AS DvUseTime,
                    shelfcount.IsStatus,
                    shelfcount.signature,
                    department.HptCode
                FROM
                    shelfcount
                INNER JOIN department ON department.DepCode = shelfcount.DepCode
                WHERE shelfcount.DocNo = '$DocNo'";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['DocNo'] = $Result['DocNo'];
            $return['ScStartTime'] = $Result['ScStartTime'];
            $return['ScEndTime'] = $Result['ScEndTime'];
            $return['ScUseTime'] = $Result['ScUseTime'];
            $return['PkStartTime'] = $Result['PkStartTime'];
            $return['PkEndTime'] = $Result['PkEndTime'];
            $return['PkUseTime'] = $Result['PkUseTime'];
            $return['DvStartTime'] = $Result['DvStartTime'];
            $return['DvEndTime'] = $Result['DvEndTime'];
            $return['DvUseTime'] = $Result['DvUseTime'];
            $return['IsStatus'] = $Result['IsStatus'];
            $return['Signature'] = $Result['signature'];
            $return['HptCode'] = $Result['HptCode'];

            $count++;
            $boolean = true;
        }
        
        if($boolean){            
            $return['status'] = "success";
            $return['form'] = "load_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function insert_process($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "INSERT INTO process (DocNo) VALUES ('$DocNo') ";
    
        if($meQuery = mysqli_query($conn,$Sql)){

            $return['status'] = "success";
            $return['form'] = "insert_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "insert_process";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $Sql = "UPDATE process SET WashStartTime = NOW(),IsStatus = 1 WHERE DocNo = '$DocNo'";
        
        if(mysqli_query($conn,$Sql)){
            $Sql = "UPDATE $From SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
        else {
            $return['status'] = "failed";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $boolean = false;

        $Sql = "UPDATE process SET WashEndTime = NOW() WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(WashEndTime,WashStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET WashUseTime = '$UseTime',IsStatus = 2 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE $From SET IsProcess = 2 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET PkStartTime = NOW(),IsStatus = 2 WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "start_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "start_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET PkEndTime = NOW(),IsStatus = 3 WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "end_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "end_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_send($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET DvStartTime = NOW() WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $return['status'] = "success";
            $return['form'] = "start_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "start_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function end_send($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE shelfcount SET DvEndTime = NOW() WHERE DocNo = '$DocNo'";

        if (mysqli_query($conn,$Sql)) {
            $return['status'] = "success";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_process') {
            load_process($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'insert_process') {
            insert_process($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_wash') {
            start_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'stop_wash') {
            stop_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'do_end_wash') {
            do_end_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_wash') {
            end_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_pack') {
            start_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_pack') {
            end_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_send') {
            start_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_send') {
            end_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        }
        
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
?>