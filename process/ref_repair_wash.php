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
    $count = 0;
    $search = date_format(date_create($DATA["search"]),"Y-m-d");
    $return['search'] = $DATA["search"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $return['siteCode'] = $siteCode;
    $boolean = false;
    $Sql = "SELECT
                    repair_wash.DocNo,
                    repair_wash.IsReceive,
                    repair_wash.IsProcess,
                    repair_wash.IsStatus,
                    site.HptName
                FROM
                repair_wash
                INNER JOIN site ON site.HptCode = repair_wash.HptCode 
                WHERE site.HptCode = '$siteCode' 
                AND repair_wash.DocDate LIKE '%$search%'
                AND repair_wash.IsStatus = 3
                AND repair_wash.IsStatus != 9 
                ORDER BY repair_wash.IsStatus ASC,repair_wash.DocNo DESC";
    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
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

function add_dirty($conn, $DATA)
{
    if ($DATA["Menu"] == 'clean') {
        $Menu = "cleanstock";
        $H_doc = "CK";
    } else if ($DATA["Menu"] == 'clean_real') {
        $Menu = "clean";
        $H_doc = "CN";
    }
    $Userid = $DATA["Userid"];
    $siteCode = $DATA["siteCode"];
    $DepCode = $DATA["DepCode"];
    $RefDocNo = $DATA["refDocNo"];
    $return['RefDocNo'] = $RefDocNo;

    $Sql = "SELECT FacCode FROM repair_wash WHERE DocNo = '$RefDocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $FacCode = $Result['FacCode'];

    $Sql = "    SELECT          CONCAT('$H_doc',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow

                FROM            $Menu

                INNER JOIN      department 
                ON              $Menu.DepCode = department.DepCode

                WHERE           DocNo Like CONCAT('$H_doc',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
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

        $Sql = "    INSERT INTO     $Menu
                                        ( 
                                            DocNo,
                                            DocDate,
                                            DepCode,
                                            FacCode,
                                            RefDocNo,
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
                                            '$RefDocNo',
                                            0,
                                            DATE(NOW()),
                                            0,0,
                                            0,0,
                                            '',
                                            $Userid,
                                            NOW() 
                                        )";

        mysqli_query($conn, $Sql);
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
                                '$RefDocNo',
                                '$Menu',
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

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_site') {
        load_site($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_dirty') {
        add_dirty($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
