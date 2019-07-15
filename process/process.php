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
        $Sql = "SELECT process.WashStartTime,process.WashStopTime,process.WashEndTime FROM process WHERE process.DocNo = '$DocNo'";
        $Sql2 = "SELECT processtime.processt FROM processtime WHERE processtime.FacCode = '$FacCode'";
        
        if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2)){
            $meQuery = mysqli_query($conn,$Sql);
            $meQuery2 = mysqli_query($conn,$Sql2);

            $boolean = false;
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $return['WashStartTime'] = $Result['WashStartTime'];
                $return['WashStopTime'] = $Result['WashStopTime'];
                $return['WashEndTime'] = $Result['WashEndTime'];
                $boolean = true;
            }
            $return['Bool'] = $boolean;

            $boolean2 = false;
            while ($Result2 = mysqli_fetch_assoc($meQuery2)) {
                $return['processt'] = $Result2['processt'];
                $boolean2 = true;
            }
            $return['Bool2'] = $boolean2;

            if($return['WashStartTime'] == null || $return['WashStartTime'] == ""){

                $nowdate = date('Y-m-d H:i:s');
                $Sql = "UPDATE process SET WashStartTime = '$nowdate',WashStopTime = null,IsStatus = 1,IsStop = 0 WHERE DocNo = '$DocNo'";
                $Sql2 = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo'";

                if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2)){
                    $meQuery = mysqli_query($conn,$Sql);
                    $meQuery2 = mysqli_query($conn,$Sql2);

                    $return['WashStartTime'] = $nowdate;
                    $return['status'] = "success";
                    $return['form'] = "start_wash";
                    echo json_encode($return);
                    mysqli_close($conn);
                    die;
                }else{
                    $return['status'] = "failed";
                    $return['form'] = "start_wash";
                    echo json_encode($return);
                    mysqli_close($conn);
                    die;
                }
                $return['IsNull'] = "Yes";
            }
            else{
                $Sql = "UPDATE process SET WashStopTime = null,IsStatus = 1,IsStop = 0 WHERE DocNo = '$DocNo'";
                $Sql2 = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo'";

                if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2)){
                    $meQuery = mysqli_query($conn,$Sql);
                    $meQuery2 = mysqli_query($conn,$Sql2);

                    $return['Bool'] = $boolean;
                    $return['Bool2'] = $boolean2;
                    $return['IsNull'] = "No";
                    $return['status'] = "success";
                    $return['form'] = "start_wash";
                    echo json_encode($return);
                    mysqli_close($conn);
                    die;
                }else{
                    $return['IsNull'] = "No";
                    $return['status'] = "failed";
                    $return['form'] = "start_wash";
                    echo json_encode($return);
                    mysqli_close($conn);
                    die;
                }
            }
        }else{
            $return['IsNull'] = "Yes";
            $return['status'] = "failed";
            $return['form'] = "start_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function set_end_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $W_End = $DATA["W_End"];
        $endtime = date("Y-m-d H:i:s", strtotime($W_End));
        $Sql = "UPDATE process SET WashEndTime = '$endtime' WHERE DocNo = '$DocNo' ";
        $return['W_End'] = $W_End;
        $return['endTime'] = $endtime;

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

            $return['status'] = "success";
            $return['form'] = "set_end_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "set_end_wash";
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
        $endtime = date("Y-m-d H:i:s");
        $Sql = "UPDATE process SET WashStopTime = null,WashEndTime = '$endtime',IsStatus = 2,IsStop = 0 WHERE DocNo = '$DocNo' ";
        $Sql2 = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
        
        if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2)){
            $meQuery = mysqli_query($conn,$Sql);
            $meQuery2 = mysqli_query($conn,$Sql2);

            $return['endTime'] = $endtime;
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
        $Sql = "SELECT process.WashEndTime FROM process WHERE process.DocNo = '$DocNo'";
        $Sql2 = "UPDATE process SET WashStopTime = null,IsStatus = 2,IsStop = 0 WHERE DocNo = '$DocNo' ";
        $Sql3 = "UPDATE dirty SET IsProcess = 1 WHERE DocNo = '$DocNo' ";
        
        if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2) && $meQuery3 = mysqli_query($conn,$Sql3)){
            $meQuery = mysqli_query($conn,$Sql);
            $meQuery2 = mysqli_query($conn,$Sql2);
            $meQuery3 = mysqli_query($conn,$Sql3);

            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $return['endTime'] = $Result['WashEndTime'];
            }

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

    function use_time_wash($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $WashUseTime = $DATA["WashUseTime"];

        $Sql = "UPDATE process SET WashUseTime = '$WashUseTime' WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

            $return['WashUseTime'] = $WashUseTime;
            $return['status'] = "success";
            $return['form'] = "use_time_wash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "use_time_wash";
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
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "UPDATE process SET PackEndTime = '$nowdate',IsStatus = 3 WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);

            $return['PackEndTime'] = $nowdate;
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
        $DocNo = $DATA["DocNo"];
        $nowdate = date('Y-m-d H:i:s');
        $Sql = "SELECT SendStartTime FROM process WHERE DocNo = '$DocNo'";
        $Sql2 = "UPDATE process SET SendEndTime = '$nowdate',IsStatus = 4 WHERE DocNo = '$DocNo'";
        $Sql3 = "UPDATE dirty SET IsProcess = 3,IsStatus = 3 WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn,$Sql) && $meQuery2 = mysqli_query($conn,$Sql2) && $meQuery3 = mysqli_query($conn,$Sql3)){

            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $return['SendStartTime'] = $Result['SendStartTime'];
            }

            $meQuery = mysqli_query($conn,$Sql2);
            $meQuery2 = mysqli_query($conn,$Sql3);

            $return['SendEndTime'] = $nowdate;
            $return['status'] = "success";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }else{
            $return['status'] = "failed";
            $return['form'] = "end_send";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function cal_overtime($conn, $DATA){
        $SiteCode = $_SESSION["HptCode"];
        $FacCode = $_SESSION["FacCode"];
        $DocNo = $DATA["DocNo"];
        $OverTime = $DATA["SendOverTime"];
        
        $Sql = "SELECT SendTime FROM delivery_time WHERE HptCode = '$SiteCode' AND FacCode = '$FacCode'";
        $return['sql overtime'] = $Sql;

        if($meQuery = mysqli_query($conn,$Sql)){
            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $SetOver = $Result['SendTime'];
            }

            $RealOver = $OverTime-$SetOver;

            if($RealOver < 0){
                $RealOver = 0;
            }

            $Sql2 = "UPDATE process SET SendOverTime = '$RealOver',SendUseTime = '$OverTime' WHERE DocNo = '$DocNo'";
            $meQuery = mysqli_query($conn,$Sql2);
            
            $return['SendUseTime'] = $OverTime;
            $return['SendOverTime'] = $RealOver;
            $return['status'] = "success";
            $return['form'] = "cal_overtime";
            echo json_encode($return);
            mysqli_close($conn);
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
        else if ($DATA['STATUS'] == 'set_end_wash') {
            set_end_wash($conn, $DATA);
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
        else if ($DATA['STATUS'] == 'use_time_wash') {
            use_time_wash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_pack') {
            start_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_pack') {
            end_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'use_time_pack') {
            use_time_pack($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'start_send') {
            start_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'end_send') {
            end_send($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'cal_overtime') {
            cal_overtime($conn, $DATA);
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