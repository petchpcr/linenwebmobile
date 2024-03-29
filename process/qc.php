<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';
    date_default_timezone_set("Asia/Bangkok");
    
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
        $Sql = "SELECT * 
                FROM ( 
                        SELECT  cleanstock.DocNo,
                                department.DepName,
                                cleanstock.IsCheckList,
                                cleanstock.IsStatus

                        FROM    cleanstock,department,site

                        WHERE   site.HptCode = '$siteCode' 
                        AND     cleanstock.DocDate LIKE '%$search%' 
                        AND     department.DepCode = cleanstock.DepCode 
                        AND     site.HptCode = department.HptCode 
                        AND     cleanstock.IsStatus > 0
                        AND     cleanstock.IsStatus != 9
                    UNION ALL
                        SELECT  repair.DocNo,
                                department.DepName,
                                repair.IsCheckList,
                                repair.IsStatus

                        FROM    repair,department,site

                        WHERE   site.HptCode = '$siteCode' 
                        AND     repair.DocDate LIKE '%$search%' 
                        AND     department.DepCode = repair.DepCode 
                        AND     site.HptCode = department.HptCode 
                        AND     repair.IsStatus > 0
                        AND     repair.IsStatus != 9
                                
                                )  a 
                ORDER BY IsCheckList ASC, DocNo DESC";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['DepName'] = $Result['DepName'];
            $return[$count]['IsCheckList'] = $Result['IsCheckList'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];

            $count++;
        }
        $return['cnt'] = $count;

        if ($count > 0) {
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

    function add_dirty($conn, $DATA){
        $Userid = $DATA["Userid"];
        $siteCode = $DATA["siteCode"];
        $DepCode = $DATA["DepCode"];
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
            $Sql = "INSERT INTO log ( log ) VALUES ('" . $Result['DocDate'] . " : " . $Result['DocNo'] . " :: '$siteCode' :: '$DepCode'')";
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
                                            dirty.Modify_Date
                                        )
                        VALUES
                                        (
                                            '$DocNo',
                                            DATE(NOW()),
                                            '$DepCode',
                                            '',
                                            0,
                                            NOW(),0,0,
                                            0,0,'',
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
                                            '$DepCode',
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

    function get_doc_type($conn, $DATA){
        $return['ss'] = "success";
        $DocNo = $DATA["DocNo"];
        $Sql = "SELECT  1 AS x
                FROM    cleanstock
                WHERE DocNo = '$DocNo'

                UNION ALL

                SELECT 2 AS x
                FROM    repair
                WHERE DocNo = '$DocNo'";
        $boolean = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['table'] = $Result['x'];
            $boolean = true;
        }
        if ($boolean) {
            $return['status'] = "success";
            $return['DocNo'] = $DocNo;
            $return['form'] = "get_doc_type";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "get_doc_type";
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
        else if ($DATA['STATUS'] == 'add_dirty') {
            add_dirty($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'get_doc_type') {
            get_doc_type($conn, $DATA);
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