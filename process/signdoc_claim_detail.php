<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_doc($conn, $DATA)
{
  $count = 0;
  $DocNo = $DATA["DocNo"];
  if ($_SESSION['lang'] == "th") {
    $FacName = "FacNameTH";
    $HptName = "HptNameTH";
    $TName = 'ThPerfix';
    $FName = 'ThName';
    $LName = 'ThLName';
  } else {
    $FacName = "FacName";
    $HptName = "HptName";
    $TName = 'EngPerfix';
    $FName = 'EngName';
    $LName = 'EngLName';
  }
  $boolean = false;
  $boolean2 = false;
  $Sql = "SELECT RefDocNo,damage.IsStatus,
          DATE_FORMAT(damage.Modify_Date,'%d %M %Y') AS xdate,
          DATE_FORMAT(damage.Modify_Date,'%H:%i') AS xtime,
          Total,
          SignFac,
          SignNH,
          users.$TName AS TName,
          users.$FName AS FName,
          users.$LName AS LName,
          site.$HptName AS HptName,
          department.DepName

          FROM damage
          INNER JOIN users ON users.ID = damage.Modify_Code
          INNER JOIN department ON department.DepCode = damage.DepCode 
          INNER JOIN site ON site.HptCode = department.HptCode
          WHERE DocNo ='$DocNo'";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['DocNo'] = $Result['RefDocNo'];
    $return['IsStatus'] = $Result['IsStatus'];
    $return['Total']  = $Result['Total'];
    $return['SignFac']  = $Result['SignFac'];
    $return['SignNH']  = $Result['SignNH'];
    $return['xdate'] = $Result['xdate'];
    $return['xtime'] = $Result['xtime'];
    $return['FName']  = $Result['TName'] . $Result['FName'] . " " . $Result['LName'];
    $return['HptName']  = $Result['HptName'];
    $return['DepName']  = $Result['DepName'];
    $boolean = true;
  }
  $return['boolean'] = $boolean;
  $return['Sql1'] = $Sql;

  $Sql = "SELECT ItemCode
            FROM damage_detail 
            WHERE DocNo = '$DocNo'
            GROUP BY ItemCode";
  
  $return['Sql2'] = $Sql;
  $ar_item = array();
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    array_push($ar_item,$Result['ItemCode']);
  }
  $return['ar_item'] = $ar_item;

  foreach ($ar_item as $key => $val) {
    $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$val'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $ItemName = $Result['ItemName'];
    $Sql = "SELECT SUM(Qty) AS Qty,SUM(Weight) AS Weight FROM damage_detail WHERE DocNo = '$DocNo' AND ItemCode = '$val'";

    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return[$count]['ItemCode'] = $val;
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

function save_signature($conn, $DATA)
{
  $DocNo = $DATA["DocNo"];

  $Sql = "UPDATE damage SET IsStatus = 9 WHERE DocNo = '$DocNo'";

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
  } else if ($DATA['STATUS'] == 'save_signature') {
    save_signature($conn, $DATA);
  } else if ($DATA['STATUS'] == 'logout') {
    logout($conn, $DATA);
  }
} else {
  $return['status'] = "error";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}
