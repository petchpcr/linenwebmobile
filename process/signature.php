<?php
    session_start();
    require '../connect/connect.php';
    date_default_timezone_set('Asia/Bangkok');
    require 'PHPMailer/PHPMailerAutoload.php';
    
    $DocNo = $_POST['DocNo'];
    $From = $_POST['From'];
    $SigCode = $_POST['SigCode'];
    
    // $DocNo = "DTBHQ1908-00006";
    // $From = "dirty";
    // $SigCode = "45154";

    $Sql = "UPDATE process SET IsStatus = 4,Signature = '$SigCode' WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);

    $Sql = "UPDATE $From SET IsStatus = 3,IsProcess = 7 WHERE DocNo = '$DocNo'";
    mysqli_query($conn,$Sql);

    $Sql = "SELECT IF(SendOverTime<0, TRUE, FALSE) AS t,SendOverTime FROM process WHERE DocNo='$DocNo'";
    $meQuery=mysqli_query($conn,$Sql);
    $Result = mysqli_fetch_assoc($meQuery);
    $Time = $Result['t'];
    $SendOverTime = $Result['SendOverTime'];

   // echo json_encode($Time);

    if($Time==1){
        $Sql = "SELECT FName,email
        FROM users,department
        WHERE users.HptCode = department.HptCode
        AND department.DepCode = (SELECT DepCode 
                                FROM dirty 
                                WHERE DocNo = '$DocNo' 
                                UNION ALL 
                                SELECT DepCode 
                                FROM rewash 
                                WHERE DocNo = '$DocNo'
                                )
        AND users.Active_mail = 1";
        $meQuery=mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);

        $email = $Result['email'];
        $FName = $Result['FName'];

        $Sql = "SELECT FacName,HptName 
        FROM dirty,department,site,factory 
        WHERE DocNo='$DocNo' 
        AND dirty.DepCode = department.DepCode 
        AND factory.FacCode = dirty.FacCode 
        AND department.HptCode=site.HptCode";
        $meQuery=mysqli_query($conn,$Sql);
        $Result = mysqli_fetch_assoc($meQuery);
        $FacName = $Result['FacName'];
        $HptName = $Result['HptName'];
        
        $Subject = "Delivery over time";
        // build message body
        $body = '
        <html>
        <body>
        <br>
        ___________________________________________________________________<br>
        <br>
        Document : '.$DocNo.'<br>
        From : '.$FacName.' To : '.$HptName.'<br>
        Over time : '.$SendOverTime.'
        <br>___________________________________________________________________<br>
        <br>
        Thanks...<br>
        </body>
        </html>
        ';
    
        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = "poseinttelligence@gmail.com";
        $mail->Password = "pose6628";
        $mail->setFrom('poseinttelligence@gmail.com', 'Pose Intelligence');
    
        $mail->addAddress($email, $FName);
        $mail->Subject = $Subject;
        $mail->msgHTML($body);
        $mail->AltBody = 'This is a plain-text message body';
        //$mail->addAttachment('images/phpmailer_mini.png');
        if (!$mail->send()) {
            $return['msg'] = "Mailer Error: " . $mail->ErrorInfo;
            echo json_encode($return);
            die;
        } else {
            $return['msg'] = "Message sent!";
            echo json_encode($return);
            die;
        }
    }
?>