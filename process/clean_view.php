<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_doc($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $siteCode = $DATA["siteCode"];
        $boolean = false;
        $Sql = "SELECT site.HptName FROM site WHERE site.HptCode = '$siteCode'";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['HptName'] = $Result['HptName'];
            $boolean = true;
        }
        
        $s = "1";
        $Sql = "SELECT RefDocNo,
                        DATE_FORMAT(clean.Modify_Date,'%d %M %Y') AS xdate,
                        DATE_FORMAT(clean.Modify_Date,'%H:%i') AS xtime,
                        FName,
                        Total,
                        department.DepCode,
                        department.DepName
                FROM clean, users, site, department
                WHERE DocNo ='$DocNo'
                AND users.ID = clean.Modify_Code
                AND clean.DepCode = department.DepCode
                AND users.HptCode = site.HptCode";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['RefDocNo'] = $Result['RefDocNo'];
            $return['xdate'] = $Result['xdate'];
            $return['xtime'] = $Result['xtime'];
            $return['FName']  = $Result['FName'];
            $return['Total']  = $Result['Total'];
            $return['DepCode']  = $Result['DepCode'];
            $return['DepName']  = $Result['DepName'];
            $boolean = true;
        }
        $return['boolean'] = $boolean;

        $s = "2";
        $Sql2 = "SELECT clean_detail.ItemCode,
                        item.ItemName,
                        clean_detail.UnitCode,
                        clean_detail.Qty,
                        clean_detail.Weight 
                FROM clean_detail,
                     item 
                WHERE DocNo = '$DocNo'
                AND	  item.ItemCode = clean_detail.ItemCode";

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

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_doc') {
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