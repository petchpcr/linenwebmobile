<?php
    session_start();
    require '../connect/connect.php';

    function load_process($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $boolean = false;
        $Sql = "SELECT
                    process.DocNo,
                    process.WashStartTime,
                    process.WashStopTime,
                    process.WashEndTime,
                    TIME_TO_SEC(TIMEDIFF(process.WashEndTime,NOW())) AS Diff_Sec,
                    TIMEDIFF(process.WashEndTime,process.WashStopTime) AS Diff_Time,
                    process.WashUseTime,
                    process.PackStartTime,
                    process.PackEndTime,
                    process.PackUseTime,
                    process.SendStartTime,
                    process.SendEndTime,
                    process.SendUseTime,
                    process.SendOverTime,
                    process.IsStatus,
                    process.IsStop,
                    process.Signature,
                    department.HptCode
                FROM
                    process
                INNER JOIN dirty ON dirty.DocNo = process.DocNo
                INNER JOIN department ON department.DepCode = dirty.DepCode
                WHERE process.DocNo = '$DocNo'";
        $return['sql'] = $Sql;
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

    function insert_process($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "INSERT INTO process (DocNo) VALUES ('$DocNo') ";
    
        if($meQuery = mysqli_query($conn,$Sql)){
            //$meQuery = mysqli_query($conn,$Sql);

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
        $FacCode = $_SESSION["FacCode"];

        $Sql = "SELECT COUNT(WashStartTime) AS ChkStart, COUNT(WashStopTime) AS ChkStop FROM process WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $ChkStart = $Result['ChkStart'];
            $ChkStop = $Result['ChkStop'];
        }

        if ($ChkStart == 0) { // ถ้ายังไม่เคยกดเริ่ม
            // ตั้งเวลาเริ่ม
            $Sql = "UPDATE process SET WashStartTime = NOW() WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            // คำนวณเวลาจบ (ด้วยการเพิ่มไป 1 ชม. จากเวลาเริ่ม)
            $Sql = "SELECT  ADDTIME((SELECT WashStartTime FROM process WHERE DocNo = '$DocNo'), 
                            SEC_TO_TIME((SELECT processt FROM processtime WHERE FacCode = '$FacCode')*60)) AS WashEndTime";
            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $EndTime = $Result['WashEndTime'];
            }

            // ตั้งเวลาจบ
            $Sql = "UPDATE process SET WashEndTime = '$EndTime',WashStopTime = NULL,IsStatus = 1,IsStop = 0 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);
            $DiffSec = 1;
        }
        else { // ถ้าเคยกดเริ่มแล้ว
            if ($ChkStop > 0) { // และกำลังหยุด
                // หาความต่างของ "เวลาหยุด" และเวลาจบ (หน่วยเป็นวินาที)
                $Sql = "SELECT TIME_TO_SEC(TIMEDIFF(WashEndTime,WashStopTime)) AS DiffSec FROM process WHERE DocNo = '$DocNo'";
                $meQuery = mysqli_query($conn,$Sql);
                while ($Result = mysqli_fetch_assoc($meQuery)) {
                    $DiffSec = $Result['DiffSec'];
                }

                // เพิ่มเวลาจบให้มากขึ้น ตามวินาทีที่เหลืออยู่
                $Sql = "UPDATE process SET WashEndTime = ADDTIME(NOW(),TIMEDIFF(WashEndTime,WashStopTime)),
                                            WashStopTime = NULL,IsStatus = 1,IsStop = 0 WHERE DocNo = '$DocNo'";
                mysqli_query($conn,$Sql);
            }
        }

        if($DiffSec == null || $DiffSec == ""){
            $return['status'] = "failed";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
        else {
            $return['status'] = "success";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function stop_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET WashStopTime = '$nowdate',IsStop = 1 WHERE DocNo = '$DocNo' ";
        $Sql2 = "UPDATE dirty SET IsProcess = 2 WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2)){
            $meQuery = mysqli_query($conn,$Sql);
            $meQuery2 = mysqli_query($conn,$Sql2);

            $return['stopTime'] = $nowdate;
            $return['status'] = "success";
            $return['form'] = "stop_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "stop_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function do_end_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $boolean = false;

        $Sql = "UPDATE process SET WashStopTime = null,WashEndTime = NOW(),IsStatus = 2,IsStop = 0 WHERE DocNo = '$DocNo' ";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(WashEndTime,WashStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";
        $return['Sql'] = $Sql;

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET WashUseTime = '$UseTime' WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "do_end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "do_end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function auto_end_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];

        $Sql = "SELECT  TIMEDIFF(WashEndTime,WashStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";
        $return['Sql'] = $Sql;

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET WashStopTime = null,WashUseTime = '$UseTime',IsStatus = 2,IsStop = 0 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
            mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "auto_end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "auto_end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function start_pack($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET PackStartTime = '$nowdate' WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

            $return['PackStartTime'] = $nowdate;
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
        $boolean = false;

        $Sql = "UPDATE process SET PackEndTime = NOW(),IsStatus = 3 WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(PackEndTime,PackStartTime) AS UseTime

                FROM    process 
                WHERE   DocNo = '$DocNo'";
        $return['Sql'] = $Sql;

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $boolean = true;
        }

        if($boolean){
            $Sql = "UPDATE process SET PackUseTime = '$UseTime' WHERE DocNo = '$DocNo'";
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
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET SendStartTime = '$nowdate' WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

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
        $boolean = false;

        $Sql = "UPDATE process SET SendEndTime = NOW(),IsStatus = 4 WHERE DocNo = '$DocNo'";
        mysqli_query($conn,$Sql);

        $Sql = "SELECT  TIMEDIFF(process.SendEndTime,process.SendStartTime) AS UseTime,
                        TIMEDIFF((delivery_time.SendTime)*100,TIMEDIFF(process.SendEndTime,process.SendStartTime)) AS Overtime 

                FROM    process,delivery_time 
                WHERE   process.DocNo = '$DocNo'
                AND     delivery_time.HptCode = '$SiteCode'
                AND     delivery_time.FacCode = '$FacCode'";
        $return['Sql'] = $Sql;

        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $UseTime = $Result['UseTime'];
            $Overtime = $Result['Overtime'];
            $boolean = true;
        }

        if ($boolean) {
            $Sql = "UPDATE process SET SendUseTime = '$UseTime',SendOverTime = '$Overtime' WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

            $Sql = "UPDATE dirty SET IsProcess = 3 WHERE DocNo = '$DocNo'";
            mysqli_query($conn,$Sql);

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
    }

    function logout($conn, $DATA){

        $logout = $DATA["Confirm"];

        if ($logout == 1) {
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
        else if ($DATA['STATUS'] == 'auto_end_wash') {
            auto_end_wash($conn, $DATA);
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