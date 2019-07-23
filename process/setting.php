<?php
    session_start();
    require '../connect/connect.php';

    function save_lang($conn, $DATA){
        $lang = $DATA["lang"];
        $Userid = $DATA["Userid"];
        $Sql = "UPDATE users SET lang = '$lang' WHERE `ID` = $Userid";
        if(mysqli_query($conn, $Sql)){
            $_SESSION['lang']=$lang;
            $return['status'] = "success";
            $return['form'] = "save_lang";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "save_lang";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function logout($conn, $DATA){

        $logout = $DATA["Confirm"];
        
        if ($logout == 1) {
            unset($_SESSION['Userid']);
            unset($_SESSION['Username']);
            unset($_SESSION['FName']);
            unset($_SESSION['PmID']);
            unset($_SESSION['TimeOut']);
            unset($_SESSION['HptCode']);
            unset($_SESSION['FacCode']);
            session_destroy();

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

        if ($DATA['STATUS'] == 'save_lang') {
            save_lang($conn, $DATA);
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