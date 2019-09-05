<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set('Asia/Bangkok');
require 'PHPMailer/PHPMailerAutoload.php';

$DocNo = $_POST["DocNo"];
$siteCode = $_POST["siteCode"];

$Sql = "SELECT FName,email FROM users WHERE HptCode = '$siteCode' AND Active_mail = 1";
$meQuery=mysqli_query($conn,$Sql);
$Result = mysqli_fetch_assoc($meQuery);

$email = $Result['email'];
$FName = $Result['FName'];
$return['email'] = $email;

$Sql = "SELECT WashDetail FROM process WHERE DocNo='$DocNo'";
$meQuery=mysqli_query($conn,$Sql);
$Result = mysqli_fetch_assoc($meQuery);
$WashDetail = $Result['WashDetail'];
$return['WashDetail'] = $WashDetail;

$Subject = "Problem detail of Wash process";
// build message body
$body = '
<html>
<body>
<br>
___________________________________________________________________<br>
<br>
Document : '.$DocNo.'<br>
Problem details : '.$WashDetail.'
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
    $return['status'] = "failed";
    $return['form'] = "sendmail";
    $return['msg'] = "Mailer Error: " . $mail->ErrorInfo;
    echo json_encode($return);
    mysqli_close($conn);
    die;
} else {
    $return['status'] = "success";
    $return['form'] = "sendmail";
    $return['msg'] = "Message sent!";
    echo json_encode($return);
    mysqli_close($conn);
    die;
}
?>