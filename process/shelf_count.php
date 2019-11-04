<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
    function load_dep($conn, $DATA){
        $count = 0;
        $siteCode = $DATA["siteCode"];
        $Sql = "SELECT DepCode, DepName FROM department
                WHERE department.HptCode='$siteCode' 
                AND IsStatus = 0
                AND IsActive = 1
                ORDER BY DepName ASC";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DepCode'] = $Result['DepCode'];
            $return[$count]['DepName'] = $Result['DepName'];
            $count++;
            $boolean = true;
        }
        $return['cnt'] = $count;
        
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
        $return['search'] = $DATA["search"];
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $siteCode = $DATA["siteCode"];
        $boolean = false;
        $Sql = "SELECT
                        shelfcount.DocNo,
                        shelfcount.IsStatus,
                        shelfcount.DvStartTime,
                        shelfcount.signature,
                        department.DepName,
                        site.HptCode,
                        site.HptName
                FROM    shelfcount
                INNER JOIN department ON department.DepCode = shelfcount.DepCode 
                INNER JOIN site ON site.HptCode = department.HptCode 
                WHERE site.HptCode = '$siteCode' 
                AND shelfcount.DocDate LIKE '%$search%'
                AND shelfcount.IsStatus = 0 
                ORDER BY shelfcount.IsStatus ASC,shelfcount.DocNo DESC";
        $return['sql'] = $Sql;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['DvStartTime'] = $Result['DvStartTime'];
            $return[$count]['signature'] = $Result['signature'];

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

    function add_sc($conn, $DATA){
        $Userid = $DATA["Userid"];
        $siteCode = $DATA["siteCode"];
        $DepCode = $DATA["DepCode"];
        $TimeName = $DATA["TimeName"];
        $return['TimeName'] = $TimeName;
        $Sql = "    SELECT CONCAT('SC',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow
                    FROM shelfcount
                    INNER JOIN department 
                    ON shelfcount.DepCode = department.DepCode
                    WHERE DocNo Like CONCAT('SC',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                    AND department.HptCode = '$siteCode'
                    ORDER BY DocNo DESC LIMIT 1";

        $meQuery = mysqli_query($conn, $Sql);

        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $DocNo = $Result['DocNo'];
            $DocDate = $Result['DocDate'];
            $RecNow  = $Result['RecNow'];
            $count = 1;
            $Sql = "INSERT INTO log ( log ) VALUES ('" . $Result['DocDate'] . " : " . $Result['DocNo'] . " :: '$siteCode' :: $DepCode')";
            mysqli_query($conn, $Sql);
        }

        if ($count == 1) {

            $Sql = "    INSERT INTO     shelfcount
                                        ( 
                                            DocNo,
                                            DocDate,
                                            DepCode,
                                            RefDocNo,
                                            ScStartTime,
                                            TaxNo,
                                            TaxDate,
                                            DiscountPercent,
                                            DiscountBath,
                                            Total,
                                            IsCancel,
                                            Detail,
                                            ScTime,
                                            Modify_Code,
                                            Modify_Date
                                        )
                        VALUES
                                        (
                                            '$DocNo',
                                            DATE(NOW()),
                                            $DepCode,
                                            '',
                                            NOW(),
                                            0,
                                            NOW(),0,0,
                                            0,0,'',
                                            $TimeName,
                                            $Userid,
                                            NOW()
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
                                            'shelfcount',
                                            $Userid,
                                            DATE(NOW())
                                        )";
            mysqli_query($conn, $Sql2);
            
            $return['user'] = $Userid;
            $return['siteCode'] = $siteCode;
            $return['DepCode'] = $DepCode;
            $return['DocNo'] = $DocNo;

            $return['status'] = "success";
            $return['form'] = "add_sc";
            echo json_encode($return);
            mysqli_close($conn);
            die;

        } else {
            $return['status'] = "failed";
            $return['form'] = "add_sc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function show_item($conn, $DATA) {
        $DocNo = $DATA["DocNo"];
        $Sql = "SELECT DepCode FROM shelfcount WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $return['DepCode'] = $Result['DepCode'];
        $return['DocNo'] = $DocNo;

        $return['status'] = "success";
        $return['form'] = "show_item";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }

    function load_Fac($conn, $DATA){
        $count = 0;
        $Sql = "SELECT FacCode,FacName FROM factory WHERE IsCancel=0";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['FacCode'] = $Result['FacCode'];
            $return[$count]['FacName'] = $Result['FacName'];
            $count++;
            $boolean = true;
        }
        $return['cnt'] = $count;

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

    function load_Time($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $count = 0;
        $boolean = false;
        $Sql = "SELECT sc_time_2.ID,sc_time_2.TimeName FROM sc_time_2 
                INNER JOIN sc_express ON sc_time_2.ID = sc_express.Time_ID 
                WHERE sc_express.HptCode = '$siteCode'";
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ID'] = $Result['ID'];
            $return[$count]['TimeName'] = $Result['TimeName'];
            $count++;
            $boolean = true;
        }
        $return['cnt'] = $count;

        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_Time";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_Time";
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
        else if ($DATA['STATUS'] == 'confirm_yes') {
            confirm_yes($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'show_item') {
            show_item($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'add_sc') {
            add_sc($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_Fac') {
            load_Fac($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_Time') {
            load_Time($conn, $DATA);
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