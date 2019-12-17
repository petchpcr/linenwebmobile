<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function choose_items($conn, $DATA)
{
  $Search = $DATA["Search"];
  $siteCode = $DATA["siteCode"];
  $first = $DATA["first"];
  $DocNo = $DATA["DocNo"];
  $Ar_ItemCode = array();
  $count = 0;

  $Sql = "SELECT n.ItemCode 
          FROM newlinentable_detail n 
          INNER JOIN item i ON n.ItemCode = i.ItemCode 
          WHERE n.DocNo = '$DocNo' 
          GROUP BY n.ItemCode 
          ORDER BY i.ItemName";
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    array_push($Ar_ItemCode,$Result['ItemCode']);
  }

  $Sql = "SELECT DISTINCT ItemCode,ItemName  
                FROM    item 
                WHERE   IsActive = 1 
                -- AND     Itemnew = 1 
                -- AND     (HptCode = '$siteCode' OR HptCode = '0')
                AND     HptCode = '$siteCode'
                AND     ItemName LIKE '%$Search%'
                ORDER BY ItemName ASC";
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return[$count]['ItemCode'] = $Result['ItemCode'];
    $return[$count]['ItemName'] = $Result['ItemName'];
    $return[$count]['UnitCode'] = $Result['UnitCode'];
    foreach($Ar_ItemCode as $key => $ItemCode) {
      if ($ItemCode == $Result['ItemCode']) {
        $return[$count]['check'] = 1;
      }
    }
    $count++;
  }
  $return['cnt'] = $count;
  $return['Sql'] = $Sql;
  $return['first'] = $first;

  if ($count > 0) {
    $return['status'] = "success";
    $return['form'] = "choose_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "choose_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
}

function load_items($conn, $DATA)
{
  $count = 0;
  $DocNo = $DATA["DocNo"];
  $Ar_ItemCode = array();
  $Ar_ItemName = array();
  $Ar_Qty = array();
  $Ar_Weight = array();
  $Total_Qty = 0;
  $Total_Weight = number_format(0,2);
  $Sql = "SELECT n.ItemCode 
          FROM newlinentable_detail n 
          INNER JOIN item i ON n.ItemCode = i.ItemCode 
          WHERE n.DocNo = '$DocNo' 
          GROUP BY n.ItemCode 
          ORDER BY i.ItemName";
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    array_push($Ar_ItemCode,$Result['ItemCode']);
    $count++;
  }

  foreach($Ar_ItemCode as $key => $ItemCode){
    $Sql = "SELECT n.ItemCode,
                  i.ItemName,
                  n.UnitCode,
                  SUM(n.Qty) AS Qty,
									SUM(n.Weight) AS Weight,
                  n.DepCode  
          FROM newlinentable_detail n,
                item i 
          WHERE DocNo = '$DocNo'
          AND	  n.ItemCode = '$ItemCode' 
          AND	  i.ItemCode = n.ItemCode 
          GROUP BY n.ItemCode 
          ORDER BY ItemName ASC";
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
      array_push($Ar_ItemName,$Result['ItemName']);
      array_push($Ar_Qty,$Result['Qty']);
      array_push($Ar_Weight,$Result['Weight']);
      $Total_Qty += $Result['Qty'];
      $Total_Weight += number_format($Result['Weight'],2);
    }
  }
  
  $return['cnt'] = $count;
  $return['ItemCode'] = $Ar_ItemCode;
  $return['ItemName'] = $Ar_ItemName;
  $return['Qty'] = $Ar_Qty;
  $return['Weight'] = $Ar_Weight;
  $return['Total_Qty'] = $Total_Qty;
  $return['Total_Weight'] = number_format($Total_Weight,2);

  if ($count > 0) {
    $return['status'] = "success";
    $return['form'] = "load_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "load_items";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
}

function load_dep($conn, $DATA)
{
  $siteCode = $DATA['siteCode'];
  $count = 0;

  $Sql = "SELECT DepCode,DepName 
                FROM department 
                WHERE HptCode = '$siteCode' 
                AND IsDefault = 1 
                ORDER BY DepName ASC";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return[$count]['DepCode'] = $Result['DepCode'];
    $return[$count]['DepName'] = $Result['DepName'];
    $count++;
  }
  $return['count'] = $count;
  $return['Sql'] = $Sql;
  if ($count > 0) {
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

function select_item($conn, $DATA)
{
  $DocNo = $DATA["DocNo"];
  $item = $DATA["item"];
  $count = 0;

  $Sql = "SELECT Qty,Weight,DepCode  
                FROM    newlinentable_detail 
                WHERE   DocNo = '$DocNo'  
                AND     ItemCode = '$item'";
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return[$count]['Qty']    =  $Result['Qty'];
    $return[$count]['Weight']    =  $Result['Weight'];
    $return[$count]['DepCode']    =  $Result['DepCode'];
    $count++;
  }
  $return['cnt'] = $count;
  $return['Sql'] = $Sql;

  if ($count > 0) {
    $return['status'] = "success";
    $return['form'] = "select_item";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "select_item";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  }
}

function edit_value($conn, $DATA)
{
  $DocNo = $DATA['DocNo'];
  $now_item = $DATA['now_item'];
  $ar_qty = $DATA['ar_qty'];
  $ar_weight = $DATA['ar_weight'];
  $ar_depcode = $DATA['ar_depcode'];

  $Sql = "DELETE FROM newlinentable_detail WHERE DocNo = '$DocNo' AND ItemCode = '$now_item'";
  mysqli_query($conn, $Sql);

  foreach ($ar_qty as $key => $qty) {
    $Sql = "INSERT INTO newlinentable_detail(`DocNo`,`ItemCode`,`DepCode`,`UnitCode`,`Weight`,`Qty`) 
                            VALUES ('$DocNo','$now_item','$ar_depcode[$key]',1,$ar_weight[$key],$qty) ";
    mysqli_query($conn, $Sql);
  }

  $return['status'] = "success";
  $return['form'] = "edit_value";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}

function del_items($conn, $DATA)
{
  $DocNo = $DATA['DocNo'];
  $item = $DATA['item'];

  $Sql = "DELETE FROM newlinentable_detail WHERE DocNo = '$DocNo' AND ItemCode = '$item'";
  mysqli_query($conn, $Sql);

  $return['status'] = "success";
  $return['form'] = "edit_value";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}

function add_item($conn, $DATA)
{
  $DocNo = $DATA['DocNo'];
  $Userid = $_SESSION['Userid'];

  $Sql = "SELECT SUM(Weight) AS total FROM newlinentable_detail WHERE DocNo = '$DocNo'";
  $meQuery = mysqli_query($conn, $Sql);
  $Result = mysqli_fetch_assoc($meQuery);
  $total = $Result['total'];

  $Sql = "UPDATE newlinentable SET Total = $total, Modify_Code = '$Userid', Modify_Date = NOW(), IsStatus = 1 WHERE DocNo = '$DocNo'";
  $return['Last Update'] = $Sql;
  mysqli_query($conn, $Sql);

  $return['status'] = "success";
  $return['form'] = "add_item";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}

function del_back($conn, $DATA)
{
  $DocNo = $DATA["DocNo"];
  $Menu = $DATA["Menu"];
  $return['Menu'] = $Menu;
  $Sql = "DELETE FROM $Menu WHERE DocNo = '$DocNo'";
  mysqli_query($conn, $Sql);
  $Sql = "DELETE FROM " . $Menu . "_detail WHERE DocNo = '$DocNo'";
  mysqli_query($conn, $Sql);

  $return['status'] = "success";
  $return['form'] = "del_back";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}

if (isset($_POST['DATA'])) {
  $data = $_POST['DATA'];
  $DATA = json_decode(str_replace('\"', '"', $data), true);

  if ($DATA['STATUS'] == 'load_items') {
    load_items($conn, $DATA);
  } else if ($DATA['STATUS'] == 'load_dep') {
    load_dep($conn, $DATA);
  } else if ($DATA['STATUS'] == 'choose_items') {
    choose_items($conn, $DATA);
  } else if ($DATA['STATUS'] == 'select_item') {
    select_item($conn, $DATA);
  } else if ($DATA['STATUS'] == 'edit_value') {
    edit_value($conn, $DATA);
  } else if ($DATA['STATUS'] == 'del_items') {
    del_items($conn, $DATA);
  } else if ($DATA['STATUS'] == 'add_item') {
    add_item($conn, $DATA);
  } else if ($DATA['STATUS'] == 'del_back') {
    del_back($conn, $DATA);
  } else if ($DATA['STATUS'] == 'logout') {
    logout($conn, $DATA);
  }
} else {
  $return['status'] = "error";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}
