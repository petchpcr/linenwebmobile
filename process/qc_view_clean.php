<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_items($conn, $DATA){
        $count = 0;
        $cnt_checkpass = 0;
        $DocNo = $DATA["DocNo"];
        $ItemCode = array();
        $Qty = array();
        $Weight = array();

        $Sql = "SELECT IsStatus FROM clean WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $return['IsStatus'] = $Result['IsStatus'];

        $Sql = "SELECT  item.ItemName,
                        item.ItemCode,
                        item.UnitCode,
                        clean_detail.Qty,
                        clean_detail.Weight,
                        clean_detail.IsCheckList 

                FROM    item,
                        clean_detail 

                WHERE   item.ItemCode = clean_detail.ItemCode 
                AND     clean_detail.DocNo = '$DocNo'";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ItemCode'] = $Result['ItemCode'];
            $return[$count]['ItemName'] = $Result['ItemName'];
            $return[$count]['UnitCode'] = $Result['UnitCode'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $return[$count]['IsCheckList'] = $Result['IsCheckList'];
            array_push($ItemCode,$Result['ItemCode']);
            array_push($Qty,$Result['Qty']);
            array_push($Weight,$Result['Weight']);
            $count++;
        }
        $return['cnt'] = $count;

        for ($i = 0; $i < $count; $i++) {
            $Sql_cnt = "SELECT COUNT(*) AS cnt_ChkPass FROM qccheckpass WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode[$i]'";
            $meQuery_cnt = mysqli_query($conn,$Sql_cnt);
            $Result_cnt = mysqli_fetch_assoc($meQuery_cnt);
            $cnt_ChkPass = $Result_cnt['cnt_ChkPass'];

            if ($cnt_ChkPass == 0) {
                $Sql_ins = "INSERT INTO qccheckpass(DocNo,ItemCode,Pass,Fail,Lost,Claim,Rewash,QCDate) 
                            VALUES ('$DocNo','$ItemCode[$i]',$Qty[$i],0,0,0,0,NOW())";
                mysqli_query($conn,$Sql_ins);
            }

            $Sql = "SELECT Fail,Claim,Rewash,Lost FROM qccheckpass WHERE ItemCode = '$ItemCode[$i]' AND DocNo = '$DocNo'";
            $meQuery = mysqli_query($conn, $Sql);
            $Result = mysqli_fetch_assoc($meQuery);
            $return[$i]['Fail'] = $Result['Fail'];
            $return[$i]['Claim'] = $Result['Claim'];
            $return[$i]['Rewash'] = $Result['Rewash'];
            $return[$i]['Lost'] = $Result['Lost'];
            $cnt_checkpass++;
        }
        $return['cnt_checkpass'] = $cnt_checkpass;

        if ($count > 0) {
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

    function show_quantity($conn, $DATA){
        $DocNo = $DATA['DocNo'];
        $ItemCode = $DATA['ItemCode'];
        $return['ItemCode'] = $ItemCode;
        $boolean = false;

        $Sql = "SELECT (SELECT ItemName FROM item WHERE ItemCode = '$ItemCode') AS itemname,Qty,
                        (SELECT Pass FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Pass,
                        (SELECT Fail FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Fail, 
                        (SELECT Lost FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Lost, 
                        (SELECT Claim FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Claim, 
                        (SELECT Rewash FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Rewash 
                FROM clean_detail WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo'";
                $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return['ItemName']	=  $Result['itemname'];
            $return['Qty']	=  $Result['Qty'];
            $return['Pass']	=  $Result['Pass'];
            $return['Fail']	=  $Result['Fail'];
            $return['Lost']	=  $Result['Lost'];
            $return['Claim']	=  $Result['Claim'];
            $return['Rewash']	=  $Result['Rewash'];
            $boolean = true;
        }

        if ($boolean) {
            $return['status'] = "success";
            $return['form'] = "show_quantity";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        } else {
            $return['status'] = "failed";
            $return['form'] = "show_quantity";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function save_checkpass($conn, $DATA) {
        $DocNo = $DATA['DocNo'];
        $ItemCode = $DATA['ItemCode'];
        $pass = $DATA['pass'];
        $fail = $DATA['fail'];
        $return['check'] = $fail;
        $lost = $DATA["lost"];
        $claim = $DATA["claim"];
        $rewash = $DATA["rewash"];

        $Sql = "SELECT COUNT(ItemCode) AS cnt FROM qccheckpass WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $cnt = $Result['cnt'];
        $return['cnt'] = $cnt;

        $passOld = 0;

        if ($cnt > 0) {
            $Sql = "SELECT Pass FROM qccheckpass WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
            $meQuery = mysqli_query($conn,$Sql);
            $Result = mysqli_fetch_assoc($meQuery);
            $passOld = $Result['Pass'];
            $Sql = "UPDATE qccheckpass SET Pass = $pass, Fail = $fail, Lost = $lost, Claim = $claim, Rewash = $rewash, QCDate = NOW() WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        }
        else {
            $Sql = "INSERT INTO	qccheckpass(DocNo,ItemCode,Pass,Fail,Lost,Claim,Rewash,QCDate) VALUES ('$DocNo','$ItemCode',$pass,$fail,$lost,$claim,$rewash,NOW())";
        }

        if (mysqli_query($conn, $Sql)) {

            if ($fail == 0) {
                $Sql = "UPDATE clean_detail SET IsCheckList = 0 WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
                mysqli_query($conn, $Sql);
    
                $Sql = "DELETE FROM qcchecklist WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
                mysqli_query($conn, $Sql);

                $return['unfail'] = 1;
            }

            if ($claim == 0 && $rewash == 0 && $lost > 0) { // สูญหาย
                $checklist = 1;
            }
            else if ($claim > 0 && $rewash == 0 && $lost == 0) { // เคลม
                $checklist = 2;
            }
            else if ($claim == 0 && $rewash > 0 && $lost == 0) { // ซักอีกครั้ง
                $checklist = 3;
            }
            else if ($claim > 0 && $rewash == 0 && $lost > 0) { // เคลม สูญหาย
                $checklist = 4;
            }
            else if ($claim == 0 && $rewash > 0 && $lost > 0) { // ซักอีกครั้ง สูญหาย
                $checklist = 5;
            }
            else if ($claim > 0 && $rewash > 0 && $lost == 0) { // เคลม ซักอีกครั้ง
                $checklist = 6;
            }
            else if ($claim > 0 && $rewash > 0 && $lost > 0) { // ไม่ผ่านทั้งหมด
                $checklist = 7;
            }
            else { // ผ่านทั้งหมด
                $checklist = 0;
            }

            $Sql = "UPDATE clean_detail SET IsCheckList = $checklist WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
            mysqli_query($conn, $Sql);
            
            $return['ItemCode'] = $ItemCode;
            $return['status'] = "success";
            $return['form'] = "save_checkpass";
            echo json_encode($return);
            mysqli_close($conn);
            die;
             
        } else {
            $return['status'] = "failed";
            $return['form'] = "save_checkpass";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function show_question($conn, $DATA){
        $DocNo = $DATA['DocNo'];
        $ItemCode = $DATA['ItemCode'];
        $return['DocNo'] = $DocNo;
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
                            qcchecklist.Qty

                FROM        qcchecklist,qcquestion

                WHERE       DocNo = '$DocNo'
                AND         ItemCode = '$ItemCode'
                AND         qcquestion.CodeId=qcchecklist.QuestionId";
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return[$count]['Question']	=  $Result['Question'];
            $return[$count]['QuestionId']	=  $Result['QuestionId'];
            $return[$count]['Qty']	=  $Result['Qty'];
            $count++;
            $have = 1;
            $success = 1;
        }

        if($have == 0){
            $count = 0;
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
                                            QuestionId
                                        )
                        
                            VALUES      (   '$DocNo',
                                            '$ItemCode',
                                            $qid
                                        )    ";
                mysqli_query($conn,$Sql_ins_checklist);
            }

            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)){
                $return[$count]['Question']	=  $Result['Question'];
                $return[$count]['QuestionId']	=  $Result['QuestionId'];
                $return[$count]['Qty']	=  $Result['Qty'];
                $count++;
                $success = 1;
            }
        }
        $return['cnt'] = $count;
        
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
        $return['DocNo']=$pDocNo;
        $return['ItemCode']=$pItemCode;

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
                        $return['cnum'] = $cnum;
                        $return['wnum'] = $wnum;
                        $return['qcqnum'] = $qcqnum;
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

    function save_checklist($conn, $DATA){
        $DocNo=$DATA["DocNo"];
        $ItemCode=$DATA["ItemCode"];
        $question=$DATA["question"];
        $amount=$DATA["amount"];
        
        $arr_question = explode(",", $question);
        $arr_amount = explode(",", $amount);

        $cnt_question = sizeof($arr_question, 0);
        $return['cnt_question'] = $cnt_question;
        $count = 0;

        for ($i = 0; $i < $cnt_question; $i++){
            $Sql = "SELECT COUNT(QuestionId) AS cnt FROM qcchecklist WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND QuestionId = '$arr_question[$i]'";
            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)){
                $cnt = $Result['cnt'];
            }

            if ($cnt > 0) {
                $Sql = "UPDATE qcchecklist SET Qty = $arr_amount[$i] WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode' AND QuestionId = '$arr_question[$i]'";
            }
            else {
                $Sql = "INSERT INTO	qcchecklist(DocNo,ItemCode,QuestionId,Qty) VALUES ('$DocNo','$ItemCode','$arr_question[$i]',$arr_amount[$i])";
            }

            if (mysqli_query($conn, $Sql)){
                $count++;
            }
        }

        if ($count == $cnt_question) {
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

    function claim_detail($conn, $DATA){
        $DocNo=$DATA["DocNo"];
        $ItemCode=$DATA["ItemCode"];

        $Sql = "SELECT ";

        $return['status'] = "success";
        $return['form'] = "claim_detail";
        echo json_encode($return);
        mysqli_close($conn);
        die;
    }
    
    function show_claim_detail($conn, $DATA){
        $DocNo=$DATA["DocNo"];
        $ItemCode=$DATA["ItemCode"];
        $cntLost = 0;
        $count = 0;

        $Sql = "SELECT Lost FROM qccheckpass WHERE DocNo= '$DocNo' AND ItemCode = '$ItemCode'";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return['Lost'] = $Result['Lost'];
            if ($Result['Lost'] > 0) {
                $cntLost++;
            }
        }
        $return['cntLost'] = $cntLost;

        $Sql = "SELECT qcquestion.Question,qcchecklist.Qty FROM qcchecklist 
                INNER JOIN qcquestion ON qcchecklist.QuestionId = qcquestion.CodeId 
                WHERE DocNo= '$DocNo' AND ItemCode = '$ItemCode'";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['Question'] = $Result['Question'];
            $return[$count]['Qty'] = $Result['Qty'];
            $count++;
        }
        $return['cnt'] = $count;
        
        if ($count > 0 || $cntLost > 0) {
            $return['status'] = "success";
            $return['form'] = "show_claim_detail";
            echo json_encode($return);
            mysqli_close($conn);
            die;
                
        } else {
            $return['status'] = "failed";
            $return['form'] = "show_claim_detail";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
    }

    function create_claim($conn, $DATA){
        $cleanDocNo=$DATA["DocNo"];
        $userid=$DATA["Userid"];
        $count = 0;
        $Fail = 0;

        $Sql = "SELECT      department.HptCode,
                            clean.DepCode

                FROM        clean,department

                WHERE       clean.DocNo = '$cleanDocNo'
                AND         department.DepCode=clean.DepCode";

        $meQuery = mysqli_query($conn, $Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $hotpCode = $Result["HptCode"];
        $deptCode = $Result["DepCode"];

        $Sql = "SELECT clean_detail.ItemCode,clean_detail.UnitCode,clean_detail.Weight,clean_detail.IsCheckList
                FROM clean_detail
                INNER JOIN clean ON clean_detail.DocNo = clean.DocNo 
                WHERE clean_detail.DocNo = '$cleanDocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $itemCode = $Result['ItemCode'];
            $unitCode = $Result['UnitCode'];
            // $weight = $Result['Weight'];
            $weight = 0;
            $CheckList = $Result['IsCheckList'];

            // SELECT เพื่อเอาจำนวน claim rewash และ remain
            $Sql_qty = "SELECT Fail,Claim,Rewash,Lost FROM qccheckpass WHERE DocNo = '$cleanDocNo' AND ItemCode = '$itemCode'";
            $meQuery_qty = mysqli_query($conn, $Sql_qty);
            $Result_qty = mysqli_fetch_assoc($meQuery_qty);
            $sum_fail = $Result_qty['Fail'];
            $sum_claim = $Result_qty['Claim'];
            $sum_rewash = $Result_qty['Rewash'];
            $sum_remain = $Result_qty['Lost'];

            // SELECT เพื่อเอา RefDoc
            $Sql_ref = "SELECT RefDocNo FROM clean WHERE DocNo = '$cleanDocNo'";
            $meQuery_ref = mysqli_query($conn, $Sql_ref);
            $Result_ref = mysqli_fetch_assoc($meQuery_ref);
            $ref = $Result_ref['RefDocNo'];

            // SELECT เพื่อเอา FacCode
            $Sql_fac = "SELECT FacCode FROM dirty WHERE dirty.DocNo = '$ref'
                        UNION ALL
                        SELECT FacCode FROM rewash WHERE rewash.DocNo = '$ref'
                        UNION ALL
                        SELECT FacCode FROM remain WHERE remain.DocNo = '$ref'
                        UNION ALL
                        SELECT FacCode FROM newlinentable WHERE newlinentable.DocNo = '$ref'";
            $return[$count]['Sql Fac'] = $Sql_fac;
            $meQuery_fac = mysqli_query($conn, $Sql_fac);
            $Result_fac = mysqli_fetch_assoc($meQuery_fac);
            $fac = $Result_fac['FacCode'];

            // เช็คว่าเคยสร้างแล้วรึป่าว แล้วเก็บ DocNo ไว้ (Rewash)
            $Sql_check = "SELECT COUNT(RefDocNo) AS chkRewash,DocNo AS DocDetali FROM rewash WHERE RefDocNo = '$cleanDocNo'";
            $meQuery_check = mysqli_query($conn, $Sql_check);
            $Result_check = mysqli_fetch_assoc($meQuery_check);
            $chkRewash = $Result_check['chkRewash'];
            $DocDetaliRewash = $Result_check['DocDetali'];
            
            // เช็คว่าเคยสร้างแล้วรึป่าว แล้วเก็บ DocNo ไว้ (Claim)
            $Sql_check = "SELECT COUNT(RefDocNo) AS chkClaim,DocNo AS DocDetali FROM claim WHERE RefDocNo = '$cleanDocNo'";
            $meQuery_check = mysqli_query($conn, $Sql_check);
            $Result_check = mysqli_fetch_assoc($meQuery_check);
            $chkClaim = $Result_check['chkClaim'];
            $DocDetaliClaim = $Result_check['DocDetali'];

            // เช็คว่าเคยสร้างแล้วรึป่าว แล้วเก็บ DocNo ไว้ (Remain)
            // $Sql_check = "SELECT COUNT(RefDocNo) AS chkRemain,DocNo AS DocDetali FROM remain WHERE RefDocNo = '$cleanDocNo'";
            // $meQuery_check = mysqli_query($conn, $Sql_check);
            // $Result_check = mysqli_fetch_assoc($meQuery_check);
            // $chkRemain = $Result_check['chkRemain'];
            // $DocDetaliRemain = $Result_check['DocDetali'];

            // ++++++++++++++++++++++++++++++++++++++++++++ สร้างเอกสาร ++++++++++++++++++++++++++++++++++++++++++++
            if ($CheckList == 0) { // -------------- Pass -------------- 
                $Sql_pass = "DELETE FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM claim_detail WHERE DocNo = '$DocDetaliClaim'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM claim WHERE DocNo = '$DocDetaliClaim'";
                    mysqli_query($conn, $Sql_pass);
                }

                $Sql_pass = "DELETE FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM rewash_detail WHERE DocNo = '$DocDetaliRewash'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM rewash WHERE DocNo = '$DocDetaliRewash'";
                    mysqli_query($conn, $Sql_pass);
                }

                // $Sql_pass = "DELETE FROM remain_detail WHERE DocNo = '$DocDetaliRemain' AND ItemCode = '$itemCode'";
                // mysqli_query($conn, $Sql_pass);

                // $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM remain_detail WHERE DocNo = '$DocDetaliRemain'";
                // $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                // $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                // $cntEmpty = $Result_chkEmpty['cntEmpty'];
                // if ($cntEmpty == 0) {
                //     $Sql_pass = "DELETE FROM remain WHERE DocNo = '$DocDetaliRemain'";
                //     mysqli_query($conn, $Sql_pass);
                // }
            }
            if ($CheckList == 2 || $CheckList == 4 || $CheckList == 6 || $CheckList == 7) { // -------------- Claim -------------- 
                // สร้างเอกสาร claim
                $Sql_claim = "SELECT      CONCAT('CM',LPAD('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                        LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,DATE(NOW()) AS DocDate,CURRENT_TIME() AS RecNow
                            FROM        claim
                            WHERE       DocNo Like CONCAT('CM',lpad('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                            AND         HptCode = '$hotpCode'
                            ORDER BY    DocNo DESC LIMIT 1";
                $meQuery2 = mysqli_query($conn, $Sql_claim);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $DocNo = $Result['DocNo'];
                    $count_claim = 1;
                }

                if ($count_claim == 1) { // ถ้าสร้างเลขใหม่ได้
                    if ($chkClaim == 0) { // ถ้าไม่มีเคยมีเอกสารนี้
                        $Sql_claim = "INSERT INTO claim(HptCode,DepCode,DocNo,DocDate,RefDocNo,TaxNo,TaxDate,DiscountPercent,DiscountBath,Total,IsStatus,IsCancel,Detail,Modify_Code,Modify_Date)
                                        VALUES ('$hotpCode',$deptCode,'$DocNo',DATE(NOW()),'$cleanDocNo',null,DATE(NOW()),0,0,0,1,0,'',$userid,NOW())";

                        mysqli_query($conn, $Sql_claim);
                        
                        $Sql_claim = "INSERT INTO daily_request(DocNo,DocDate,DepCode,RefDocNo,IsStatus,Detail,Modify_Code,Modify_Date)
                                            VALUES ('$DocNo',DATE(NOW()),$deptCode,'',1,'Claim',$userid,DATE(NOW()))";
            
                        mysqli_query($conn, $Sql_claim);
                    }
                }
                else {
                    $Fail++;
                }

                // สร้างเอกสาร claim_detail
                if ($chkClaim == 0) { // ถ้าไม่มีเคยมีเอกสารใน claim
                    $Sql_claim = "INSERT INTO claim_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                    VALUES ('$DocNo','$itemCode',$unitCode,1,$sum_claim,0,$weight,0,0,0)";
                }
                else {
                    $Sql_chkDetail = "SELECT COUNT(ItemCode) AS chkDeteil FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                    $meQuery_chkDetail = mysqli_query($conn, $Sql_chkDetail);
                    $Result_chkDetail = mysqli_fetch_assoc($meQuery_chkDetail);
                    $chkDeteil = $Result_chkDetail['chkDeteil'];

                    if ($chkDeteil == 0) { // ถ้าไม่มีเคยมีเอกสารใน claim_detail
                        $Sql_claim = "INSERT INTO claim_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                        VALUES ('$DocDetaliClaim','$itemCode',$unitCode,1,$sum_claim,0,$weight,0,0,0)";
                    }
                    else {
                        $Sql_claim = "UPDATE claim_detail SET Qty1 = $sum_claim,Qty2 = 0,Weight = $weight 
                                        WHERE       DocNo = '$DocDetaliClaim'
                                        AND         ItemCode = '$itemCode'";
                    }
                }
                mysqli_query($conn, $Sql_claim);
                
            }
            if ($CheckList == 3 || $CheckList == 5 || $CheckList == 6 || $CheckList == 7) { // -------------- Rewash -------------- 
                // สร้างเอกสาร rewash
                $Sql_rewash = "SELECT      CONCAT('RW',LPAD('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
                                            LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,DATE(NOW()) AS DocDate,CURRENT_TIME() AS RecNow
                                FROM        rewash
                                WHERE       DocNo Like CONCAT('RW',lpad('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
                                ORDER BY    DocNo DESC LIMIT 1";

                $meQuery2 = mysqli_query($conn, $Sql_rewash);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $DocNo = $Result['DocNo'];
                    $count_rewash = 1;
                }

                if ($count_rewash == 1) {
                    if ($chkRewash == 0) {
                        $Sql_rewash = "INSERT INTO rewash(DepCode,DocNo,DocDate,RefDocNo,TaxNo,TaxDate,DiscountPercent,DiscountBath,Total,IsCancel,Detail,Modify_Code,Modify_Date,IsStatus,FacCode)
                                        VALUES ($deptCode,'$DocNo',DATE(NOW()),'$cleanDocNo',null,DATE(NOW()),0,0,0,0,'',$userid,NOW(),1,$fac)";
                        mysqli_query($conn, $Sql_rewash);
                        
                        $Sql_rewash = "INSERT INTO daily_request(DocNo,DocDate,DepCode,RefDocNo,Detail,Modify_Code,Modify_Date)
                                        VALUES ('$DocNo',DATE(NOW()),$deptCode,'','Rewash',$userid,DATE(NOW()))";
                        mysqli_query($conn, $Sql_rewash);
                    }
                }
                else {
                    $Fail++;
                }

                // สร้างเอกสาร rewash_detail
                if ($chkRewash == 0) { // ถ้าไม่มีเคยมีเอกสารใน rewash
                    $Sql_rewash = "INSERT INTO rewash_detail(DocNo,ItemCode,UnitCode,Qty,ReceiveQty,Weight,IsCancel)
                                    VALUES ('$DocNo','$itemCode',$unitCode,$sum_rewash,0,$weight,0)";
                }
                else {
                    $Sql_chkDetail = "SELECT COUNT(ItemCode) AS chkDeteil FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                    $meQuery_chkDetail = mysqli_query($conn, $Sql_chkDetail);
                    $Result_chkDetail = mysqli_fetch_assoc($meQuery_chkDetail);
                    $chkDeteil = $Result_chkDetail['chkDeteil'];

                    if ($chkDeteil == 0) { // ถ้าไม่มีเคยมีเอกสารใน rewash_detail
                        $Sql_rewash = "INSERT INTO rewash_detail(DocNo,ItemCode,UnitCode,Qty,ReceiveQty,Weight,IsCancel)
                                        VALUES ('$DocDetaliRewash','$itemCode',$unitCode,$sum_rewash,0,$weight,0)";
                    }
                    else {
                        $Sql_rewash = "UPDATE rewash_detail SET Qty = $sum_rewash,ReceiveQty = 0,Weight = $weight 
                                        WHERE DocNo = '$DocDetaliRewash'
                                        AND ItemCode = '$itemCode'";
                    }
                }
                $return['ssqqll'] = $Sql_rewash;
                mysqli_query($conn, $Sql_rewash);
            }
            // if ($CheckList == 1 || $CheckList == 4 || $CheckList == 5 || $CheckList == 7) { // -------------- Remain -------------- 
            //     // สร้างเอกสาร remain
            //     $Sql_remain = "SELECT      CONCAT('RM',LPAD('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'-',
            //                                 LPAD( (COALESCE(MAX(CONVERT(SUBSTRING(DocNo,12,5),UNSIGNED INTEGER)),0)+1) ,5,0)) AS DocNo,DATE(NOW()) AS DocDate,CURRENT_TIME() AS RecNow
            //                     FROM        remain
            //                     WHERE       DocNo Like CONCAT('RM',lpad('$hotpCode', 3, 0),SUBSTRING(YEAR(DATE(NOW())),3,4),LPAD(MONTH(DATE(NOW())),2,0),'%')
            //                     ORDER BY    DocNo DESC LIMIT 1";

            //     $meQuery2 = mysqli_query($conn, $Sql_remain);
            //     while ($Result = mysqli_fetch_assoc($meQuery2)) {
            //         $DocNo = $Result['DocNo'];
            //         $count_remain = 1;
            //     }

            //     if ($count_remain == 1) {
            //         if ($chkRemain == 0) {
            //             $Sql_remain = "INSERT INTO remain(DepCode,DocNo,DocDate,RefDocNo,TaxNo,TaxDate,DiscountPercent,DiscountBath,Total,IsCancel,Detail,Modify_Code,Modify_Date,IsStatus,FacCode)
            //                             VALUES ($deptCode,'$DocNo',DATE(NOW()),'$cleanDocNo',null,DATE(NOW()),0,0,0,0,'',$userid,NOW(),1,$fac)";
            //             mysqli_query($conn, $Sql_remain);
                        
            //             $Sql_remain = "INSERT INTO daily_request(DocNo,DocDate,DepCode,RefDocNo,Detail,Modify_Code,Modify_Date)
            //                             VALUES ('$DocNo',DATE(NOW()),$deptCode,'','Remain',$userid,DATE(NOW()))";
            //             mysqli_query($conn, $Sql_remain);
            //         }
            //     }
            //     else {
            //         $Fail++;
            //     }

            //     // สร้างเอกสาร remain_detail
            //     if ($chkRemain == 0) { // ถ้าไม่มีเคยมีเอกสารใน remain
            //         $Sql_remain = "INSERT INTO remain_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
            //                         VALUES ('$DocNo','$itemCode',$unitCode,1,$sum_remain,0,$weight,0,0,0)";
            //     }
            //     else {
            //         $Sql_chkDetail = "SELECT COUNT(ItemCode) AS chkDeteil FROM remain_detail WHERE DocNo = '$DocDetaliRemain' AND ItemCode = '$itemCode'";
            //         $meQuery_chkDetail = mysqli_query($conn, $Sql_chkDetail);
            //         $Result_chkDetail = mysqli_fetch_assoc($meQuery_chkDetail);
            //         $chkDeteil = $Result_chkDetail['chkDeteil'];

            //         if ($chkDeteil == 0) { // ถ้าไม่มีเคยมีเอกสารใน remain_detail
            //             $Sql_remain = "INSERT INTO remain_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
            //                             VALUES ('$DocDetaliRemain','$itemCode',$unitCode,1,$sum_remain,0,$weight,0,0,0)";
            //         }
            //         else {
            //             $Sql_remain = "UPDATE remain_detail SET Qty1 = $sum_remain,Qty2 = 0,Weight = $weight 
            //                             WHERE DocNo = '$DocDetaliRemain'
            //                             AND ItemCode = '$itemCode'";
            //         }
            //     }
            //     mysqli_query($conn, $Sql_remain);
            // }

            // ++++++++++++++++++++++++++++++++++++++++++++ ลบเอกสาร ++++++++++++++++++++++++++++++++++++++++++++
            if ($CheckList == 1) {
                $Sql_pass = "DELETE FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM claim_detail WHERE DocNo = '$DocDetaliClaim'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM claim WHERE DocNo = '$DocDetaliClaim'";
                    mysqli_query($conn, $Sql_pass);
                }

                $Sql_pass = "DELETE FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM rewash_detail WHERE DocNo = '$DocDetaliRewash'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM rewash WHERE DocNo = '$DocDetaliRewash'";
                    mysqli_query($conn, $Sql_pass);
                }
            }
            else if ($CheckList == 2) {
                $Sql_pass = "DELETE FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM rewash_detail WHERE DocNo = '$DocDetaliRewash'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM rewash WHERE DocNo = '$DocDetaliRewash'";
                    mysqli_query($conn, $Sql_pass);
                }

                // $Sql_pass = "DELETE FROM remain_detail WHERE DocNo = '$DocDetaliRemain' AND ItemCode = '$itemCode'";
                // mysqli_query($conn, $Sql_pass);

                // $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM remain_detail WHERE DocNo = '$DocDetaliRemain'";
                // $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                // $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                // $cntEmpty = $Result_chkEmpty['cntEmpty'];
                // if ($cntEmpty == 0) {
                //     $Sql_pass = "DELETE FROM remain WHERE DocNo = '$DocDetaliRemain'";
                //     mysqli_query($conn, $Sql_pass);
                // }
            }
            else if ($CheckList == 3) {
                $Sql_pass = "DELETE FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM claim_detail WHERE DocNo = '$DocDetaliClaim'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM claim WHERE DocNo = '$DocDetaliClaim'";
                    mysqli_query($conn, $Sql_pass);
                }

                // $Sql_pass = "DELETE FROM remain_detail WHERE DocNo = '$DocDetaliRemain' AND ItemCode = '$itemCode'";
                // mysqli_query($conn, $Sql_pass);

                // $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM remain_detail WHERE DocNo = '$DocDetaliRemain'";
                // $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                // $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                // $cntEmpty = $Result_chkEmpty['cntEmpty'];
                // if ($cntEmpty == 0) {
                //     $Sql_pass = "DELETE FROM remain WHERE DocNo = '$DocDetaliRemain'";
                //     mysqli_query($conn, $Sql_pass);
                // }
            }
            else if ($CheckList == 4) {
                $Sql_pass = "DELETE FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM rewash_detail WHERE DocNo = '$DocDetaliRewash'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM rewash WHERE DocNo = '$DocDetaliRewash'";
                    mysqli_query($conn, $Sql_pass);
                }          
            }
            else if ($CheckList == 5) {
                $Sql_pass = "DELETE FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM claim_detail WHERE DocNo = '$DocDetaliClaim'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM claim WHERE DocNo = '$DocDetaliClaim'";
                    mysqli_query($conn, $Sql_pass);
                }
            }
            else if ($CheckList == 6) {
                // $Sql_pass = "DELETE FROM remain_detail WHERE DocNo = '$DocDetaliRemain' AND ItemCode = '$itemCode'";
                // mysqli_query($conn, $Sql_pass);

                // $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM remain_detail WHERE DocNo = '$DocDetaliRemain'";
                // $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                // $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                // $cntEmpty = $Result_chkEmpty['cntEmpty'];
                // if ($cntEmpty == 0) {
                //     $Sql_pass = "DELETE FROM remain WHERE DocNo = '$DocDetaliRemain'";
                //     mysqli_query($conn, $Sql_pass);
                // }
            }
            
            $count++;
        }
        $return['count'] = $count;
        if ($Fail == 0) {
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

    function save_qc($conn, $DATA){
        $pDocNo = $DATA["DocNo"];
        
        //get number of QC pass
        $Sql = "SELECT COUNT(*) AS pNum FROM clean_detail WHERE DocNo= '$pDocNo' AND IsCheckList = 0";
        
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        $pNum = $Result['pNum'];

        //get number of all item
        $Sql = "SELECT COUNT(*) AS itemNum FROM clean_detail WHERE DocNo= '$pDocNo'";
        
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        $itemNum = $Result['itemNum'];

        //0=ยังไม่ได้ตรวจสอบ QC , 1=ผ่าน QC , 2=ส่งเครม

        if($pNum==$itemNum){ // IsCheckList = 1 QC pass all 
            $Sql = "SELECT clean_detail.ItemCode 
                    FROM clean_detail
                    INNER JOIN clean ON clean_detail.DocNo = clean.DocNo 
                    WHERE clean_detail.DocNo = '$pDocNo'";
            $meQuery = mysqli_query($conn, $Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)) {
                $itemCode = $Result['ItemCode'];

                $Sql_DocNo = "SELECT claim_detail.DocNo AS DocDetaliClaim
                                FROM claim_detail
                                INNER JOIN claim ON claim.DocNo = claim_detail.DocNo 
                                INNER JOIN clean ON clean.DocNo = claim.RefDocNo 
                                WHERE clean.DocNo = '$pDocNo' 
                                AND claim_detail.ItemCode = '$itemCode'";
                $meQuery_DocNo = mysqli_query($conn, $Sql_DocNo);
                $Result_DocNo = mysqli_fetch_assoc($meQuery_DocNo);
                $DocDetaliClaim = $Result_DocNo['DocDetaliClaim'];

                $Sql_pass = "DELETE FROM claim_detail WHERE DocNo = '$DocDetaliClaim' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM claim_detail WHERE DocNo = '$DocDetaliClaim'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM claim WHERE DocNo = '$DocDetaliClaim'";
                    mysqli_query($conn, $Sql_pass);
                }

                $Sql_DocNo = "SELECT rewash_detail.DocNo AS DocDetaliRewash
                                FROM rewash_detail
                                INNER JOIN rewash ON rewash.DocNo = rewash_detail.DocNo 
                                INNER JOIN clean ON clean.DocNo = rewash.RefDocNo 
                                WHERE clean.DocNo = '$pDocNo' 
                                AND rewash_detail.ItemCode = '$itemCode'";
                $meQuery_DocNo = mysqli_query($conn, $Sql_DocNo);
                $Result_DocNo = mysqli_fetch_assoc($meQuery_DocNo);
                $DocDetaliRewash = $Result_DocNo['DocDetaliRewash'];

                $Sql_pass = "DELETE FROM rewash_detail WHERE DocNo = '$DocDetaliRewash' AND ItemCode = '$itemCode'";
                mysqli_query($conn, $Sql_pass);

                $Sql_chkEmpty = "SELECT COUNT(DocNo) cntEmpty FROM rewash_detail WHERE DocNo = '$DocDetaliRewash'";
                $meQuery_chkEmpty = mysqli_query($conn, $Sql_chkEmpty);
                $Result_chkEmpty = mysqli_fetch_assoc($meQuery_chkEmpty);
                $cntEmpty = $Result_chkEmpty['cntEmpty'];
                if ($cntEmpty == 0) {
                    $Sql_pass = "DELETE FROM rewash WHERE DocNo = '$DocDetaliRewash'";
                    mysqli_query($conn, $Sql_pass);
                }
            }
            $Sql = "UPDATE clean SET IsCheckList = 1,IsStatus = 4 WHERE DocNo= '$pDocNo'";

            
        }else{ // IsCheckList = 2 some send claim
            $Sql = "UPDATE clean SET IsCheckList = 2,IsStatus = 3 WHERE DocNo= '$pDocNo'";
        }

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

    function save_item_stock($conn, $DATA) {
        $count = 0;
        $DocNo = $DATA["DocNo"];
        $HptCode = $_SESSION['HptCode'];

        $SqlD = "SELECT DepCode FROM department WHERE HptCode ='$HptCode' AND IsDefault = 1";
        $meQueryD = mysqli_query($conn,$SqlD);
        $ResultD = mysqli_fetch_assoc($meQueryD);
        $DepCode = $ResultD['DepCode'];
        
        $Sql = "SELECT COUNT(*) AS cntItem FROM qccheckpass WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $cntItem = $Result['cntItem'];

        $Sql = "SELECT ItemCode,Pass FROM qccheckpass WHERE DocNo = '$DocNo'";
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $ItemCode = $Result['ItemCode'];
            $return[$count]['ItemCode'] = $ItemCode;
            $Pass = $Result['Pass'];
            $return[$count]['Pass'] = $Pass;

            $SqlT = "SELECT TotalQty FROM item_stock WHERE ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
            $meQueryT = mysqli_query($conn,$SqlT);
            $ResultT = mysqli_fetch_assoc($meQueryT);
            $TotalQty = $ResultT['TotalQty'];
            $return[$count]['TotalQty'] = $TotalQty;
            $TotalQty = $TotalQty + $Pass;

            $SqlI = "UPDATE item_stock SET TotalQty  = $TotalQty WHERE ItemCode = '$ItemCode' AND DepCode = '$DepCode'";
            // $return[$count]['Sql'] = $SqlI;
            if (mysqli_query($conn, $SqlI)) {
                $return[$count]['count'] = $count;
                $count++;
            }
        }
        $return['count_end'] = $count;
        $return['cntItem'] = $cntItem;

        if ($cntItem == $count) {
            $return['status'] = "success";
            $return['form'] = "save_item_stock";
            echo json_encode($return);
            mysqli_close($conn);
            die;
        }
        else {
            $return['status'] = "failed";
            $return['form'] = "save_item_stock";
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
        else if ($DATA['STATUS'] == 'show_quantity') {
            show_quantity($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'save_checkpass') {
            save_checkpass($conn, $DATA);
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
        else if ($DATA['STATUS'] == 'save_checklist') {
            save_checklist($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'claim_detail') {
            claim_detail($conn, $DATA);
        }
        else if ($DATA['STATUS'] == 'show_claim_detail') {
            show_claim_detail($conn, $DATA);
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
        else if ($DATA['STATUS'] == 'save_item_stock') {
            save_item_stock($conn, $DATA);
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