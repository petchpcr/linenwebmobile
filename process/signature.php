<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set('Asia/Bangkok');
// require 'PHPMailer/PHPMailerAutoload.php';

$DocNo = $_POST['DocNo'];
$From = $_POST['From'];
$SigCode = $_POST['SigCode'];

// $DocNo = "DTBHQ1908-00006";
// $From = "dirty";
// $SigCode = "45154";

// $return['get'] = $DocNo + "--" + $From + "--" + $SigCode;

// $Sql = "UPDATE process SET IsStatus = 4,Signature = '$SigCode' WHERE DocNo = '$DocNo'";
// mysqli_query($conn, $Sql);

// $return['Sql_up_process'] = $Sql;

// $Sql = "UPDATE $From SET IsStatus = 3,IsProcess = 7 WHERE DocNo = '$DocNo'";
// mysqli_query($conn, $Sql);

// $return['Sql_up_from'] = $Sql;

// $Sql = "SELECT IF(SendOverTime < 0, TRUE, FALSE) AS t,
//             TIME_TO_SEC(SUBSTRING(SendOverTime,2))/60 AS SendOverTime,
//             DATE_FORMAT(SendStartTime,'%H:%i') AS SendStartTime,
//             DATE_FORMAT(SendEndTime,'%H:%i') AS SendEndTime 
//             FROM process WHERE DocNo='$DocNo'";
// $meQuery = mysqli_query($conn, $Sql);
// $Result = mysqli_fetch_assoc($meQuery);
// $Time = $Result['t'];
// $SendStartTime = $Result['SendStartTime'];
// $SendEndTime = $Result['SendEndTime'];
// $SendOverTime = floor($Result['SendOverTime']);
// $return['Sql_overT'] = $Sql;
// $return['Time'] = $Time;

// if ($Time == 1) {
//   //============= SELECT FacCode AND HptCode =============
//   $Sql = "SELECT (SELECT FacCode 
//                     FROM dirty 
//                     WHERE DocNo = '$DocNo' 
//                     UNION ALL 
//                     SELECT FacCode 
//                     FROM repair_wash 
//                     WHERE DocNo = '$DocNo'
//                     UNION ALL 
//                     SELECT FacCode 
//                     FROM newlinentable 
//                     WHERE DocNo = '$DocNo'
//                     ) AS FacCode,

//                     (SELECT HptCode 
//                     FROM dirty 
//                     WHERE DocNo = '$DocNo' 
//                     UNION ALL 
//                     SELECT HptCode 
//                     FROM repair_wash 
//                     WHERE DocNo = '$DocNo'
//                     UNION ALL 
//                     SELECT FacCode 
//                     FROM newlinentable 
//                     WHERE DocNo = '$DocNo'
//                     ) AS HptCode";

//   $meQuery = mysqli_query($conn, $Sql);
//   $Result = mysqli_fetch_assoc($meQuery);
//   $FacCode = $Result['FacCode'];
//   $HptCode = $Result['HptCode'];

//   //============= SELECT FacCode AND HptCode =============
//   $Sql = "SELECT FacName,FacNameTH,HptName,HptNameTH 
//             FROM site,factory 
//             WHERE factory.FacCode = $FacCode 
//             AND site.HptCode = '$HptCode'";

//   $meQuery = mysqli_query($conn, $Sql);
//   $Result = mysqli_fetch_assoc($meQuery);
//   $FacName = $Result['FacName'];
//   $FacNameTH = $Result['FacNameTH'];
//   $HptName = $Result['HptName'];
//   $HptNameTH = $Result['HptNameTH'];

//   $return['Sql2'] = $Sql;
//   $return['HptName'] = $HptName;

//   $Sql = "SELECT SendTime FROM delivery_fac_nhealth WHERE HptCode = '$HptCode' AND FacCode = $FacCode";
//   $meQuery = mysqli_query($conn, $Sql);
//   $Result = mysqli_fetch_assoc($meQuery);
//   $SendTime = $Result['SendTime'];

//   //============= SELECT Email AND Name =============
//   $Sql = "SELECT EngPerfix,EngName,EngLName,ThPerfix,ThName,ThLName,email
//         FROM users
//         WHERE HptCode = (SELECT HptCode 
//                         FROM dirty 
//                         WHERE DocNo = '$DocNo' 

//                         UNION ALL 

//                         SELECT HptCode 
//                         FROM repair_wash 
//                         WHERE DocNo = '$DocNo'

//                         UNION ALL 

//                         SELECT HptCode 
//                         FROM newlinentable 
//                         WHERE DocNo = '$DocNo'
//                         )
//         AND (PmID = 3 OR PmID = 5 OR PmID = 7)";
//   $meQuery = mysqli_query($conn, $Sql);
//   while ($Result = mysqli_fetch_assoc($meQuery)) {
//     $return['Sql1'] = $Sql;

//     $email = $Result['email'];
//     $FName = $Result['EngPerfix'] . $Result['EngName'] . " " . $Result['EngLName'];
//     $return['email'] = $email;

//     //============= TEXT OF EMAIL =============
//     $Subject = "Delivery over time";
//     $body = "
//             <html>
//             <body>

//             <hr style='margin:25px 0;'>

//             <div style='margin-bottom:10px;'>Laundry : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacName . "</u>
//             To : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptName . "</u></div>
//             <div style='margin-bottom:10px;'>Document : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
//             <div style='margin-bottom:10px;'>Start Time Delivery : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 50px 0 10px;'>" . $SendStartTime . "</u>
//             Finish Time Delivery : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $SendEndTime . "</u></div>
//             <div style='margin-bottom:10px;'>Set Time : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 90px 0 10px;'>" . $SendTime . " Minute</u>
//             Over Time : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $SendOverTime . " Minute</u></div>

//             <hr style='margin:25px 0;'>

//             <div style='margin-bottom:10px;'>โรงซัก : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacNameTH . "</u>
//             ถึง โรงพยาบาล : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptNameTH . "</u></div>
//             <div style='margin-bottom:10px;'>เลขที่เอกสาร : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
//             <div style='margin-bottom:10px;'>เริ่มเดินทาง : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 100px 0 10px;'>" . $SendStartTime . " น.</u>
//             สิ้นสุดการเดินทาง : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $SendEndTime . " น.</u></div>
//             <div style='margin-bottom:10px;'>ระยะเวลาที่กำหนด : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 80px 0 10px;'>" . $SendTime . " นาที</u>
//             ระยะเวลาช้ากว่ากำหนด : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $SendOverTime . " นาที</u></div>

//             <hr style='margin:25px 0;'>

//             </body>
//             </html>
//             ";

//     $strTo = $email;
//     $strSubject = $Subject;
//     $strHeader = "Content-type: text/html; charset=UTF-8\r\n"; // or UTF-8 //
//     $strHeader .= "From: poseinttelligence@gmail.com (Pose Intelligence)";
//     $strMessage = $body;
//     $flgSend = @mail($strTo, $strSubject, $strMessage, $strHeader);  // @ = No Show Error //
//     if ($flgSend) {
//       $return['status'][$count] = "success";
//     } else {
//       $return['status'][$count] = "failed";
//     }

//     // $mail = new PHPMailer;
//     // $mail->CharSet = "UTF-8";
//     // $mail->isSMTP();
//     // $mail->SMTPDebug = 2;
//     // $mail->Debugoutput = 'html';
//     // $mail->Host = 'smtp.gmail.com';
//     // $mail->Port = 587;
//     // $mail->SMTPSecure = 'tls';
//     // $mail->SMTPAuth = true;
//     // $mail->Username = "poseinttelligence@gmail.com";
//     // $mail->Password = "pose6628";
//     // $mail->setFrom('poseinttelligence@gmail.com', 'Pose Intelligence');

//     // $mail->addAddress($email, $FName);
//     // $mail->Subject = $Subject;
//     // $mail->msgHTML($body);
//     // $mail->AltBody = 'This is a plain-text message body';
//     // //$mail->addAttachment('images/phpmailer_mini.png');
//     // $mail->send();
//   }
//   // if (!$mail->send()) {
//   //     $return['msg'] = "Mailer Error: " . $mail->ErrorInfo;
//   //     echo json_encode($return);
//   //     die;
//   // } else {
//   //     $return['msg'] = "Message sent!";
//   //     echo json_encode($return);
//   //     die;
//   // }
// }
