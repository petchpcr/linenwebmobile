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
    $Sql = "SELECT DISTINCT
                    clean.DocNo,
                    clean.IsStatus,
                    clean.IsCheckList,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM
                clean
                INNER JOIN department ON department.DepCode = clean.DepCode AND department.DepCode = clean.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                WHERE site.HptCode = '$siteCode' 
                AND clean.DocDate LIKE '%$search%'
                AND clean.IsStatus = 1 
                ORDER BY clean.IsStatus ASC,clean.DocNo DESC";
    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
        $return[$count]['DepName'] = $Result['DepName'];
        $return[$count]['HptName'] = $Result['HptName'];
        $return[$count]['IsStatus'] = $Result['IsStatus'];
        $return[$count]['IsCheckList'] = $Result['IsCheckList'];

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

function add_rewash($conn, $DATA)
{
    $Menu = "rewash";
    $H_doc = "RW";
    $Userid = $DATA["Userid"];
    $Menu = $DATA["Menu"];
    $siteCode = $DATA["siteCode"];
    $RefDocNo = $DATA["refDocNo"];

    $Sql = "SELECT DepCode FROM department WHERE HptCode = '$siteCode' AND IsDefault = 1";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $DepCode = $Result['DepCode'];

    $Sql = "SELECT FacCode FROM $Menu"." WHERE DocNo = '$RefDocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $FacCode = $Result['FacCode'];

    $Sql = "SELECT          CONCAT('$H_doc',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                            LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                            DATE(NOW()) AS DocDate,
                            CURRENT_TIME() AS RecNow

            FROM            $Menu"."

            INNER JOIN      department 
            ON              $Menu".".DepCode = department.DepCode

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

        $Sql = "    INSERT INTO     $Menu"."
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
        
        if (mysqli_query($conn, $Sql)) {
            $cnt = 0;
            $Sql2 = "SELECT ItemCode,UnitCode,Qty,Weight FROM clean_detail WHERE DocNo = '$RefDocNo'";
            $meQuery = mysqli_query($conn, $Sql2);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $return[$cnt]['ItemCode'] = $Result['ItemCode'];
                $ItemCode = $Result['ItemCode'];
                $UnitCode = $Result['UnitCode'];
                $Qty = $Result['Qty'];
                $Weight = $Result['Weight'];
                $Sql2 = "INSERT INTO $Menu"."_detail ( DocNo,ItemCode,UnitCode,Qty,Weight,IsCancel ) VALUES ('$DocNo','$ItemCode','$UnitCode',$Qty,$Weight,0)";
                mysqli_query($conn, $Sql2);
                $cnt++;
            }
        }
        

        
        $Sql3 = "    INSERT INTO     daily_request
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
        mysqli_query($conn, $Sql3);
                
        // $Sql = "UPDATE $Menu SET IsStatus = 5 WHERE DocNo = '$RefDocNo'";
        // mysqli_query($conn, $Sql);

        $return['user'] = $Userid;
        $return['siteCode'] = $siteCode;
        $return['DepCode'] = $DepCode;
        $return['DocNo'] = $DocNo;
        $return['RefDocNo'] = $RefDocNo;

        $return['status'] = "success";
        $return['form'] = "add_rewash";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "add_rewash";
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
    } else if ($DATA['STATUS'] == 'add_rewash') {
        add_rewash($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
