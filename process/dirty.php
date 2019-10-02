<?php
session_start();
require '../connect/connect.php';
require 'logout.php';

function load_dep($conn, $DATA)
{
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

function load_doc_procees($conn, $DATA)
{
    $count = 0;
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    $return['search'] = $DATA["search"];
    $From = $DATA["From"];
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $FacCode = $_SESSION['FacCode'];
    $boolean = false;
    if ($From == 'all') {
        $Sql = "SELECT * From (SELECT
            newlinentable.DocNo,
            newlinentable.IsReceive,
            newlinentable.IsProcess,
            newlinentable.IsStatus,
            'newlinentable' AS F
            FROM
                newlinentable
            WHERE HptCode = '$siteCode' 
            AND newlinentable.DocDate LIKE '%$search%'
            AND newlinentable.FacCode = '$FacCode'
            AND newlinentable.IsStatus > 0
            AND newlinentable.IsStatus != 9 
    
            UNION ALL       
    
            SELECT
            repair_wash.DocNo,
            repair_wash.IsReceive,
            repair_wash.IsProcess,
            repair_wash.IsStatus,
            'repair_wash' AS F
            FROM
                repair_wash
            WHERE HptCode = '$siteCode' 
            AND repair_wash.DocDate LIKE '%$search%'
            AND repair_wash.FacCode = '$FacCode'
            AND repair_wash.IsStatus > 0 
            AND repair_wash.IsStatus != 9 
            UNION ALL                 
            SELECT
            dirty.DocNo,
            dirty.IsReceive,
            dirty.IsProcess,
            dirty.IsStatus,
            'dirty' AS F
            FROM
                dirty
            WHERE HptCode = '$siteCode' 
            AND dirty.DocDate LIKE '%$search%'
            AND dirty.FacCode = '$FacCode'
            AND dirty.IsStatus > 0
            AND dirty.IsStatus != 9 )a

            ORDER BY IsProcess ASC,DocNo DESC";
    } else {
            $Sql = "SELECT
                        DocNo,
                        IsReceive,
                        IsProcess,
                        IsStatus,
                        '$From' AS F
                    FROM
                        $From
                    WHERE
                        HptCode = '$siteCode'
                    AND DocDate LIKE '%$search%'
                    AND FacCode = '$FacCode'
                    AND IsStatus > 0
                    AND IsStatus != 9
                    ORDER BY
                        IsStatus ASC,
                        DocNo DESC";
    }
    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
        $return[$count]['IsReceive'] = $Result['IsReceive'];
        $return[$count]['IsProcess'] = $Result['IsProcess'];
        $return[$count]['IsStatus'] = $Result['IsStatus'];
        $return[$count]['From'] = $Result['F'];

        $DocNo = $Result['DocNo'];
        $Sql2 = "SELECT Signature FROM process WHERE DocNo = '$DocNo'";
        $meQuery2 = mysqli_query($conn, $Sql2);
        while ($Result = mysqli_fetch_assoc($meQuery2)) {
            $return[$count]['Signature'] = $Result['Signature'];
        }

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

function load_doc_tracking($conn, $DATA)
{
    $count = 0;
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $boolean = false;
    $Sql = "SELECT * From (SELECT
                    dirty.DocNo,
                    dirty.IsReceive,
                    dirty.IsStatus,
                    dirty.IsProcess,
                'dirty' AS F
                FROM
                    dirty
                WHERE HptCode = '$siteCode' 
                AND dirty.DocDate LIKE '%$search%'
                AND dirty.IsStatus > 1 
                AND dirty.IsStatus != 9 
                UNION ALL
                SELECT
                    repair_wash.DocNo,
                    repair_wash.IsReceive,
                    repair_wash.IsStatus,
                    repair_wash.IsProcess,
                'repair_wash' AS F
                FROM repair_wash
                WHERE HptCode = '$siteCode' 
                AND repair_wash.IsStatus > 1
                AND repair_wash.IsStatus != 9 
                AND repair_wash.DocDate LIKE '%$search%'
                UNION ALL
                SELECT
                    newlinentable.DocNo,
                    newlinentable.IsReceive,
                    newlinentable.IsStatus,
                    newlinentable.IsProcess,
                'newlinentable' AS F
                FROM newlinentable
                WHERE HptCode = '$siteCode' 
                AND newlinentable.IsStatus > 1
                AND newlinentable.IsStatus != 9 
                AND newlinentable.DocDate LIKE '%$search%')a

                ORDER BY IsProcess ASC,DocNo DESC";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['DocNo'] = $Result['DocNo'];
        $return[$count]['IsReceive'] = $Result['IsReceive'];
        $return[$count]['IsStatus'] = $Result['IsStatus'];
        $return[$count]['IsProcess'] = $Result['IsProcess'];
        $return[$count]['From'] = $Result['F'];

        // $DocNo = $Result['DocNo'];
        // $Sql2 = "SELECT IsStatus FROM process WHERE DocNo = '$DocNo'";
        // $meQuery2 = mysqli_query($conn, $Sql2);
        // while ($Result = mysqli_fetch_assoc($meQuery2)) {
        //     $return[$count]['IsProcess'] = $Result['IsStatus'];
        // }

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

function load_doc($conn, $DATA)
{
    $count = 0;
    $search = date_format(date_create($DATA["search"]), "Y-m-d");
    $return['search'] = $search;
    if ($search == null || $search == "") {
        $search = date('Y-m-d');
    }
    $siteCode = $DATA["siteCode"];
    $boolean = false;
    $Sql = "SELECT
                    DocNo,
                    IsReceive,
                    IsProcess,
                    IsStatus
            FROM    dirty
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

function receive_zero($conn, $DATA)
{
    $count = 0;
    $DocNo = $DATA["DocNo"];
    $From = $DATA["From"];
    $return['DocNo'] = $DocNo;
    $return['From'] = $From;

    $Sql = "SELECT ".$From."_detail.ItemCode,SUM(".$From."_detail.Qty) AS Qty,item.ItemName 
            FROM ".$From."_detail 
            INNER JOIN item ON ".$From."_detail.ItemCode = item.ItemCode 
            WHERE DocNo = '$DocNo'
            GROUP BY ".$From."_detail.ItemCode";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return[$count]['ItemCode'] = $Result['ItemCode'];
        $return[$count]['ItemName'] = $Result['ItemName'];
        $return[$count]['Qty'] = $Result['Qty'];
        $count++;
    }
    $return['count'] = $count;
    $return['Sql'] = $Sql;

    if ($count > 0) {
        $return['DocNo'] = $DocNo;
        $return['status'] = "success";
        $return['form'] = "receive_zero";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "receive_zero";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

function confirm_yes($conn, $DATA)
{
    $FacCode = $_SESSION["FacCode"];
    $DocNo = $DATA["DocNo"];
    $From = $DATA["From"];
    $Str_ItemCode = $DATA["Str_ItemCode"];
    $Str_Qty = $DATA["Str_Qty"];
    $count = 0;
    $return['From'] = $From;

    $Arr_ItemCode = explode(",", $Str_ItemCode);
    $Arr_Qty = explode(",", $Str_Qty);
    $cnt_Arr = sizeof($Arr_ItemCode, 0);
    
    for ($i = 0; $i < $cnt_Arr; $i++){
        $Sql = "UPDATE ".$From."_detail SET ReceiveQty = $Arr_Qty[$i] WHERE DocNo = '$DocNo' AND ItemCode = '$Arr_ItemCode[$i]'";
        $return['Sql'] = $Sql;
        if (mysqli_query($conn, $Sql)) {
            $count++;
        }
        $return['Sql'] = $Sql;
    }

    if ($count > 0) {
        $Sql = "UPDATE $From SET IsReceive = 1,IsStatus = 2,FacCode = $FacCode,ReceiveDate = NOW() WHERE DocNo = '$DocNo'";

        if (mysqli_query($conn, $Sql)) {
            $return['DocNo'] = $DocNo;
            $return['status'] = "success";
            $return['form'] = "confirm_yes";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['cause'] = "update IsReceive fail";
            $return['status'] = "failed";
            $return['form'] = "confirm_yes";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    } else {
        // $return['cause'] = "update loop(".$count.") != array size(".$cnt_Arr.")";
        $return['cause'] = "count = ".$count;
        $return['status'] = "failed";
        $return['form'] = "confirm_yes";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
    
}

function add_dirty($conn, $DATA)
{
    $Userid = $DATA["Userid"];
    $siteCode = $DATA["siteCode"];
    $FacCode = $DATA["FacCode"];
    $Sql = "    SELECT CONCAT('DT',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                                DATE(NOW()) AS DocDate,
                                CURRENT_TIME() AS RecNow
                    FROM dirty
                    INNER JOIN site 
                    ON dirty.HptCode = site.HptCode
                    WHERE DocNo Like CONCAT('DT',lpad('$siteCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
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
        // mysqli_query($conn, $Sql);

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
        // mysqli_query($conn, $Sql2);

        $return['user'] = $Userid;
        $return['siteCode'] = $siteCode;
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

function load_Fac($conn, $DATA)
{
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


if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_dep') {
        load_dep($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_site') {
        load_site($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_doc_procees') {
        load_doc_procees($conn, $DATA);
    } else if ($DATA['STATUS'] == 'receive_zero') {
        receive_zero($conn, $DATA);
    } else if ($DATA['STATUS'] == 'confirm_yes') {
        confirm_yes($conn, $DATA);
    } else if ($DATA['STATUS'] == 'add_dirty') {
        add_dirty($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_Fac') {
        load_Fac($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    } else if ($DATA['STATUS'] == 'load_doc_tracking') {
        load_doc_tracking($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
