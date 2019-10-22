<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function load_dep($conn, $DATA){
        $count = 0;
        $siteCode = $DATA["siteCode"];
        $Sql = "SELECT DepCode, DepName FROM department
                WHERE department.HptCode='$siteCode' AND IsStatus = 0
                ORDER BY DepName ASC";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DepCode'] = $Result['DepCode'];
            $return[$count]['DepName'] = $Result['DepName'];
            $count++;
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
        $return['search'] = $search;
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $siteCode = $DATA["siteCode"];
        $boolean = false;
        $Sql = "SELECT
                        DocNo,
                        IsReceive,
                        IsProcess,
                        IsStatus
                FROM    newlinentable
                WHERE HptCode = '$siteCode' 
                AND DocDate LIKE '%$search%'
                AND IsStatus != 9 
                ORDER BY IsStatus ASC,DocNo DESC";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];

            $count++;
            $boolean = true;
        }
        $return['cnt'] = $count;
        
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
        $FacCode = $_SESSION["FacCode"];
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $return['From'] = $From;
        $Sql = "UPDATE $From SET IsReceive = 1,IsStatus = 2,FacCode = $FacCode,ReceiveDate = NOW() WHERE DocNo = '$DocNo'";

        if(mysqli_query($conn, $Sql)){
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

    function add_newlinentable($conn, $DATA){
        $Userid = $DATA["Userid"];
        $siteCode = $DATA["siteCode"];
        $FacCode = $DATA["FacCode"];
        $Sql = "    SELECT CONCAT('NL',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow
                    FROM newlinentable
                    INNER JOIN site 
                    ON newlinentable.HptCode = site.HptCode
                    WHERE DocNo Like CONCAT('NL',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                    AND site.HptCode = '$siteCode'
                    ORDER BY DocNo DESC LIMIT 1";

        $meQuery = mysqli_query($conn, $Sql);

        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $DocNo = $Result['DocNo'];
            $DocDate = $Result['DocDate'];
            $RecNow  = $Result['RecNow'];
            $count = 1;
            // $Sql = "INSERT INTO log ( log ) VALUES ('" . $Result['DocDate'] . " : " . $Result['DocNo'] . " :: '$siteCode' :: $DepCode')";
            // mysqli_query($conn, $Sql);
        }

        if ($count == 1) {

            $Sql = "    INSERT INTO     newlinentable
                                        ( 
                                            DocNo,
                                            DocDate,
                                            HptCode,
                                            RefDocNo,
                                            TaxNo,
                                            TaxDate,
                                            DiscountPercent,
                                            DiscountBath,
                                            Total,
                                            IsCancel,
                                            Detail,
                                            newlinentable.Modify_Code,
                                            newlinentable.Modify_Date,
                                            FacCode
                                        )
                        VALUES
                                        (
                                            '$DocNo',
                                            DATE(NOW()),
                                            '$siteCode',
                                            '',
                                            0,
                                            NOW(),0,0,
                                            0,0,'',
                                            $Userid,
                                            NOW(),
                                            $FacCode
                                        )";
            mysqli_query($conn,$Sql);

            $Sql2 = "    INSERT INTO     daily_request
                                        (
                                            DocNo,
                                            DocDate,
                                            DepCode,
                                            RefDocNo,
                                            Detail,
                                            Modify_Code,
                                            Modify_Date
                                        )
                        VALUES          (
                                            '$DocNo',
                                            DATE(NOW()),
                                            $DepCode,
                                            '',
                                            'newlinentable',
                                            $Userid,
                                            DATE(NOW())
                                        )";
            // mysqli_query($conn, $Sql2);
            
            $return['user'] = $Userid;
            $return['siteCode'] = $siteCode;
            $return['DocNo'] = $DocNo;

            $return['status'] = "success";
            $return['form'] = "add_newlinentable";
            echo json_encode($return);
            mysqli_close($conn);
            die;

        } else {
            $return['status'] = "failed";
            $return['form'] = "add_newlinentable";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function load_Fac($conn, $DATA){
        if ($_SESSION['lang'] == 'th') {
            $Fname = "FacNameTH";
        } else if ($_SESSION['lang'] == 'en') {
            $Fname = "FacName";
        }
        $count = 0;
        $Sql = "SELECT FacCode,$Fname AS FacName FROM factory WHERE IsCancel=0";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['FacCode'] = $Result['FacCode'];
            $return[$count]['FacName'] = $Result['FacName'];
            $count++;
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_Fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_Fac";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }


    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_dep') {
            load_dep($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_site') {
            load_site($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_doc') {
            load_doc($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_doc_procees') {
            load_doc_procees($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'confirm_yes') {
            confirm_yes($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'add_newlinentable') {
            add_newlinentable($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_Fac') {
            load_Fac($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        } else if ($DATA['STATUS'] == 'load_doc_tracking') {
            load_doc_tracking($conn, $DATA);
        }
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
?>