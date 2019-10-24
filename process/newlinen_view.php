<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_doc($conn, $DATA)
{
    $count = 0;
    $DocNo = $DATA["DocNo"];
    if ($_SESSION['lang'] == 'en') {
        $TName = 'EngPerfix';
        $FName = 'EngName';
        $LName = 'EngLName';
    } else {
        $TName = 'ThPerfix';
        $FName = 'ThName';
        $LName = 'ThLName';
    }
    $boolean = false;
    $boolean2 = false;
    $Sql = "SELECT RefDocNo,
                        DATE_FORMAT(newlinentable.Modify_Date,'%d %M %Y') AS xdate,
                        DATE_FORMAT(newlinentable.Modify_Date,'%H:%i') AS xtime,
                        users.$TName AS TName,
                        users.$FName AS FName,
                        users.$LName AS LName,
                        Total,
                        site.HptName
                FROM newlinentable,users,site
                WHERE DocNo ='$DocNo'
                AND users.ID = Modify_Code
                AND newlinentable.HptCode = site.HptCode";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['DocNo'] = $Result['RefDocNo'];
        $return['xdate'] = $Result['xdate'];
        $return['xtime'] = $Result['xtime'];
        $return['FName']  = $Result['TName'].$Result['FName']." ".$Result['LName'];
        $return['Total']  = $Result['Total'];
        $return['HptName']  = $Result['HptName'];
        $boolean = true;
    }
    $return['boolean'] = $boolean;
    $return['Sql1'] = $Sql;

    $Sql2 = "SELECT newlinentable_detail.ItemCode,
                        item.ItemName,
                        newlinentable_detail.UnitCode,
                        (
                            SELECT SUM(newlinentable_detail.Qty)
                            FROM
                            newlinentable_detail
                            WHERE
                                DocNo = '$DocNo'
                            AND item.ItemCode = newlinentable_detail.ItemCode
                            GROUP BY
                                item.ItemCode
                        ) AS Qty,
                        (
                            SELECT SUM(newlinentable_detail.Weight)
                            FROM
                            newlinentable_detail
                            WHERE
                                DocNo = '$DocNo'
                            AND item.ItemCode = newlinentable_detail.ItemCode
                            GROUP BY
                                item.ItemCode
                        ) AS Weight

                FROM newlinentable_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND item.ItemCode = newlinentable_detail.ItemCode 
                GROUP BY item.ItemCode
                ORDER BY item.ItemName";
    $return['Sql2'] = $Sql2;
    $meQuery2 = mysqli_query($conn, $Sql2);
    while ($Result = mysqli_fetch_assoc($meQuery2)) {
        $return[$count]['ItemCode'] = $Result['ItemCode'];
        $return[$count]['ItemName'] = $Result['ItemName'];
        $return[$count]['UnitCode'] = $Result['UnitCode'];
        $return[$count]['Qty'] = $Result['Qty'];
        $return[$count]['Weight'] = $Result['Weight'];
        $count++;
        $boolean2 = true;
    }
    $return['cnt'] = $count;
    $return['boolean2'] = $boolean2;
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

function view_dep($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $ItemCode = $DATA["Item"];
    $count = 0;

    $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$ItemCode'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['ItemName'] = $Result['ItemName'];

    $Sql = "SELECT department.DepName,newlinentable_detail.Qty,newlinentable_detail.Weight 
          FROM newlinentable_detail 
          INNER JOIN department ON department.DepCode = newlinentable_detail.DepCode 
          WHERE newlinentable_detail.DocNo = '$DocNo' 
          AND newlinentable_detail.ItemCode = '$ItemCode'
          ORDER BY department.DepName ASC";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $return['DepName'][$count] = $Result['DepName'];
        $return['Qty'][$count] = $Result['Qty'];
        $return['Weight'][$count] = $Result['Weight'];
        $count++;
    }
    $return['cnt'] = $count;

    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "view_dep";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "view_dep";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_doc') {
        load_doc($conn, $DATA);
    } else if ($DATA['STATUS'] == 'view_dep') {
        view_dep($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
