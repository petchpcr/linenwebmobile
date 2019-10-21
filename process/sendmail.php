<?php

date_default_timezone_set('Asia/Bangkok');
require 'PHPMailer/PHPMailerAutoload.php';

if (isset($_POST['DATA'])) {



    // ===================================================================================================================
    // =============================================== ไปดูที่ signature.php ===============================================
    // ===================================================================================================================




    // $data = $_POST['DATA'];
    // $DATA = json_decode(str_replace('\"', '"', $data), true);
    // $Docno = $DATA['Docno'];

    // $Sql = "SELECT IF(SendOverTime < 0, 'TRUE', 'FALSE'),SendOverTime AS t FROM process WHERE DocNo='$Docno'";
    // $meQuery = mysqli_query($conn, $Sql);
    // $Result = mysqli_fetch_assoc($meQuery);
    // $Time = $Result['t'];
    // $SendOverTime = $Result['SendOverTime'];

    // if ($Time == 1) {
    //     $Sql = "SELECT FName,email
    //     FROM users
    //     WHERE HptCode = (SELECT HptCode 
    //                     FROM dirty 
    //                     WHERE DocNo = '$Docno' 

    //                     UNION ALL 

    //                     SELECT HptCode 
    //                     FROM repair_wash 
    //                     WHERE DocNo = '$Docno'

    //                     UNION ALL 

    //                     SELECT HptCode 
    //                     FROM newlinentable 
    //                     WHERE DocNo = '$Docno'
    //                     )
    //     AND (PmID = 3 OR PmID = 5 OR PmID = 7)";
    //     $meQuery = mysqli_query($conn, $Sql);
    //     while ($Result = mysqli_fetch_assoc($meQuery)) {

    //         $email = $Result['email'];
    //         $FName = $Result['FName'];
    //         $Subject = "Delivery over time";

    //         // build message body
    //         $body = "
    //         <html>
    //         <body>
    //         <br>
    //         ___________________________________________________________________<br>
    //         Document : $Docno
    //         Over time : $SendOverTime
    //         SQL777777 : $Sql
    //         ___________________________________________________________________<br>
    //         <br>
    //         Thanks...<br>
    //         </body>
    //         </html>
    //         ";

    //         $mail = new PHPMailer;
    //         $mail->CharSet = "UTF-8";
    //         $mail->isSMTP();
    //         $mail->SMTPDebug = 2;
    //         $mail->Debugoutput = 'html';
    //         $mail->Host = 'smtp.gmail.com';
    //         $mail->Port = 587;
    //         $mail->SMTPSecure = 'tls';
    //         $mail->SMTPAuth = true;
    //         $mail->Username = "poseinttelligence@gmail.com";
    //         $mail->Password = "pose6628";
    //         $mail->setFrom('poseinttelligence@gmail.com', 'Pose Intelligence');

    //         $mail->addAddress($email, $FName);
    //         $mail->Subject = $Subject;
    //         $mail->msgHTML($body);
    //         $mail->AltBody = 'This is a plain-text message body';
    //         //$mail->addAttachment('images/phpmailer_mini.png');
    //         $mail->send();
    //     }
    //     // if (!$mail->send()) {
    //     //     $return['msg'] = "Mailer Error: " . $mail->ErrorInfo;
    //     //     echo json_encode($return);
    //     //     die;
    //     // } else {
    //     //     $return['msg'] = "Message sent!";
    //     echo json_encode($return);
    //     mysqli_close($conn);
    //     die;
    //     // }
    // }
}
