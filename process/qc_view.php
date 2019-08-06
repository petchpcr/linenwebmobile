<?php
    session_start();
    require '../connect/connect.php';
    require 'logout.php';

    function load_items($conn, $DATA){
        $count = 0;
        $DocNo = $DATA["DocNo"];
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

                AND     clean_detail.DocNo = '$DocNo'";

        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['ItemCode'] = $Result['ItemCode'];
            $return[$count]['ItemName'] = $Result['ItemName'];
            $return[$count]['UnitCode'] = $Result['UnitCode'];
            $return[$count]['Qty'] = $Result['Qty'];
            $return[$count]['Weight'] = $Result['Weight'];
            $return[$count]['IsCheckList'] = $Result['IsCheckList'];
            $count++;
        }

        $return['cnt'] = $count;
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
                        (SELECT pass FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Pass,
                        (SELECT fail FROM qccheckpass WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo') AS Fail 
                FROM clean_detail WHERE ItemCode = '$ItemCode' AND DocNo = '$DocNo'";
                $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)){
            $return['ItemName']	=  $Result['itemname'];
            $return['Qty']	=  $Result['Qty'];
            $return['Pass']	=  $Result['Pass'];
            $return['Fail']	=  $Result['Fail'];
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
        $Sql = "SELECT COUNT(ItemCode) AS cnt FROM qccheckpass WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $cnt = $Result['cnt'];
        $return['cnt'] = $cnt;

        if ($cnt > 0) {
            $Sql = "UPDATE qccheckpass SET Pass = $pass, Fail = $fail, QCDate = NOW() WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        }
        else {
            $Sql = "INSERT INTO	qccheckpass(DocNo,ItemCode,Pass,Fail,QCDate) VALUES ('$DocNo','$ItemCode','$pass','$fail',NOW())";
        }

        if (mysqli_query($conn, $Sql)) {
            if ($fail == 0) {
                $Sql = "UPDATE clean_detail SET IsCheckList = 0 WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
                mysqli_query($conn, $Sql);
    
                $Sql = "DELETE FROM qcchecklist WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
                mysqli_query($conn, $Sql);

                $return['unfail'] = 1;
            }
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

        $claim = 0;
        $rewash = 0;

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

            $Sql = "SELECT CopeMethod FROM qcquestion WHERE CodeId = '$arr_question[$i]'";
            $meQuery = mysqli_query($conn,$Sql);
            while ($Result = mysqli_fetch_assoc($meQuery)){
                $CopeMethod = $Result['CopeMethod'];
            }

            if ($CopeMethod == 1) { // Claim
                $claim = 1;
            }
            else if ($CopeMethod == 2) { // Rewash
                $rewash = 1;
            }
        }

        $checklist = 0;
        if ($claim == 1 && $rewash == 1) { // Claim & Rewash
            $checklist = 3;
        }
        else if ($claim == 0 && $rewash == 1) { // Rewash
            $checklist = 2;
        }
        else if ($claim == 1 && $rewash == 0) { // Claim
            $checklist = 1;
        }
        
        $Sql = "UPDATE clean_detail SET IsCheckList = $checklist WHERE DocNo = '$DocNo' AND ItemCode = '$ItemCode'";
        mysqli_query($conn, $Sql);
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
        $count = 0;

        $Sql = "SELECT qcquestion.Question,qcchecklist.Qty FROM qcchecklist 
                INNER JOIN qcquestion ON qcchecklist.QuestionId = qcquestion.CodeId 
                WHERE DocNo= '$DocNo' AND ItemCode = '$ItemCode'";
        $return['Sql'] = $Sql;
        $meQuery = mysqli_query($conn, $Sql);
        while ($Result = mysqli_fetch_assoc($meQuery)) {
            $return[$count]['Question'] = $Result['Question'];
            $return[$count]['Qty'] = $Result['Qty'];
            $count++;
        }
        $return['cnt'] = $count;
        
        if ($count > 0) {
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
            $weight = $Result['Weight'];
            $CheckList = $Result['IsCheckList'];

            $Sql_qty = "SELECT Fail FROM qccheckpass WHERE DocNo = '$cleanDocNo' AND ItemCode = '$itemCode'";
            $meQuery_qty = mysqli_query($conn, $Sql_qty);
            $Result_qty = mysqli_fetch_assoc($meQuery_qty);
            $qty = $Result_qty['Fail'];

            // SELECT เพื่อเอา FacCode
            $Sql_ref = "SELECT RefDocNo FROM clean WHERE DocNo = '$cleanDocNo'";
            $meQuery_ref = mysqli_query($conn, $Sql_ref);
            $Result_ref = mysqli_fetch_assoc($meQuery_ref);
            $ref = $Result_ref['RefDocNo'];

            // SELECT เพื่อเอา FacCode
            $Sql_fac = "SELECT FacCode FROM dirty WHERE dirty.DocNo = '$ref'
                        UNION ALL
                        SELECT FacCode FROM rewash WHERE rewash.DocNo = '$ref'";
            $return[$count]['Sql Fac'] = $Sql_fac;
            $meQuery_fac = mysqli_query($conn, $Sql_fac);
            $Result_fac = mysqli_fetch_assoc($meQuery_fac);
            $fac = $Result_fac['FacCode'];
            $return['Sql 777'] = $Sql_fac;

            if ($CheckList == 0) { // -------------- Pass -------------- 
                
            }
            else if ($CheckList == 1) { // -------------- Claim -------------- 
                // เช็คว่าเคยสร้างแล้วรึป่าว
                $Sql_check = "SELECT COUNT(RefDocNo) AS cntCheck FROM claim WHERE RefDocNo = '$cleanDocNo'";
                $meQuery_check = mysqli_query($conn, $Sql_check);
                $Result_check = mysqli_fetch_assoc($meQuery_check);
                $Check = $Result_check['cntCheck'];

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

                if ($count_claim == 1) {
                    if ($Check == 0) {
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
                $Sql_claim = "SELECT          COUNT(*) as Cnt
                            FROM            claim_detail

                            WHERE           claim_detail.DocNo = '$DocNo'
                            AND             claim_detail.ItemCode = '$itemCode'";

                $meQuery2 = mysqli_query($conn, $Sql_claim);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $chkUpdate = $Result['Cnt'];
                }

                if ($chkUpdate == 0) {
                    $Sql_claim = "    INSERT INTO claim_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                            VALUES ('$DocNo','$itemCode',$unitCode,1,$qty,0,$weight,0,0,0)";
                        
                    mysqli_query($conn, $Sql_claim);
                } else {
                    $Sql_claim = "    UPDATE claim_detail SET Qty1 = $qty,Qty2 = 0,Weight = $weight 

                                WHERE       DocNo = '$DocNo'
                                AND         ItemCode = '$itemCode'";

                    mysqli_query($conn, $Sql_claim);
                }
            }
            else if ($CheckList == 2) { // -------------- Rewash -------------- 
                // เช็คว่าเคยสร้างแล้วรึป่าว
                $Sql_check = "SELECT COUNT(RefDocNo) AS cntCheck FROM rewash WHERE RefDocNo = '$cleanDocNo'";
                $meQuery_check = mysqli_query($conn, $Sql_check);
                $Result_check = mysqli_fetch_assoc($meQuery_check);
                $Check = $Result_check['cntCheck'];
                
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
                    if ($Check == 0) {
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
                $Sql_rewash = "SELECT          COUNT(*) as Cnt FROM rewash_detail

                                WHERE           rewash_detail.DocNo = '$DocNo'
                                AND             rewash_detail.ItemCode = '$itemCode'";

                $meQuery2 = mysqli_query($conn, $Sql_rewash);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $chkUpdate = $Result['Cnt'];
                }

                if ($chkUpdate == 0) {
                    $Sql_rewash = "    INSERT INTO rewash_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                            VALUES ('$DocNo','$itemCode',$unitCode,1,$qty,0,$weight,0,0,0)";
                        
                    mysqli_query($conn, $Sql_rewash);
                } else {
                    $Sql_rewash = "UPDATE rewash_detail SET Qty1 = $qty,Qty2 = 0,Weight = $weight 

                                    WHERE       DocNo = '$DocNo'
                                    AND         ItemCode = '$itemCode'";

                    mysqli_query($conn, $Sql_rewash);
                }
            }
            else if ($CheckList == 3) { // -------------- Claim & Rewash -------------- 
                // เช็คว่าเคยสร้างแล้วรึป่าว
                $Sql_check = "SELECT COUNT(RefDocNo) AS chkClaim FROM claim WHERE RefDocNo = '$cleanDocNo'";
                $meQuery_check = mysqli_query($conn, $Sql_check);
                $Result_check = mysqli_fetch_assoc($meQuery_check);
                $chkClaim = $Result_check['chkClaim'];

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

                if ($count_claim == 1) {
                    if ($chkClaim == 0) {
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
                $Sql_claim = "SELECT          COUNT(*) as Cnt
                            FROM            claim_detail

                            WHERE           claim_detail.DocNo = '$DocNo'
                            AND             claim_detail.ItemCode = '$itemCode'";

                $meQuery2 = mysqli_query($conn, $Sql_claim);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $chkUpdate = $Result['Cnt'];
                }

                if ($chkUpdate == 0) {
                    $Sql_claim = "    INSERT INTO claim_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                            VALUES ('$DocNo','$itemCode',$unitCode,1,$qty,0,$weight,0,0,0)";
                        
                    mysqli_query($conn, $Sql_claim);
                } else {
                    $Sql_claim = "    UPDATE claim_detail SET Qty1 = $qty,Qty2 = 0,Weight = $weight 

                                WHERE       DocNo = '$DocNo'
                                AND         ItemCode = '$itemCode'";

                    mysqli_query($conn, $Sql_claim);
                }

                // เช็คว่าเคยสร้างแล้วรึป่าว
                $Sql_check = "SELECT COUNT(RefDocNo) AS chkRewash FROM claim WHERE RefDocNo = '$cleanDocNo'";
                $meQuery_check = mysqli_query($conn, $Sql_check);
                $Result_check = mysqli_fetch_assoc($meQuery_check);
                $chkRewash = $Result_check['chkRewash'];

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
                $Sql_rewash = "SELECT          COUNT(*) as Cnt FROM rewash_detail

                                WHERE           rewash_detail.DocNo = '$DocNo'
                                AND             rewash_detail.ItemCode = '$itemCode'";

                $meQuery2 = mysqli_query($conn, $Sql_rewash);
                while ($Result = mysqli_fetch_assoc($meQuery2)) {
                    $chkUpdate = $Result['Cnt'];
                }

                if ($chkUpdate == 0) {
                    $Sql_rewash = "    INSERT INTO rewash_detail(DocNo,ItemCode,UnitCode1,UnitCode2,Qty1,Qty2,Weight,IsCancel,Price,Total)
                                            VALUES ('$DocNo','$itemCode',$unitCode,1,$qty,0,$weight,0,0,0)";
                        
                    mysqli_query($conn, $Sql_rewash);
                } else {
                    $Sql_rewash = "UPDATE rewash_detail SET Qty1 = $qty,Qty2 = 0,Weight = $weight 

                                    WHERE       DocNo = '$DocNo'
                                    AND         ItemCode = '$itemCode'";

                    mysqli_query($conn, $Sql_rewash);
                }
            }
            $count++;
        }
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

        if($pNum==$itemNum){

        //IsCheckList = 1 QC pass all  
            $Sql = "UPDATE clean SET IsCheckList = 1 WHERE DocNo= '$pDocNo'";

        //IsCheckList = 2 some send claim
        }else{
            $Sql = "UPDATE clean SET IsCheckList = 2 WHERE DocNo= '$pDocNo'";
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