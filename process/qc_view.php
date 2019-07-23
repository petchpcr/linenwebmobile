<?php
    session_start();
    require '../connect/connect.php';

    function load_items($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $boolean = false;
        $ItemCode = $DATA['ItemCode'];
        $return['ItemCode'] = $ItemCode;
        $Sql = "SELECT  item.ItemName,
                        item.ItemCode,
                        item.UnitCode,
                        clean_detail.Qty,
                        clean_detail.Weight,
                        clean_detail.IsCheckList

                FROM    item,
                        clean_detail

                WHERE   item.ItemCode = clean_detail.ItemCode

                AND     clean_detail.DocNo = '$DocNo' ";
        $return['sql'] = $Sql;

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ItemCode'] = $Result['ItemCode'];
            $return[$count]['ItemName'] = $Result['ItemName'];
            $return[$count]['UnitCode'] = $Result['UnitCode'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $return[$count]['IsCheckList'] = $Result['IsCheckList'];
            $count++;
            $boolean = true;
        }
        $return['boolean'] = $boolean;
        if ($boolean) {
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

    function show_question($conn, $DATA){
        $DocNo = $DATA['DocNo'];
        $ItemCode = $DATA['ItemCode'];
        $return['ItemCode'] = $ItemCode;
        $count = 0;
        $have = 0;
        $success = 0;
        $Sql = "SELECT ItemName FROM item WHERE ItemCode = '$ItemCode'";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return['ItemName']	=  $Result['ItemName'];
        }

        $Sql = "SELECT      qcquestion.Question,
                            qcchecklist.QuestionId,
                            IsStatus

                FROM        qcchecklist,qcquestion

                WHERE       DocNo = '$DocNo'
                AND         ItemCode = '$ItemCode'
                AND         qcquestion.CodeId=qcchecklist.QuestionId";
        
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['Question']	=  $Result['Question'];
            $return[$count]['IsStatus']	=  $Result['IsStatus'];
            $return[$count]['QuestionId']	=  $Result['QuestionId'];
            $count++;
            $have = 1;
            $success = 1;
        }

        if($have == 0){
            $Sql_get_catCode = "SELECT CategoryCode FROM item WHERE ItemCode = '$ItemCode'";
            
            $meQuery = mysqli_query($conn,$Sql_get_catCode);
            $Result = mysqli_fetch_assoc($meQuery);

            $catCode = $Result['CategoryCode'];

            $Sql_get_qcquestion = "SELECT questionCode FROM category_qcquestion WHERE category_qcquestion.categoryCode=$catCode";

            $meQuery = mysqli_query($conn,$Sql_get_qcquestion);
            while ($Result = mysqli_fetch_assoc($meQuery)){
                $qid = $Result['questionCode'];
                $Sql_ins_checklist = "    INSERT INTO     qcchecklist
                                        (
                                            DocNo ,
                                            ItemCode,
                                            QCDate,
                                            QuestionId
                                        )
                        
                            VALUES      (   '$DocNo',
                                            '$ItemCode', 
                                            NOW() , 
                                            $qid
                                        )    ";
                mysqli_query($conn,$Sql_ins_checklist);
            }

            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)){
                $return[$count]['Question']	=  $Result['Question'];
                $return[$count]['IsStatus']	=  $Result['IsStatus'];
                $return[$count]['QuestionId']	=  $Result['QuestionId'];
                $return[$count]['count'] = $count;
                $count++;
                $success = 1;
            }
        }

        if ($success == 1) {
            $return['status'] = "success";
            $return['form'] = "show_question";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "show_question";
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

        if ($DATA['STATUS'] == 'load_items') {
            load_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'show_question') {
            show_question($conn, $DATA);
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