<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_site($conn, $DATA){
        $siteCode = $DATA["siteCode"];
        $From = $DATA["From"];
        $DocNo = $DATA["DocNo"];
        $Sql = "SELECT site.HptName FROM site WHERE site.HptCode = '$siteCode'";
        $Sql2 = "SELECT department.DepName,department.DepCode 
                FROM department 
                INNER JOIN $From ON $From.DepCode = department.DepCode 
                WHERE $From.DocNo = '$DocNo'";
        $boolean = false;
        $boolean2 = false;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['HptName'] = $Result['HptName'];
            $boolean = true;
        }
        $meQuery2 = mysqli_query($conn, $Sql2);
        while ($Result = mysqli_fetch_assoc($meQuery2)) {
            $return['DepName'] = $Result['DepName'];
            $return['DepCode'] = $Result['DepCode'];
            $boolean2 = true;
        }
        $return['boolean'] = $boolean;
        $return['boolean2'] = $boolean2;

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

    function load_doc($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $From = $DATA["From"];
        $boolean = false;
        $boolean2 = false;
        $Sql = "SELECT RefDocNo,
                        DATE_FORMAT($From.Modify_Date,'%d %M %Y') AS xdate,
                        DATE_FORMAT($From.Modify_Date,'%H:%i') AS xtime,
                        FName,
                        Total,
                        $From.DepCode
                FROM $From, users, site
                WHERE DocNo ='$DocNo'
                AND users.ID = $From.Modify_Code
                AND users.HptCode = site.HptCode";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['DocNo'] = $Result['RefDocNo'];
            $return['xdate'] = $Result['xdate'];
            $return['xtime'] = $Result['xtime'];
            $return['FName']  = $Result['FName'];
            $return['Total']  = $Result['Total'];
            $return['DepCode']  = $Result['DepCode'];
            $boolean = true;
        }
        $return['boolean'] = $boolean;

        $Sql2 = "SELECT ".$From."_detail.ItemCode,
                        item.ItemName,
                        ".$From."_detail.UnitCode,
                        ".$From."_detail.Qty,
                        ".$From."_detail.Weight 
                FROM ".$From."_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND	  item.ItemCode = ".$From."_detail.ItemCode";
        $return['Sql'] = $Sql2;
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

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_site') {
            load_site($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'load_doc') {
            load_doc($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'logout') {
            logout($conn, $DATA);
        }
    }else {
        $return['status'] = "error";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
?>