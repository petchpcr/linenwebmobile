<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_Doc($conn, $DATA){
        $search = $DATA["search"];
        $DepCode = $DATA['DepCode'];
        if($search == null || $search == ""){
            $search = date('Y-m-d');
        }
        $count = 0;
        $boolean = false;

        $Sql = "SELECT DocNo,IsStatus FROM shelfcount WHERE DepCode = $DepCode AND DocDate = '$search' AND IsStatus >0";
        $meQuery = mysqli_query($conn, $Sql);

        $return[' $Sql'] =  $Sql;

        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['DocNo'] = $Result['DocNo'];
            $return[$count]['IsStatus'] = $Result['IsStatus'];
            $count++;
            $boolean = true;
        }

        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "load_Doc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "load_Doc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    if(isset($_POST['DATA'])){
        $data = $_POST['DATA'];
        $DATA = json_decode(str_replace('\"', '"', $data), true);

        if ($DATA['STATUS'] == 'load_Doc') {
            load_Doc($conn, $DATA);
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