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
  $boolean = false;
  $boolean2 = false;
  $Sql = "SELECT RefDocNo,
          DATE_FORMAT($From.Modify_Date,'%d %M %Y') AS xdate,
          DATE_FORMAT($From.Modify_Date,'%H:%i') AS xtime,
          users.FName,
          Total,
          site.HptName
          FROM $From,users,site
          WHERE DocNo ='$DocNo'
          AND users.ID = Modify_Code
          AND $From.HptCode = site.HptCode";

  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return['DocNo'] = $Result['RefDocNo'];
    $return['xdate'] = $Result['xdate'];
    $return['xtime'] = $Result['xtime'];
    $return['FName']  = $Result['FName'];
    $return['Total']  = $Result['Total'];
    $return['HptName']  = $Result['HptName'];
    $boolean = true;
  }
  $return['boolean'] = $boolean;
  $return['Sql1'] = $Sql;

  $Sql2 = "SELECT " . $From . "_detail.ItemCode,
                        item.ItemName,
                        " . $From . "_detail.UnitCode,
                        (
                            SELECT SUM(" . $From . "_detail.Qty)
                            FROM
                            " . $From . "_detail
                            WHERE
                                DocNo = '$DocNo'
                            AND item.ItemCode = " . $From . "_detail.ItemCode
                            GROUP BY
                                item.ItemCode
                        ) AS Qty,
                        (
                            SELECT SUM(" . $From . "_detail.Weight)
                            FROM
                            " . $From . "_detail
                            WHERE
                                DocNo = '$DocNo'
                            AND item.ItemCode = " . $From . "_detail.ItemCode
                            GROUP BY
                                item.ItemCode
                        ) AS Weight

                FROM " . $From . "_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND item.ItemCode = " . $From . "_detail.ItemCode 
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

  $Sql = "SELECT department.DepName,dirty_detail.Qty,dirty_detail.Weight 
          FROM dirty_detail 
          INNER JOIN department ON department.DepCode = dirty_detail.DepCode 
          WHERE dirty_detail.DocNo = '$DocNo' 
          AND dirty_detail.ItemCode = '$ItemCode'
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

  if ($DATA['STATUS'] == 'load_site') {
    load_site($conn, $DATA);
  } else if ($DATA['STATUS'] == 'load_doc') {
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
