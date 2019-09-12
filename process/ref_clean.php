<?php
session_start();
require '../connect/connect.php';
require 'logout.php';

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
    $From = $DATA["From"];
    $siteCode = $DATA["siteCode"];
    $return['siteCode'] = $siteCode;
    $boolean = false;
    $Sql = "SELECT DISTINCT
                    $From.DocNo,
                    $From.IsStatus,
                    $From.IsCheckList,
                    department.DepName,
                    site.HptCode,
                    site.HptName
                FROM
                $From
                INNER JOIN department ON department.DepCode = $From.DepCode AND department.DepCode = $From.DepCode
                INNER JOIN site ON site.HptCode = department.HptCode AND site.HptCode = department.HptCode
                INNER JOIN qccheckpass ON qccheckpass.DocNo = $From.DocNo 
                WHERE site.HptCode = '$siteCode' 
                AND $From.DocDate LIKE '%$search%'
                AND $From.IsStatus = 3 
                AND $From.IsStatus != 9 
                AND qccheckpass.Lost > 0
                ORDER BY $From.IsStatus ASC,$From.DocNo DESC";
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

function add_clean($conn, $DATA)
{
    $Userid = $DATA["Userid"];
    $siteCode = $DATA["siteCode"];
    $DepCode = $DATA["DepCode"];
    $RefDocNo = $DATA["refDocNo"];
    $return['RefDocNo'] = $RefDocNo;

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
        $Sql = "INSERT INTO log ( log ) VALUES ('" . $Result['DocDate'] . " : " . $Result['DocNo'] . " :: '$siteCode' :: $DepCode')";
        mysqli_query($conn, $Sql);
    }
    
    if ($count == 1) {

        $Sql = "    INSERT INTO     clean
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
                                            clean.Modify_Code,
                                            clean.Modify_Date
                                        )
                        VALUES
                                        ( 
                                            '$DocNo',
                                            DATE(NOW()),
                                            '$DepCode',
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
            $Sql2 = "SELECT ItemCode,Lost FROM qccheckpass WHERE DocNo = '$RefDocNo' AND Lost > 0";
            $meQuery = mysqli_query($conn, $Sql2);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $return[$cnt]['ItemCode'] = $Result['ItemCode'];
                $return[$cnt]['Lost'] = $Result['Lost'];
                $ItemCode = $Result['ItemCode'];
                $Lost = $Result['Lost'];
                $Sql2 = "INSERT INTO clean_detail ( DocNo,ItemCode,UnitCode,Qty,Weight,IsCancel,IsCheckList ) VALUES ('$DocNo','$ItemCode',1,$Lost,0,0,0)";
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
                                $DepCode,
                                '$RefDocNo',
                                'Clean',
                                $Userid,
                                DATE(NOW())
                            )";
        mysqli_query($conn, $Sql3);
                
        $return['user'] = $Userid;
        $return['siteCode'] = $siteCode;
        $return['DepCode'] = $DepCode;
        $return['DocNo'] = $DocNo;

        $return['status'] = "success";
        $return['form'] = "add_clean";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "add_clean";
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
    } else if ($DATA['STATUS'] == 'add_clean') {
        add_clean($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
