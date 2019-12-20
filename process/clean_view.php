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

  if ($DATA["Menu"] == 'clean') {
    $Menu = "cleanstock";
  } else if ($DATA["Menu"] == 'clean_real') {
    $Menu = "clean";
  }
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
  $return['Sql1'] = $Sql;

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['HptName'] = $Result['HptName'];
    $boolean = true;
  }

  $s = "1";
  $Sql = "SELECT RefDocNo,$Menu.IsStatus,
                  DATE_FORMAT($Menu.Modify_Date,'%d %M %Y') AS xdate,
                  DATE_FORMAT($Menu.Modify_Date,'%H:%i') AS xtime,
                  users.$TName AS TName,
                  users.$FName AS FName,
                  users.$LName AS LName,
                  Total,
                  department.DepCode,
                  department.DepName
          FROM $Menu, users, site, department
          WHERE DocNo ='$DocNo'
          AND users.ID = $Menu.Modify_Code
          AND $Menu.DepCode = department.DepCode
          AND users.HptCode = site.HptCode";
  $return['Sql2'] = $Sql;

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

  if ($DATA["Menu"] == 'clean_real') {
    $RefDocNo_clean = array();
    $Sql = "SELECT RefDocNo FROM clean_ref WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
      array_push($RefDocNo_clean,$Result['RefDocNo']);
    }
    $return['RefDocNo'] = $RefDocNo_clean;
  }

  $s = "2";
  if ($DATA["Menu"] == 'clean') {
    $Sql2 = "SELECT cleanstock_detail.ItemCode,
                  item.ItemName,
                  cleanstock_detail.UnitCode,
                  cleanstock_detail.Qty,
                  cleanstock_detail.Weight 
              FROM cleanstock_detail,item 
              WHERE DocNo = '$DocNo'
              AND	  item.ItemCode = cleanstock_detail.ItemCode";

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
  } else if ($DATA["Menu"] == 'clean_real') {
    $ar_item = array();
    $ar_request = array();

    $Sql = "SELECT ItemCode,RequestName
            FROM clean_detail 
            WHERE DocNo = '$DocNo'
            GROUP BY ItemCode,RequestName";

    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
      array_push($ar_item, $Result['ItemCode']);
      array_push($ar_request, $Result['RequestName']);
    }
    $return['ar_item'] = $ar_item;
    $return['ar_request'] = $ar_request;

    foreach ($ar_item as $key => $val) {
      if ($val == 'HDL') {
        $ItemName = $ar_request[$key];
        $Sql = "SELECT SUM(Qty) AS Qty,SUM(Weight) AS Weight FROM clean_detail WHERE DocNo = '$DocNo' AND RequestName = '$ar_request[$key]'";
      } else {
        $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$val'";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $ItemName = $Result['ItemName'];
        $Sql = "SELECT SUM(Qty) AS Qty,SUM(Weight) AS Weight FROM clean_detail WHERE DocNo = '$DocNo' AND ItemCode = '$val'";
      }
      $meQuery = mysqli_query($conn, $Sql);
      $Result = mysqli_fetch_assoc($meQuery);
      $return[$count]['ItemCode'] = $val;
      $return[$count]['RequestName'] = $ar_request[$key];
      $return[$count]['ItemName'] = $ItemName;
      $return[$count]['Qty'] = $Result['Qty'];
      $return[$count]['Weight'] = $Result['Weight'];
      $count++;
    }
  }


  $s = "3";
  $return['cnt'] = $count;
  $return['Menu'] = $Menu;

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
  if ($DATA["Menu"] == 'clean') {
    $Menu = "cleanstock";
  } else if ($DATA["Menu"] == 'clean_real') {
    $Menu = "clean";
  }

  $Sql = "SELECT RefDocNo FROM $Menu WHERE DocNo = '$DocNo'";
  $meQuery = mysqli_query($conn, $Sql);
  $Result = mysqli_fetch_assoc($meQuery);
  $RefDocNo = $Result['RefDocNo'];

  $Sql = "UPDATE dirty SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE newlinentable SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE repair_wash SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "UPDATE $Menu SET IsStatus = 3 WHERE DocNo = '$RefDocNo'";
  mysqli_query($conn, $Sql);

  $Sql = "UPDATE $Menu SET IsStatus = 9 WHERE DocNo = '$DocNo'";

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
