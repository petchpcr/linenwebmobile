<?php
session_start();
require '../connect/connect.php';
require 'logout.php';
date_default_timezone_set("Asia/Bangkok");

function load_question($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $count = 0;
    $boolean = false;

    $Sql = "SELECT IsStatus FROM kpi_clean2 WHERE DocNo = '$DocNo'";
    $meQuery = mysqli_query($conn, $Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $return['IsStatus'] = $Result['IsStatus'];

    $Sql = "SELECT ID,Type,Question FROM kpi_clean2_question";

    $return['Sql'] = $Sql;
    $meQuery = mysqli_query($conn, $Sql);
    while ($Result = mysqli_fetch_assoc($meQuery)) {
        $ID = $Result['ID'];
        $return['ID'][$count] = $ID;
        $return['Type'][$count] = $Result['Type'];
        $return['Question'][$count] = $Result['Question'];

        $SqlD = "SELECT IsCheck FROM kpi_clean2_detail WHERE DocNo = '$DocNo' AND Question_ID = '$ID'";
        $meQueryD = mysqli_query($conn, $SqlD);
        $ResultD = mysqli_fetch_assoc($meQueryD);
        $return['IsCheck'][$count] = $ResultD['IsCheck'];

        $count++;
        $boolean = true;
    }
    $return['cnt'] = $count;

    if ($boolean) {
        $return['status'] = "success";
    } else {
        $return['status'] = "failed";
    }

    $return['form'] = "load_question";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}

function save_checklist($conn, $DATA)
{
    $DocNo = $DATA["DocNo"];
    $RadioName = $DATA["RadioName"];
    $ChkResult = $DATA["ChkResult"];
    $count = 0;
    $have_null = 0;
    $return['RadioName'] = $RadioName;
    $return['ChkResult'] = $ChkResult;

    foreach($RadioName as $key => $question_id) {

        $Sql = "SELECT COUNT(*) AS cnt FROM kpi_clean2_detail WHERE DocNo = '$DocNo' AND Question_ID = $question_id";
        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $cnt = $Result['cnt'];
        $return['cnt'][$count] = $cnt;
        $return['isCheck'][$count] = $ChkResult[$key];


        if ($cnt == 0) { // ไม่ได้สร้างไว้
            $Sql = "INSERT INTO kpi_clean2_detail(DocNo,Question_ID,IsCheck) VALUES('$DocNo',$question_id,$ChkResult[$key])";

        } else { // สร้างไว้แล้ว
            $Sql = "UPDATE kpi_clean2_detail SET IsCheck = $ChkResult[$key] WHERE DocNo = '$DocNo' AND Question_ID = $question_id";
        }

        $return['Sql'][$count] = $Sql;
        if ($ChkResult[$key] != null) {
            mysqli_query($conn, $Sql);
        } else {
            $have_null = 1;
        }
        $count++;
    }

    if ($have_null == 0) {
        $Sql = "UPDATE kpi_clean2 SET IsStatus = 1 WHERE DocNo = '$DocNo'";
        mysqli_query($conn, $Sql);
    }

    if ($count > 0) {
        $return['status'] = "success";
        $return['form'] = "save_checklist";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    } else {
        $return['status'] = "failed";
        $return['form'] = "save_checklist";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
}

if (isset($_POST['DATA'])) {
    $data = $_POST['DATA'];
    $DATA = json_decode(str_replace('\"', '"', $data), true);

    if ($DATA['STATUS'] == 'load_question') {
        load_question($conn, $DATA);
    } else if ($DATA['STATUS'] == 'save_checklist') {
        save_checklist($conn, $DATA);
    } else if ($DATA['STATUS'] == 'logout') {
        logout($conn, $DATA);
    }
} else {
    $return['status'] = "error";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
