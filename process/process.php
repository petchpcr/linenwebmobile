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
        $From = $DATA["From"];
        $Limit_null = 30;
        $boolean = false;
        $Sql = "SELECT
                    process.DocNo,
                    process.WashStartTime,
                    process.WashStopTime,
                    process.WashEndTime,
                    process.WashBalance AS Diff_Sec,
                    SEC_TO_TIME(process.WashBalance) AS Diff_Time,
                    process.WashUseTime,
                    process.PackStartTime,
                    process.PackEndTime,
                    process.PackUseTime,
                    process.SendStartTime,
                    process.SendEndTime,
                    DATE_FORMAT(process.SendLimitTime,'%H:%i:%s') AS SendLimitTime,
                    process.SendUseTime,
                    process.SendOverTime,
                    process.IsStatus,
                    process.IsStop,
                    process.Signature,
                     $From.HptCode  
                FROM
                    process
                INNER JOIN $From ON $From.DocNo = process.DocNo
                WHERE process.DocNo = '$DocNo'";

        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['DocNo'] = $Result['DocNo'];
            $return['WashStartTime'] = $Result['WashStartTime'];
            $return['WashStopTime'] = $Result['WashStopTime'];
            $return['WashEndTime'] = $Result['WashEndTime'];
            $return['Diff_Sec'] = $Result['Diff_Sec'];
            $return['Diff_Time'] = $Result['Diff_Time'];
            $return['WashUseTime'] = $Result['WashUseTime'];
            $return['PackStartTime'] = $Result['PackStartTime'];
            $return['PackEndTime'] = $Result['PackEndTime'];
            $return['PackUseTime'] = $Result['PackUseTime'];
            $return['SendStartTime'] = $Result['SendStartTime'];
            $return['SendEndTime'] = $Result['SendEndTime'];
            $return['SendLimitTime'] = $Result['SendLimitTime'];
            $return['SendUseTime'] = $Result['SendUseTime'];
            $return['SendOverTime'] = $Result['SendOverTime'];
            $return['IsStatus'] = $Result['IsStatus'];
            $return['IsStop'] = $Result['IsStop'];
            $return['Signature'] = $Result['Signature'];
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

    function load_fac_time($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $FacCode = $_SESSION['FacCode'];
        $count = 0;

        $Sql = "SELECT SendTime FROM delivery_fac_nhealth WHERE HptCode = '$siteCode' AND FacCode = '$FacCode' ORDER BY SendTime ASC";
        $meQuery = mysqli_query($conn, $Sql);
        $return['Sql'] = $Sql;
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['SendTime'][$count] = $Result['SendTime'];
            $count++;
        }
        $return['cnt'] = $count;

        if($count > 0){
            $return['status'] = "success";
            $return['form'] = "load_fac_time";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "load_fac_time";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function insert_process($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $FacCode = $_SESSION['FacCode'];
        $Sql = "INSERT INTO process (DocNo,FacCode) VALUES ('$DocNo','$FacCode') ";
    
        if(mysqli_query($conn,$Sql)){

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
        $question = $DATA["question"];
        $return['question'] = $question;
        $boolean = false;

        $Sql = "UPDATE process SET WashEndTime = NOW(),WashDetail = '$question' WHERE DocNo = '$DocNo'";
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
        $limit_date = date('Y-m-d '.$DATA["slc_time"]);
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET SendStartTime = '$nowdate',SendLimitTime = '$limit_date' WHERE DocNo = '$DocNo'";
        $return['limit_date'] = $limit_date;
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

        $Sql = "SELECT  TIMEDIFF(SendEndTime,SendStartTime) AS UseTime,
                        TIMEDIFF(SendLimitTime,SendEndTime) AS Overtime 

                FROM    process 
                WHERE   DocNo = '$DocNo'";

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
        else if ($DATA['STATUS'] == 'load_fac_time') {
            load_fac_time($conn, $DATA);
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
