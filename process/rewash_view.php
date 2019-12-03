<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_doc($conn, $DATA)
{
  $count = 0;
  $DocNo = $DATA["DocNo"];
  $siteCode = $DATA["siteCode"];

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
  $Sql = "SELECT site.HptName FROM site WHERE site.HptCode = '$siteCode'";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['HptName'] = $Result['HptName'];
    $boolean = true;
  }

  $s = "1";
  $Sql = "SELECT RefDocNo,rewash.IsStatus,
                  DATE_FORMAT(rewash.Modify_Date,'%d %M %Y') AS xdate,
                  DATE_FORMAT(rewash.Modify_Date,'%H:%i') AS xtime,
                  users.$TName AS TName,
                  users.$FName AS FName,
                  users.$LName AS LName,
                  Total,
                  department.DepCode,
                  department.DepName
          FROM rewash, users, site, department
          WHERE DocNo ='$DocNo'
          AND users.ID = rewash.Modify_Code
          AND rewash.DepCode = department.DepCode
          AND users.HptCode = site.HptCode";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['RefDocNo'] = $Result['RefDocNo'];
    $return['IsStatus'] = $Result['IsStatus'];
    $return['xdate'] = $Result['xdate'];
    $return['xtime'] = $Result['xtime'];
    $return['FName']  = $Result['TName'] . $Result['FName'] . " " . $Result['LName'];
    $return['Total']  = $Result['Total'];
    $return['DepCode']  = $Result['DepCode'];
    $return['DepName']  = $Result['DepName'];
    $boolean = true;
  }
  $return['boolean'] = $boolean;

  $s = "2";
  $Sql2 = "SELECT rewash_detail.ItemCode,
                  item.ItemName,
                  rewash_detail.UnitCode,
                  rewash_detail.Qty,
                  rewash_detail.Weight 
              FROM rewash_detail,item 
              WHERE DocNo = '$DocNo'
              AND	  item.ItemCode = rewash_detail.ItemCode";

  $meQuery2 = mysqli_query($conn, $Sql2);
  while ($Result = mysqli_fetch_assoc($meQuery2)) {
    $return[$count]['ItemCode'] = $Result['ItemCode'];
    $return[$count]['ItemName'] = $Result['ItemName'];
    $return[$count]['UnitCode'] = $Result['UnitCode'];
    $return[$count]['Qty'] = $Result['Qty'];
    $return[$count]['Weight'] = $Result['Weight'];
    $count++;
    $s = "true";
  }

  $s = "3";
  $return['cnt'] = $count;

  if ($boolean) {
    $return['status'] = "success";
    $return['form'] = "load_doc";
    $return['msg'] =  $s;
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "load_doc";
    $return['msg'] =  $s;
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
}

function CancelDoc($conn, $DATA)
{
  $DocNo = $DATA["DocNo"];

  $Sql = "SELECT RefDocNo FROM rewash WHERE DocNo = '$DocNo'";
  $meQuery = mysqli_query($conn, $Sql);
  $Result = mysqli_fetch_assoc($meQuery);
  $RefDocNo = $Result['RefDocNo'];

  $Sql = "UPDATE dirty SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE newlinentable SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE repair_wash SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE rewash SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);

  $Sql = "UPDATE rewash SET IsStatus = 9 WHERE DocNo = '$DocNo'";

  if (mysqli_query($conn, $Sql)) {
    $return['status'] = "success";
    $return['form'] = "CancelDoc";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "CancelDoc";
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
  } else if ($DATA['STATUS'] == 'CancelDoc') {
    CancelDoc($conn, $DATA);
  } else if ($DATA['STATUS'] == 'logout') {
    logout($conn, $DATA);
  }
} else {
  $return['status'] = "error";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}
