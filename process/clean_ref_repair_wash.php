<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_site($conn, $DATA)
{
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

function load_doc($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $count = 0;
    $search = date_format(date_create($DATA["search"]),"Y-m-d");
    $search2 = date_format(date_create($DATA["search2"]),"Y-m-d");
    $return['search'] = $DATA["search"];
    $return['search2'] = $DATA["search2"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    if ($search2 == null || $search2 == "") {
        $search2 = date('Y-m-d');
    }
    if ($_SESSION['lang'] == 'th') {
        $FacName = "FacNameTH";
    } else {
        $FacName = "FacName";
    }
    
    $Sql = "SELECT
                repair_wash.DocNo,
                repair_wash.FacCode,
                factory.$FacName AS FacName,
                repair_wash.IsReceive,
                repair_wash.IsProcess,
                repair_wash.IsStatus,
                site.HptName
            FROM
            repair_wash
            INNER JOIN site ON site.HptCode = repair_wash.HptCode 
            INNER JOIN factory ON factory.FacCode = repair_wash.FacCode 
            WHERE site.HptCode = '$siteCode' 
            AND repair_wash.DocDate BETWEEN '$search' AND '$search2' 
            AND repair_wash.IsStatus = 3
            AND repair_wash.IsStatus != 9 
            ORDER BY repair_wash.DocNo DESC";
    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
        $return[$count]['FacCode'] = $Result['FacCode'];
        $return[$count]['FacName'] = $Result['FacName'];
        $return[$count]['HptName'] = $Result['HptName'];
        $return[$count]['IsReceive'] = $Result['IsReceive'];
        $return[$count]['IsProcess'] = $Result['IsProcess'];
        $return[$count]['IsStatus'] = $Result['IsStatus'];
        $count++;
    }

    $return['cnt'] = $count;
    $return['siteCode'] = $siteCode;

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

function add_repair_wash($conn, $DATA)
{
    $Userid = $DATA["Userid"];
    $siteCode = $DATA["siteCode"];
    $FacCode = $DATA["FacCode"];
    $DepCode = $DATA["DepCode"];
    $RefDocNo_ar = $DATA["RefDocNo_ar"];
    $return['RefDocNo_ar'] = $RefDocNo_ar;

    $Sql = "    SELECT          CONCAT('CN',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow

                FROM            clean

                INNER JOIN      department 
                ON              clean.DepCode = department.DepCode

                WHERE           DocNo Like CONCAT('CN',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                AND             department.HptCode = '$siteCode'

                ORDER BY        DocNo DESC LIMIT 1";

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

        $Sql = "    INSERT INTO     clean
                                        ( 
                                            DocNo,
                                            DocDate,
                                            DepCode,
                                            FacCode,
                                            TaxNo,
                                            TaxDate,
                                            DiscountPercent,
                                            DiscountBath,
                                            Total,
                                            IsCancel,
                                            Detail,
                                            Modify_Code,
                                            Modify_Date
                                        )
                        VALUES
                                        ( 
                                            '$DocNo',
                                            DATE(NOW()),
                                            '$DepCode',
                                            '$FacCode',
                                            0,
                                            DATE(NOW()),
                                            0,0,
                                            0,0,
                                            '',
                                            $Userid,
                                            NOW() 
                                        )";

        if(mysqli_query($conn, $Sql)){
            foreach($RefDocNo_ar as $key => $RefDocNo){
                $Sql2 = "    INSERT INTO     clean_ref
                                                ( 
                                                    DocNo,
                                                    RefDocNo
                                                )
                                VALUES
                                                ( 
                                                    '$DocNo',
                                                    '$RefDocNo'
                                                )";
                mysqli_query($conn, $Sql2);
                $return['Sql0'] = $Sql2;

            }
        }
        
        $Sql2 = "    INSERT INTO     daily_request
                            (
                                DocNo,
                                DocDate,
                                DepCode,
                                Detail,
                                Modify_Code,
                                Modify_Date
                            )
                    VALUES          (
                                '$DocNo',
                                DATE(NOW()),
                                '$DepCode',
                                'clean',
                                $Userid,
                                DATE(NOW())
                            )";
        mysqli_query($conn, $Sql2);
        
        // $Sql = "UPDATE repair_wash SET IsStatus = 4 WHERE DocNo = '$RefDocNo'";
        // mysqli_query($conn, $Sql);

        $return['user'] = $Userid;
        $return['siteCode'] = $siteCode;
        $return['DepCode'] = $DepCode;
        $return['DocNo'] = $DocNo;

        $return['status'] = "success";
        $return['form'] = "add_repair_wash";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "add_repair_wash";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_site') {
        load_site($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_repair_wash') {
        add_repair_wash($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
