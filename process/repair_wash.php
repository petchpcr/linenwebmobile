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
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    $return['search'] = $DATA["search"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $boolean = false;
    $Sql = "SELECT
                repair_wash.DocNo,
                repair_wash.IsStatus,
                repair_wash.Modify_Date,
                department.DepName,
                site.HptCode,
                site.HptName
            FROM
                repair_wash,department,site
            WHERE site.HptCode = '$siteCode' 
            AND repair_wash.DocDate LIKE '%$search%' 
            AND department.DepCode = repair_wash.DepCode 
            AND site.HptCode = department.HptCode
            ORDER BY repair_wash.DocNo DESC";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
        $return[$count]['DepName'] = $Result['DepName'];
        $return[$count]['HptName'] = $Result['HptName'];
        $return[$count]['IsReceive'] = $Result['IsReceive'];
        $return[$count]['IsStatus'] = $Result['IsStatus'];
        $return[$count]['Modify_Date'] = date_format(date_create($Result['Modify_Date']),"d-m-Y");
        $return[$count]['Modify_Time'] = date_format(date_create($Result['Modify_Date']),"H:i:s");

        $count++;
        $boolean = true;
    }
    $return['Sql'] = $Sql;
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

function confirm_yes($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $Sql = "UPDATE dirty SET IsReceive = 1,IsStatus = 2 WHERE DocNo = '$DocNo'";

    if (mysqli_query($conn, $Sql)) {
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

function load_dep($conn, $DATA)
{
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

function load_Fac($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $count = 0;
    if ($_SESSION['leng'] = 'th') {
        $Fname = 'FacNameTH';
    } else if ($_SESSION['leng'] = 'en') {
        $Fname = 'FacName';
    }
    $Sql = "SELECT FacCode,$Fname AS Fname FROM factory
                WHERE HptCode='$siteCode' 
                AND IsCancel = 0";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['FacCode'][$count] = $Result['FacCode'];
        $return['FacName'][$count] = $Result['Fname'];
        $count++;
    }
    $return['cnt'] = $count;

    if ($count > 0) {
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

function add_repair_wash($conn, $DATA)
{
    $siteCode = $DATA["siteCode"];
    $FacCode = $DATA["FacCode"];
    $Userid = $_SESSION['Userid'];
    $count = 0;

    $Sql = "SELECT DepCode FROM department WHERE HptCode = '$siteCode' AND IsDefault = 1";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $DepCode = $Result['DepCode'];

    $Sql = "    SELECT          CONCAT('RPW',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow

                FROM            repair_wash

                INNER JOIN      department 
                ON              repair_wash.DepCode = department.DepCode


                WHERE           DocNo Like CONCAT('RPW',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                AND             department.HptCode = '$siteCode'

                ORDER BY        DocNo DESC LIMIT 1";


    $meQuery = mysqli_query($conn, $Sql);

    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $DocNo = $Result['DocNo'];
        $count = 1;
        $Sql = "INSERT INTO log ( log ) VALUES ('" . $Result['DocDate'] . " : " . $Result['DocNo'] . " :: '$siteCode' :: $DepCode')";
        mysqli_query($conn, $Sql);
    }
    $return['cnt'] = $count;
    $return['DocNo'] = $DocNo;
    $return['Userid'] = $Userid;
    $return['DepCode'] = $DepCode;
    $return['siteCode'] = $siteCode;

    $Sql = "    INSERT INTO     repair_wash
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
                                            null,
                                            0,
                                            DATE(NOW()),
                                            0,0,
                                            0,0,
                                            '',
                                            $Userid,
                                            NOW() 
                                        )";


    if (mysqli_query($conn, $Sql)) {
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
    } else if ($DATA['STATUS'] == 'confirm_yes') {
        confirm_yes($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_dep') {
        load_dep($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_Fac') {
        load_Fac($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_repair_wash') {
        add_repair_wash($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
