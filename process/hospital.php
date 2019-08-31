<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_site($conn){
        $FacCode = $_SESSION['FacCode'];
        $count = 0;
        $Sql = "SELECT * 
                FROM    (
                            SELECT site.HptCode,site.HptName,site.picture 
                            FROM site,dirty,department 
                            WHERE site.IsStatus = 0
                            AND dirty.DepCode = department.DepCode
                            AND dirty.FacCode = $FacCode
                            AND department.HptCode = site.HptCode

                            UNION All

                            SELECT site.HptCode,site.HptName,site.picture 
                            FROM site,rewash,department 
                            WHERE site.IsStatus = 0
                            AND rewash.DepCode = department.DepCode
                            AND rewash.FacCode = $FacCode
                            AND department.HptCode = site.HptCode

                            UNION All

                            SELECT site.HptCode,site.HptName,site.picture 
                            FROM site,newlinentable,department 
                            WHERE site.IsStatus = 0
                            AND newlinentable.DepCode = department.DepCode
                            AND newlinentable.FacCode = $FacCode
                            AND department.HptCode = site.HptCode
                        ) h
                GROUP BY HptCode
                ORDER BY HptName ASC";
        // $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['HptCode'] = $Result['HptCode'];
            $return[$count]['HptName'] = $Result['HptName'];
            $return[$count]['picture'] = $Result['picture'];
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

    function show_doc($conn, $DATA){
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

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_site') {
            load_site($conn);
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