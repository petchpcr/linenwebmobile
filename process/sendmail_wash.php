<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set('Asia/Bangkok');
require 'PHPMailer/PHPMailerAutoload.php';

$DocNo = $_POST["DocNo"];
$siteCode = $_POST["siteCode"];



$Sql = "SELECT WashDetail,DATE_FORMAT(WashEndTime,'%H:%i') AS EndWash FROM process WHERE DocNo='$DocNo'";
$meQuery = mysqli_query($conn, $Sql);
$Result = mysqli_fetch_assoc($meQuery);
$WashDetail = $Result['WashDetail'];
$Ctime = $Result['EndWash'];
$return['WashDetail'] = $WashDetail;

$Sql = "SELECT FacName,FacNameTH,HptName,HptNameTH 
        FROM site,factory 
        WHERE factory.FacCode = (SELECT FacCode 
                            FROM dirty 
                            WHERE DocNo = '$DocNo' 
                            UNION ALL 
                            SELECT FacCode 
                            FROM repair_wash 
                            WHERE DocNo = '$DocNo'
                            UNION ALL 
                            SELECT FacCode 
                            FROM newlinentable 
                            WHERE DocNo = '$DocNo'
                            )

        AND site.HptCode = (SELECT HptCode 
                            FROM dirty 
                            WHERE DocNo = '$DocNo' 
                            UNION ALL 
                            SELECT HptCode 
                            FROM repair_wash 
                            WHERE DocNo = '$DocNo'
                            UNION ALL 
                            SELECT HptCode 
                            FROM newlinentable 
                            WHERE DocNo = '$DocNo'
                            )";
$meQuery = mysqli_query($conn, $Sql);
$Result = mysqli_fetch_assoc($meQuery);
$FacName = $Result['FacName'];
$FacNameTH = $Result['FacNameTH'];
$HptName = $Result['HptName'];
$HptNameTH = $Result['HptNameTH'];

$Sql = "SELECT FName,email FROM users WHERE HptCode = '$siteCode' AND (PmID = 3 OR PmID = 5 OR PmID = 7)";
$meQuery = mysqli_query($conn, $Sql);
while ($Result = mysqli_fetch_assoc($meQuery)) {

    $email = $Result['email'];
    $FName = $Result['FName'];

    $Subject = "Problem detail of Wash process";
    // build message body
    $body = "
            <html>
            <body>

            <hr style='margin:25px 0;'>

            <div style='margin-bottom:10px;'>Laundry : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacName . "</u>
            To : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptName . "</u></div>
            <div style='margin-bottom:10px;'>Document : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
            <div style='margin-bottom:10px;'>Comment Time : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $Ctime . "</u></div>
            <div style='margin-bottom:10px;'>Problem details : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $WashDetail . "</u></div>
            
            <hr style='margin:25px 0;'>
            
            <div style='margin-bottom:10px;'>โรงซัก : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacNameTH . "</u>
            ถึง โรงพยาบาล : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptNameTH . "</u></div>
            <div style='margin-bottom:10px;'>เลขที่เอกสาร : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
            <div style='margin-bottom:10px;'>เวลาในการเริ่มกรอกรายละเอียด : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $Ctime . "</u></div>
            <div style='margin-bottom:10px;'>รายละเอียด : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $WashDetail . "</u></div>
            
            <hr style='margin:25px 0;'>

            </body>
            </html>
            ";


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
    $mail->send();
}
// if (!$mail->send()) {
//     $return['status'] = "failed";
//     $return['form'] = "sendmail";
//     $return['msg'] = "Mailer Error: " . $mail->ErrorInfo;
    echo json_encode($return);
    mysqli_close($conn);
    die;
// } else {
//     $return['status'] = "success";
//     $return['form'] = "sendmail";
//     $return['msg'] = "Message sent!";
//     echo json_encode($return);
//     mysqli_close($conn);
//     die;
// }
?>