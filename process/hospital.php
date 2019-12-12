<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_site($conn, $DATA)
{
  $form_out = $DATA['form_out'];
  $FacCode = $_SESSION['FacCode'];
  if ($_SESSION['lang'] == 'th') {
    $hptname = "HptNameTH";
  } else if ($_SESSION['lang'] == 'en') {
    $hptname = "HptName";
  }
  $count = 0;
  if ($form_out == 1) {
    $Sql = "SELECT HptCode,$hptname AS Hname FROM site WHERE IsStatus = 0";
  } else {
    $Sql = "SELECT * 
            FROM    ( SELECT site.HptCode,site.$hptname AS Hname 
                      FROM dirty 
                      INNER JOIN site on site.HptCode = dirty.HptCode
                      WHERE dirty.FacCode = $FacCode
                      AND site.IsStatus = 0
                      
                      UNION ALL
                      
                      SELECT site.HptCode,site.$hptname AS Hname 
                      FROM newlinentable 
                      INNER JOIN site on site.HptCode = newlinentable.HptCode
                      WHERE newlinentable.FacCode = $FacCode
                      AND site.IsStatus = 0
                      
                      -- UNION ALL
                      
                      -- SELECT site.HptCode,site.$hptname AS Hname 
                      -- FROM rewash 
                      -- INNER JOIN department on department.DepCode = rewash.DepCode
                      -- INNER JOIN site on site.HptCode = department.HptCode
                      -- WHERE rewash.FacCode = $FacCode
                      -- AND site.IsStatus = 0
                      ) h
            GROUP BY HptCode
            ORDER BY Hname ASC";
  }
  $return['Sql'] = $Sql;
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $return[$count]['HptCode'] = $Result['HptCode'];
    $return[$count]['HptName'] = $Result['Hname'];
    // $return[$count]['picture'] = $Result['picture'];
    $count++;
  }

  $return['count'] = $count;
  if ($count > 0) {
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

function show_doc($conn, $DATA)
{
  $count = 0;
  $siteCode = $DATA["SiteCode"];
  $Sql = "SELECT COUNT(dirty.DocNo) AS cnt 
                FROM dirty 
                INNER JOIN department ON dirty.DepCode = department.DepCode 
                WHERE department.HptCode = '$siteCode'";
  $meQuery = mysqli_query($conn, $Sql);
  while ($Result = mysqli_fetch_assoc($meQuery)) {
    $cntDocNo = $Result['cnt'];
    $count++;
  }
  if ($cntDocNo >= 1) {
    $return['siteCode'] = $siteCode;
    $return['status'] = "success";
    $return['form'] = "show_doc";
    echo json_encode($return);
    mysqli_close($conn);
    die;
  } else {
    $return['status'] = "failed";
    $return['form'] = "show_doc";
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
  } else if ($DATA['STATUS'] == 'show_doc') {
    show_doc($conn, $DATA);
  } else if ($DATA['STATUS'] == 'logout') {
    logout($conn, $DATA);
  }
} else {
  $return['status'] = "error";
  echo json_encode($return);
  mysqli_close($conn);
  die;
}
