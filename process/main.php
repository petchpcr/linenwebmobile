<?php
    session_start();
    require '../connect/connect.php';

    function load_site($conn, $DATA){
        $count = 0;
        $boolean = false;
        $Sql = "SELECT site.HptCode,site.HptName,site.picture FROM site WHERE site.IsStatus = 0 ORDER BY HptName ASC";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['HptCode'] = $Result['HptCode'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['picture'] = $Result['picture'];
            $count++;
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

    function show_doc($conn, $DATA){
        $count = 0;
        $siteCode = $DATA["SiteCode"];
        $boolean = false;
        $Sql = "SELECT COUNT(dirty.DocNo) AS cnt 
                FROM dirty 
                INNER JOIN department ON dirty.DepCode = department.DepCode 
                WHERE department.HptCode = '$siteCode'";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $cntDocNo = $Result['cnt'];
            $count++;
            $boolean = true;
        }
        // $boolean = true;
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

    function logout($conn, $DATA){

        $logout = $DATA["Confirm"];

        unset($_SESSION['Userid']);
        unset($_SESSION['Username']);
        unset($_SESSION['FName']);
        unset($_SESSION['PmID']);
        unset($_SESSION['TimeOut']);
        unset($_SESSION['HptCode']);
        unset($_SESSION['FacCode']);
        session_destroy();

        if ($logout == 1) {
            $return['status'] = "success";
            $return['form'] = "logout";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "logout";
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
        else if ($DATA['STATUS'] == 'show_doc') {
            show_doc($conn, $DATA);
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