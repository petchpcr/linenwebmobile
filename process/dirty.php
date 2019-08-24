<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

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

    function load_doc_procees($conn, $DATA){
        $count = 0;
        $search = date_format(date_create($DATA["search"]),"Y-m-d");
        $return['search'] = $DATA["search"];
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $siteCode = $DATA["siteCode"];
        $FacCode = $_SESSION['FacCode'];
        $boolean = false;
        $Sql = "SELECT
                    dirty.DocNo,
                    dirty.IsReceive,
                    dirty.IsProcess,
                    dirty.IsStatus,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM
                    dirty
                INNER JOIN department ON department.DepCode = dirty.DepCode AND department.DepCode = dirty.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND dirty.DocDate LIKE '%$search%'
                AND dirty.FacCode = '$FacCode'
                AND dirty.IsStatus > 0 
                ORDER BY dirty.IsStatus ASC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['From'] = "dirty";

            $DocNo = $Result['DocNo'];
            $Sql2 = "SELECT Signature FROM process WHERE DocNo = '$DocNo'";
            $meQuery2 = mysqli_query($conn, $Sql2);
            while ($Result = mysqli_fetch_assoc($meQuery2)) {
                $return[$count]['Signature'] = $Result['Signature'];
            }

            $count++;
            $boolean = true;
        }

        $Sql = "SELECT
                    newLinenTable.DocNo,
                    newLinenTable.IsReceive,
                    newLinenTable.IsStatus,
                    newLinenTable.IsProcess,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM newLinenTable
                INNER JOIN department ON department.DepCode = newLinenTable.DepCode AND department.DepCode = newLinenTable.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND newLinenTable.IsStatus > 1
                AND newLinenTable.DocDate LIKE '%$search%'
                ORDER BY newLinenTable.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['From'] = "newLinenTable";

            $count++;
            $boolean = true;
        }

        $Sql = "SELECT
                    rewash.DocNo,
                    rewash.IsReceive,
                    rewash.IsProcess,
                    rewash.IsStatus,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM rewash
                INNER JOIN department ON department.DepCode = rewash.DepCode AND department.DepCode = rewash.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND rewash.FacCode = '$FacCode'
                AND rewash.DocDate LIKE '%$search%'
                ORDER BY rewash.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['From'] = "rewash";

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

    function load_doc_tracking($conn, $DATA){
        $count = 0;
        $search = date_format(date_create($DATA["search"]),"Y-m-d");
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $siteCode = $DATA["siteCode"];
        $boolean = false;
        $Sql = "SELECT
                    dirty.DocNo,
                    dirty.IsReceive,
                    dirty.IsStatus,
                    dirty.IsProcess,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM
                    dirty
                INNER JOIN department ON department.DepCode = dirty.DepCode AND department.DepCode = dirty.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND dirty.DocDate LIKE '%$search%'
                AND dirty.IsStatus > 1 
                ORDER BY dirty.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['From'] = "dirty";

            // $DocNo = $Result['DocNo'];
            // $Sql2 = "SELECT IsStatus FROM process WHERE DocNo = '$DocNo'";
            // $meQuery2 = mysqli_query($conn, $Sql2);
            // while ($Result = mysqli_fetch_assoc($meQuery2)) {
            //     $return[$count]['IsProcess'] = $Result['IsStatus'];
            // }

            $count++;
            $boolean = true;
        }

        $Sql = "SELECT
                    rewash.DocNo,
                    rewash.IsReceive,
                    rewash.IsStatus,
                    rewash.IsProcess,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM rewash
                INNER JOIN department ON department.DepCode = rewash.DepCode AND department.DepCode = rewash.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND rewash.IsStatus > 1
                AND rewash.DocDate LIKE '%$search%'
                ORDER BY rewash.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['From'] = "rewash";

            // $DocNo = $Result['DocNo'];
            // $Sql2 = "SELECT IsStatus FROM process WHERE DocNo = '$DocNo'";
            // $meQuery2 = mysqli_query($conn, $Sql2);
            // while ($Result = mysqli_fetch_assoc($meQuery2)) {
            //     $return[$count]['IsProcess'] = $Result['IsStatus'];
            // }

            $count++;
            $boolean = true;
        }

        $Sql = "SELECT
                    newLinenTable.DocNo,
                    newLinenTable.IsReceive,
                    newLinenTable.IsStatus,
                    newLinenTable.IsProcess,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM newLinenTable
                INNER JOIN department ON department.DepCode = newLinenTable.DepCode AND department.DepCode = newLinenTable.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND newLinenTable.IsStatus > 1
                AND newLinenTable.DocDate LIKE '%$search%'
                ORDER BY newLinenTable.DocNo DESC";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
            $return[$count]['From'] = "newLinenTable";

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
                        dirty.DocNo,
                        dirty.IsReceive,
                        dirty.IsProcess,
                        dirty.IsStatus,
                        department.DepName,
                        site.HptCode,
                        site.HptName
                FROM    dirty
                INNER JOIN department ON department.DepCode = dirty.DepCode AND department.DepCode = dirty.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND dirty.DocDate LIKE '%$search%'
                ORDER BY dirty.DocNo DESC";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['IsReceive'] = $Result['IsReceive'];
            $return[$count]['IsProcess'] = $Result['IsProcess'];
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

    function add_dirty($conn, $DATA){
        $Userid = $DATA["Userid"];
        $siteCode = $DATA["siteCode"];
        $DepCode = $DATA["DepCode"];
        $FacCode = $DATA["FacCode"];
        $Sql = "    SELECT CONCAT('DT',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow
                    FROM dirty
                    INNER JOIN department 
                    ON dirty.DepCode = department.DepCode
                    WHERE DocNo Like CONCAT('DT',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
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

            $Sql = "    INSERT INTO     dirty
                                        ( 
                                            DocNo,
                                            DocDate,
                                            DepCode,
                                            RefDocNo,
                                            TaxNo,
                                            TaxDate,
                                            DiscountPercent,
                                            DiscountBath,
                                            Total,
                                            IsCancel,
                                            Detail,
                                            dirty.Modify_Code,
                                            dirty.Modify_Date,
                                            FacCode
                                        )
                        VALUES
                                        (
                                            '$DocNo',
                                            DATE(NOW()),
                                            $DepCode,
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
                                            'Dirty',
                                            $Userid,
                                            DATE(NOW())
                                        )";
            mysqli_query($conn, $Sql2);
            
            $return['user'] = $Userid;
            $return['siteCode'] = $siteCode;
            $return['DepCode'] = $DepCode;
            $return['DocNo'] = $DocNo;

            $return['status'] = "success";
            $return['form'] = "add_dirty";
            echo json_encode($return);
            mysqli_close($conn);
            die;

        } else {
            $return['status'] = "failed";
            $return['form'] = "add_dirty";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
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
        else if ($DATA['STATUS'] == 'add_dirty') {
            add_dirty($conn, $DATA);
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