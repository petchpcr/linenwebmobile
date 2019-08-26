<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_site($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $Sql = "SELECT site.HptName FROM site WHERE site.HptCode = '$siteCode'";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['HptName'] = $Result['HptName'];
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_site";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_site";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function load_doc($conn, $DATA){
        $count = 0;
        $search = date_format(date_create($DATA["search"]),"Y-m-d");
        $return['search'] = $DATA["search"];
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $siteCode = $DATA["siteCode"];
        $boolean = false;
        $Sql = "SELECT
                    clean.DocNo,
                    clean.IsStatus,
                    clean.IsCheckList,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM
                    clean,department,site
                WHERE site.HptCode = '$siteCode' 
                AND clean.DocDate = '$search' 
                AND department.DepCode = clean.DepCode AND department.DepCode = clean.DepCode
                AND site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                ORDER BY clean.IsStatus,clean.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsCheckList'] = $Result['IsCheckList'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];

            $count++;
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_doc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_doc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function confirm_yes($conn, $DATA){
        $DocNo = $DATA["DocNo"];
        $Sql = "UPDATE dirty SET IsReceive = 1,IsStatus = 2 WHERE DocNo = '$DocNo'";

        if($meQuery = mysqli_query($conn, $Sql)){
            $return['DocNo'] = $DocNo;
            $return['status'] = "success";
            $return['form'] = "confirm_yes";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "confirm_yes";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function load_dep($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $Sql = "SELECT DepCode FROM department
                WHERE department.HptCode='$siteCode' 
                AND IsStatus = 0
                AND IsDefault = 1";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['DepCode'] = $Result['DepCode'];
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_dep";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_dep";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_site') {
            load_site($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_doc') {
            load_doc($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'confirm_yes') {
            confirm_yes($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        }else if ($DATA['STATUS'] == 'load_dep') {
            load_dep($conn, $DATA);
        }
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
?>