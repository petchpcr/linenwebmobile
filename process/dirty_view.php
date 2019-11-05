<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_site($conn, $DATA)
{
  $siteCode = $DATA["siteCode"];
  $From = $DATA["From"];
  $DocNo = $DATA["DocNo"];
  $Sql = "SELECT site.HptName FROM site WHERE site.HptCode = '$siteCode'";

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
  $DocNo = $DATA["DocNo"];
  $From = $DATA["From"];
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
  $Sql = "SELECT RefDocNo,$From.IsStatus,
          DATE_FORMAT($From.Modify_Date,'%d %M %Y') AS xdate,
          DATE_FORMAT($From.Modify_Date,'%H:%i') AS xtime,
          users.$TName AS TName,
          users.$FName AS FName,
          users.$LName AS LName,
          Total,
          site.HptName
          FROM $From,users,site
          WHERE DocNo ='$DocNo'
          AND users.ID = $From.Modify_Code
          AND $From.HptCode = site.HptCode";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['DocNo'] = $Result['RefDocNo'];
    $return['IsStatus'] = $Result['IsStatus'];
    $return['xdate'] = $Result['xdate'];
    $return['xtime'] = $Result['xtime'];
    $return['FName']  = $Result['TName'] . $Result['FName'] . " " . $Result['LName'];
    $return['Total']  = $Result['Total'];
    $return['HptName']  = $Result['HptName'];
    $boolean = true;
  }
  $return['boolean'] = $boolean;
  $return['Sql1'] = $Sql;

  $Sql = "SELECT ItemCode,RequestName
            FROM dirty_detail 
            WHERE DocNo = '$DocNo'
            GROUP BY ItemCode,RequestName";
  
  $return['Sql2'] = $Sql;
  $ar_item = array();
  $ar_request = array();
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    array_push($ar_item,$Result['ItemCode']);
    array_push($ar_request,$Result['RequestName']);
  }
  $return['ar_item'] = $ar_item;
  $return['ar_request'] = $ar_request;

  foreach ($ar_item as $key => $val) {
    if ($val == 'HDL') {
      $ItemName = $ar_request[$key];
      $Sql = "SELECT SUM(Qty) AS Qty,SUM(Weight) AS Weight FROM dirty_detail WHERE DocNo = '$DocNo' AND RequestName = '$ar_request[$key]'";
    } else {
      $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$val'";
      $meQuery = mysqli_query($conn, $Sql);
      $Result = mysqli_fetch_assoc($meQuery);
      $ItemName = $Result['ItemName'];
      $Sql = "SELECT SUM(Qty) AS Qty,SUM(Weight) AS Weight FROM dirty_detail WHERE DocNo = '$DocNo' AND ItemCode = '$val'";
    }
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return[$count]['ItemCode'] = $val;
    $return[$count]['RequestName'] = $ar_request[$key];
    $return[$count]['ItemName'] = $ItemName;
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
  $RequestName = $DATA["Request"];
  $count = 0;

  if ($ItemCode == 'HDL') {
    $return['ItemName'] = $RequestName;
    $Sql = "SELECT department.DepName,dirty_detail.Qty,dirty_detail.Weight 
            FROM dirty_detail 
            INNER JOIN department ON department.DepCode = dirty_detail.DepCode 
            WHERE dirty_detail.DocNo = '$DocNo' 
            AND dirty_detail.RequestName = '$RequestName'
            ORDER BY department.DepName ASC";
    
  } else {
    $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$ItemCode'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['ItemName'] = $Result['ItemName'];

    $Sql = "SELECT department.DepName,dirty_detail.Qty,dirty_detail.Weight 
            FROM dirty_detail 
            INNER JOIN department ON department.DepCode = dirty_detail.DepCode 
            WHERE dirty_detail.DocNo = '$DocNo' 
            AND dirty_detail.ItemCode = '$ItemCode'
            ORDER BY department.DepName ASC";
  }

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

function CancelDoc($conn, $DATA)
{
  $DocNo = $DATA["DocNo"];

  $Sql = "UPDATE dirty SET IsStatus = 9 WHERE DocNo = '$DocNo'";

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

  if ($DATA['STATUS'] == 'load_site') {
    load_site($conn, $DATA);
  } else if ($DATA['STATUS'] == 'load_doc') {
    load_doc($conn, $DATA);
  } else if ($DATA['STATUS'] == 'view_dep') {
    view_dep($conn, $DATA);
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
