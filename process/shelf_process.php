<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

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
                    SEC_TO_TIME(shelfcount.ScEndTime-shelfcount.ScStartTime) AS ScUseTime,
                    shelfcount.PkStartTime,
                    shelfcount.PkEndTime,
                    SEC_TO_TIME(shelfcount.PkEndTime-shelfcount.PkStartTime) AS PkUseTime,
                    shelfcount.DvStartTime,
                    shelfcount.DvEndTime,
                    SEC_TO_TIME(shelfcount.DvEndTime-shelfcount.DvStartTime) AS DvUseTime,
                    shelfcount.DvOverTime,
                    shelfcount.IsStatus,
                    shelfcount.signature,
                    (SELECT SendTime 
                     FROM delivery_fac_nhealth
                     WHERE HptCode = '$siteCode'
                     AND FacCode = '$FacCode') AS LimitTime,
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
            $return['DvOverTime'] = $Result['DvOverTime'];
            $return['IsStatus'] = $Result['IsStatus'];
            $return['Signature'] = $Result['signature'];
            $return['LimitTime'] = $Result['LimitTime'];
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
        $From = $DATA["From"];
        $Sql = "UPDATE process SET PackStartTime = NOW() WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $Sql = "UPDATE $From SET IsProcess = 3 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

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
        $From = $DATA["From"];
        $boolean = false;

        $Sql = "UPDATE process SET PackEndTime = NOW(),IsStatus = 3 WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(PackEndTime,PackStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET PackUseTime = '$UseTime' WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE $From SET IsProcess = 4 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

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

    function use_time_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $PackUseTime = $DATA["PackUseTime"];

        $Sql = "UPDATE process SET PackUseTime = '$PackUseTime' WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

            $return['PackUseTime'] = $PackUseTime;
            $return['status'] = "success";
            $return['form'] = "use_time_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "use_time_pack";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_send($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET SendStartTime = '$nowdate' WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn,$Sql)){
            $Sql = "UPDATE $From SET IsProcess = 5 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['SendStartTime'] = $nowdate;
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
        $SiteCode = $DATA["siteCode"];
        $FacCode = $_SESSION["FacCode"];
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $boolean = false;

        $Sql = "UPDATE process SET SendEndTime = NOW(),IsStatus = 4 WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(process.SendEndTime,process.SendStartTime) AS UseTime,
                        TIMEDIFF((delivery_fac_nhealth.SendTime)*100,TIMEDIFF(process.SendEndTime,process.SendStartTime)) AS Overtime 

                FROM    process,delivery_fac_nhealth 
                WHERE   process.DocNo = '$DocNo'
                AND     delivery_fac_nhealth.HptCode = '$SiteCode'
                AND     delivery_fac_nhealth.FacCode = '$FacCode'";

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $Overtime = $Result['Overtime'];
            $boolean = true;
        }

        if ($boolean) {
            $Sql = "UPDATE process SET SendUseTime = '$UseTime',SendOverTime = '$Overtime' WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE $From SET IsProcess = 6 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

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