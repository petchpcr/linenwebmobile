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

        $return['cnt'] = $count;
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

    function chk_items($conn, $DATA){
        $DocNo=$DATA["DocNo"];
        $ItemCode=$DATA["ItemCode"];
        $question=$DATA["question"];
        $IsStatus=$DATA["IsStatus"];

        $Sql = "    UPDATE      qcchecklist

                    SET         IsStatus = $IsStatus
                    
                    WHERE       DocNo= '$DocNo'
                    AND         ItemCode='$ItemCode'
                    AND         QuestionId='$question'";
        $return['sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);

        if ($meQuery = mysqli_query($conn,$Sql)) {
            $return['status'] = "success";
            $return['form'] = "chk_items";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "chk_items";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function close_question($conn, $DATA){
        $pDocNo=$DATA["DocNo"];
        $pItemCode=$DATA["ItemCode"];

        $cnum=0;
        $wnum=0;


        $Sql = "    SELECT      COUNT(*) AS cNum

                    FROM	    qcchecklist,
                                qcquestion
                            
                    WHERE       DocNo= '$pDocNo'
                    AND         ItemCode='$pItemCode'
                    AND         IsStatus=0
                    AND         qcchecklist.Questionid=qcquestion.CodeId
                    AND         CopeMethod=1";
            
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        //claim num
        $cnum = $Result['cNum'];

        $Sql = "    SELECT      COUNT(*) AS wNum

        FROM	    qcchecklist,
                    qcquestion
                
        WHERE       DocNo= '$pDocNo'
        AND         ItemCode='$pItemCode'
        AND         IsStatus=0
        AND         qcchecklist.Questionid=qcquestion.CodeId
        AND         CopeMethod=2";

        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        //rewash num
        $wnum = $Result['wNum'];
        //cliam num +rewash num
        $anum=$wnum+$cnum;

        if($anum!=0){
            $Sql = "    SELECT COUNT(*) AS qcqNum

            FROM	    qcchecklist
            
            WHERE       DocNo= '$pDocNo'
            AND         ItemCode='$pItemCode'";
        
            $meQuery = mysqli_query($conn,$Sql);
            $Result = mysqli_fetch_assoc($meQuery);

            //all list num
            $qcqnum = $Result['qcqNum'];

            //0=ผ่าน QC ,1=ส่งเครมบางส่วน , 2=ส่งเครมทั้งหมด ,3 = ส่งซักบางส่วน ,4 =ส่งซักทั้งหมด ,5= ส่งเคลมและซักบางส่วน,6= ส่งเคลมและซักทั้งหมด

            if($cnum==0){
                //4 =ส่งซักทั้งหมด
                if($wnum==$qcqnum){
                    $Sql = "    UPDATE      clean_detail
                
                    SET         IsCheckList = 4
                    
                    WHERE       DocNo= '$pDocNo'
                    AND         ItemCode='$pItemCode'";
                //3 = ส่งซักบางส่วน 
                }else{
                    $Sql = "    UPDATE      clean_detail
                
                    SET         IsCheckList = 3
                    
                    WHERE       DocNo= '$pDocNo'
                    AND         ItemCode='$pItemCode'";
                }
            }else{
                //2=ส่งเครมทั้งหมด
                if($cnum==$qcqnum){
                    $Sql = "    UPDATE      clean_detail
            
                    SET         IsCheckList = 2
                    
                    WHERE       DocNo= '$pDocNo'
                    AND         ItemCode='$pItemCode'";
                //5= ส่งเคลมและซัก
                }else{
                     //1=ส่งเครมบางส่วน
                    if($wnum==0){
                        $Sql = "    UPDATE      clean_detail
                    
                        SET         IsCheckList = 1
                        
                        WHERE       DocNo= '$pDocNo'
                        AND         ItemCode='$pItemCode'";
                    }else{
                        $Sql = "    UPDATE      clean_detail
                
                        SET         IsCheckList = 5
                        
                        WHERE       DocNo= '$pDocNo'
                        AND         ItemCode='$pItemCode'";
                    }
                }
               
            }
        //0=ผ่าน
        }else{
            $Sql = "    UPDATE      clean_detail
                
            SET         IsCheckList = 0
            
            WHERE       DocNo= '$pDocNo'
            AND         ItemCode='$pItemCode'";
            
        }
        $meQuery = mysqli_query($conn,$Sql);
        $return['cntClaim'] = $anum;
        $return['cntList'] = $qcqnum;

        if ($meQuery = mysqli_query($conn,$Sql)) {
            $return['status'] = "success";
            $return['form'] = "close_question";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "close_question";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function create_claim($conn, $DATA){
        $cleanDocNo=$DATA["DocNo"];
        $userid=$DATA["Userid"];

        $Sql = "SELECT      department.HptCode,
                            clean.DepCode

                FROM        clean,department

                WHERE       clean.DocNo = '$cleanDocNo'
                AND         department.DepCode=clean.DepCode";

        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        
        $hotpCode = $Result["HptCode"];
        $deptCode = $Result["DepCode"];

        $Sql = "SELECT      CONCAT('CM',LPAD('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                            LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                            DATE(NOW()) AS DocDate,
                            CURRENT_TIME() AS RecNow
                FROM        claim
                WHERE       DocNo Like CONCAT('CM',lpad('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                AND         HptCode = '$hotpCode'
                ORDER BY    DocNo DESC LIMIT 1";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $DocNo = $Result['DocNo'];
            $count = 1;
        }
        $return['NewDocNo'] = $DocNo;

        if ($count == 1) {

            // $Sql = "DELETE FROM claim WHERE DocNo = '$DocNo'";
            // $meQuery = mysqli_query($conn, $Sql);

            $Sql = "INSERT INTO     claim
                                    ( 
                                        HptCode,
                                        DepCode,
                                        DocNo,
                                        DocDate,
                                        RefDocNo,
                                        TaxNo,
                                        TaxDate,
                                        DiscountPercent,
                                        DiscountBath,
                                        Total,
                                        IsCancel,
                                        Detail,
                                        Modify_Code,
                                        Modify_Date
                                    )
                    VALUES          ( 
                                        '$hotpCode',
                                        $deptCode,
                                        '$DocNo',
                                        DATE(NOW()),
                                        '',
                                        null,
                                        DATE(NOW()),
                                        0,0,
                                        0,0,'',
                                        $userid,
                                        NOW()
                                    )";
            $meQuery = mysqli_query($conn, $Sql);
            $return['claim'] = $Sql;
            
            $Sql = "INSERT INTO     daily_request
                                    (
                                        DocNo,
                                        DocDate,
                                        DepCode,
                                        RefDocNo,
                                        Detail,
                                        Modify_Code,
                                        Modify_Date
                                    )
                    VALUES          (
                                        '$DocNo',
                                        DATE(NOW()),
                                        $deptCode,
                                        '',
                                        'Claim',
                                        $userid,
                                        DATE(NOW())
                                    )";

            $meQuery = mysqli_query($conn, $Sql);

            $return['status'] = "success";
            $return['form'] = "create_claim";
            echo json_encode($return);
            mysqli_close($conn);
            die;
             
        } else {
            $return['status'] = "failed";
            $return['form'] = "create_claim";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function send_claim($conn, $DATA){
        $DocNo=$DATA["DocNo"];

        $arr_claim_code = $DATA['claim_code'];
        $arr_claim_qty = $DATA['claim_qty'];
        $arr_claim_weight = $DATA['claim_weight'];
        $arr_claim_unit = $DATA['claim_unit'];

        $claim_code = explode(",", $arr_claim_code);
        $claim_qty = explode(",", $arr_claim_qty);
        $claim_weight = explode(",", $arr_claim_weight);
        $claim_unit = explode(",", $arr_claim_unit);

        $cnt_claim = sizeof($claim_code, 0);
        $return['cnt_claim'] = $cnt_claim;
        $count = 0;

        for ($i = 0; $i < $cnt_claim; $i++) {
            $Sql = "    SELECT          COUNT(*) as Cnt
                        FROM            claim_detail

                        WHERE           claim_detail.DocNo = '$DocNo'
                        AND             claim_detail.ItemCode = '$claim_code[$i]'";

            $meQuery = mysqli_query($conn, $Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $chkUpdate = $Result['Cnt'];
            }

            if ($chkUpdate == 0) {
                $Sql = "    INSERT INTO     claim_detail
                                            (
                                                DocNo,
                                                ItemCode,
                                                UnitCode1,
                                                UnitCode2,
                                                Qty1,
                                                Qty2,
                                                Weight,
                                                IsCancel,
                                                Price,
                                                Total
                                            )
                            VALUES
                                            (
                                                '$DocNo',
                                                '$claim_code[$i]',
                                                $claim_unit[$i],
                                                $claim_unit[$i],
                                                $claim_qty[$i],
                                                $claim_qty[$i],
                                                $claim_weight[$i],
                                                0,0,0
                                            )";
                    
                mysqli_query($conn, $Sql);
                $count++;
                $return[$i]['claim_detail'] = $Sql;
            } else {
                $Sql = "    UPDATE      claim_detail

                            SET         Qty1 = $claim_qty[$i],
                                        Qty2 = $claim_qty[$i],
                                        Weight = $claim_weight[$i]

                            WHERE       DocNo = '$DocNo'
                            AND         ItemCode = '$claim_code[$i]'";

                mysqli_query($conn, $Sql);
                $count++;
                $return[$i]['claim_detail'] = $Sql;
            }
        }

        if ($count == $cnt_claim) {
            $return['status'] = "success";
            $return['form'] = "send_claim";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "send_claim";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function create_rewash($conn, $DATA){
        $cleanDocNo=$DATA["DocNo"];
        $userid=$DATA["Userid"];

        $Sql = "SELECT      department.HptCode,
                            clean.DepCode

                FROM        clean,department

                WHERE       clean.DocNo = '$cleanDocNo'
                AND         department.DepCode=clean.DepCode";

        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        
        $hotpCode = $Result["HptCode"];
        $deptCode = $Result["DepCode"];

        $Sql = "SELECT      CONCAT('RW',LPAD('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                            LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,
                            DATE(NOW()) AS DocDate,
                            CURRENT_TIME() AS RecNow
                FROM        rewash
                WHERE       DocNo Like CONCAT('RW',lpad('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                AND         HptCode = '$hotpCode'
                ORDER BY    DocNo DESC LIMIT 1";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $DocNo = $Result['DocNo'];
            $count = 1;
        }

        if ($count == 1) {
            $Sql = "INSERT INTO     rewash
                                    ( 
                                        HptCode,
                                        DepCode,
                                        DocNo,
                                        DocDate,
                                        RefDocNo,
                                        TaxNo,
                                        TaxDate,
                                        DiscountPercent,
                                        DiscountBath,
                                        Total,
                                        IsCancel,
                                        Detail,
                                        Modify_Code,
                                        Modify_Date
                                    )
                    VALUES          ( 
                                        '$hotpCode',
                                        $deptCode,
                                        '$DocNo',
                                        DATE(NOW()),
                                        '',
                                        null,
                                        DATE(NOW()),
                                        0,0,
                                        0,0,'',
                                        $userid,
                                        NOW()
                                    )";
            $meQuery = mysqli_query($conn, $Sql);
            $return['rewash'] = $Sql;

            $Sql = "INSERT INTO     daily_request
                                    (
                                        DocNo,
                                        DocDate,
                                        DepCode,
                                        RefDocNo,
                                        Detail,
                                        Modify_Code,
                                        Modify_Date
                                    )
                    VALUES          (
                                        '$DocNo',
                                        DATE(NOW()),
                                        $deptCode,
                                        '',
                                        'Rewash',
                                        $userid,
                                        DATE(NOW())
                                    )";

            $meQuery = mysqli_query($conn, $Sql);

            $return['status'] = "success";
            $return['form'] = "create_rewash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
             
        } else {
            $return['status'] = "failed";
            $return['form'] = "create_rewash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function send_rewash($conn, $DATA){
        $DocNo=$DATA["DocNo"];

        $arr_rewash_code = $DATA['rewash_code'];
        $arr_rewash_qty = $DATA['rewash_qty'];
        $arr_rewash_weight = $DATA['rewash_weight'];
        $arr_rewash_unit = $DATA['rewash_unit'];

        $rewash_code = explode(",", $arr_rewash_code);
        $rewash_qty = explode(",", $arr_rewash_qty);
        $rewash_weight = explode(",", $arr_rewash_weight);
        $rewash_unit = explode(",", $arr_rewash_unit);

        $cnt_rewash = sizeof($rewash_code, 0);
        $count = 0;

        for ($i = 0; $i < $cnt_rewash; $i++) {
            $Sql = "    SELECT          COUNT(*) as Cnt
                        FROM            rewash_detail

                        WHERE           rewash_detail.DocNo = '$DocNo'
                        AND             rewash_detail.ItemCode = '$rewash_code[$i]'";

            $meQuery = mysqli_query($conn, $Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $chkUpdate = $Result['Cnt'];
            }

            if ($chkUpdate == 0) {
                $Sql = "    INSERT INTO     rewash_detail
                                            (
                                                    DocNo,
                                                    ItemCode,
                                                    UnitCode1,
                                                    UnitCode2,
                                                    Qty1,
                                                    Qty2,
                                                    Weight,
                                                    IsCancel,
                                                    Price,
                                                    Total
                                            )
                            VALUES
                                            (
                                                    '$DocNo',
                                                    '$rewash_code[$i]',
                                                    $rewash_unit[$i],
                                                    $rewash_unit[$i],
                                                    $rewash_qty[$i],
                                                    $rewash_qty[$i],
                                                    $rewash_weight[$i],
                                                    0,0,0
                                            )";
                    
                mysqli_query($conn, $Sql);
                $count++;
                $return['rewash_detail'] = $Sql;
            } else {
                $Sql = "    UPDATE      rewash_detail

                            SET         Qty1 = $rewash_qty[$i],
                                        Qty2 = $rewash_qty[$i],
                                        Weight = $rewash_weight[$i]

                            WHERE       DocNo = '$DocNo'
                            AND         ItemCode = '$rewash_code[$i]'";

                mysqli_query($conn, $Sql);
                $count++;
                $return['rewash_detail'] = $Sql;
            }
        }

        if ($count == $cnt_rewash) {
            $return['status'] = "success";
            $return['form'] = "send_rewash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "send_rewash";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function save_qc($conn, $DATA){
        $pDocNo = $DATA["DocNo"];

        //get number of QC pass
        $Sql = "    SELECT      COUNT(*) AS pNum

                    FROM	    clean_detail
                                    
                    WHERE       DocNo= '$pDocNo'
                    AND         IsCheckList=0";
            
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        $pNum = $Result['pNum'];

        //get number of all item
        $Sql = "    SELECT      COUNT(*) AS itemNum

                    FROM	    clean_detail
                        
                    WHERE       DocNo= '$pDocNo'";
        
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        $itemNum = $Result['itemNum'];

//0=ยังไม่ได้ตรวจสอบ QC , 1=ผ่าน QC , 2=ส่งเครม


        if($pNum==$itemNum){

//IsCheckList = 1 QC pass all  
            $Sql = "    UPDATE      clean
                
            SET         IsCheckList = 1
                
            WHERE       DocNo= '$pDocNo'";

//IsCheckList = 2 some send claim
        }else{
            $Sql = "    UPDATE      clean
                
            SET         IsCheckList = 2
                
            WHERE       DocNo= '$pDocNo'";
        }
        $return['Sql'] = $Sql;

        if ($meQuery = mysqli_query($conn,$Sql)) {
            $return['status'] = "success";
            $return['form'] = "save_qc";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "save_qc";
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
        else if ($DATA['STATUS'] == 'chk_items') {
            chk_items($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'close_question') {
            close_question($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'create_claim') {
            create_claim($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'send_claim') {
            send_claim($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'create_rewash') {
            create_rewash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'send_rewash') {
            send_rewash($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'save_qc') {
            save_qc($conn, $DATA);
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