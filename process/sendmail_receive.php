<?php
session_start();
require '../connect/connect.php';
date_default_timezone_set('Asia/Bangkok');
require 'PHPMailer/PHPMailerAutoload.php';

$DocNo = $_POST["DocNo"];
$siteCode = $_POST["siteCode"];
$Arr_ItemName = $_POST["Arr_ItemName"];
$Receive_Qty = $_POST["Receive_Qty"];
$Total_Qty = $_POST["Total_Qty"];
$cnt_Arr = sizeof($Arr_ItemName, 0);
$dateTH = date("d ") . getTHmonthFromnum((int) date("m")) . " " . ((int) date("Y") + 543);
$dateEN = date("d F Y");
$count = 0;

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

$Sql = "SELECT EngPerfix,EngName,EngLName,ThPerfix,ThName,ThLName,email FROM users WHERE HptCode = '$siteCode' AND (PmID = 3 OR PmID = 5 OR PmID = 7)";
$meQuery = mysqli_query($conn, $Sql);
while ($Result = mysqli_fetch_assoc($meQuery)) {

  $email = $Result['email'];
  $FName = $Result['EngPerfix'].$Result['EngName']." ".$Result['EngLName'];


  $count++;

  $Subject = "Problem detail of Wash process";
  // build message body
  $body = "
        <html>
        <body>

        <hr style='margin:25px 0;'>
        <div style='width: 100%;text-align: center;'>
        <div style='margin-bottom:10px;'>Laundry : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptName . "</u>
         To : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacName . "</u></div>
        <div style='margin-bottom:10px;'>Document : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
        <div style='margin-bottom:10px;'>Date : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $dateEN . "</u></div>
        <table cellspacing='0' cellpadding='1' border='1' style='width:80%;margin:0 10%;text-align:center;'>
					<tbody>
						<tr>
							<th style='padding:10px 0;'>Catagory</th>
							<th>Unit From Hospital</th>
							<th>Unit From Laundry</th>
							<th>Result</th>
                        </tr>";
  foreach ($Arr_ItemName as $key => $val) {
    $Differ = $Total_Qty[$key] - $Receive_Qty[$key];
    $text_diff = $Differ;
    if ($Differ > 0) {
      $text_diff = "shot ".$Differ;
    }
    else if ($Differ < 0) {
      $text_diff = "over ".($Differ*-1);
    }
    $body .= "<tr>
							<td style='text-align:left;padding:10px 0 10px 10px;'>$val</td>
							<td>$Total_Qty[$key]</td>
							<td>$Receive_Qty[$key]</td>
							<td>$text_diff</td>
                        </tr>";
  }


  $body .= "</tbody>
				</table>
        
        <hr style='margin:25px 0;'>
        
        <div style='margin-bottom:10px;'>โรงพยาบาล : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $HptNameTH . "</u>
         ถึง โรงซัก : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $FacNameTH . "</u></div>
        <div style='margin-bottom:10px;'>เลขที่เอกสาร : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $DocNo . "</u></div>
        <div style='margin-bottom:10px;'>วันที่ : <u style='text-decoration: underline;text-decoration-style: dotted;margin:0 10px;'>" . $dateTH . "</u></div>
        <table cellspacing='0' cellpadding='1' border='1' style='width:80%;margin:0 10%;text-align:center;'>
					<tbody>
						<tr>
							<th style='padding:10px 0;'>รายการ</th>
							<th>จำนวนที่โรงพยาบาลส่ง</th>
							<th>จำนวนที่โรงซักรับ</th>
							<th>ผลต่าง</th>
                        </tr>";
        foreach ($Arr_ItemName as $key => $val) {
          $Differ = $Total_Qty[$key] - $Receive_Qty[$key];
          $text_diff = $Differ;
          if ($Differ > 0) {
            $text_diff = "ขาด ".$Differ;
          }
          else if ($Differ < 0) {
            $text_diff = "เกิน ".($Differ*-1);
          }
          $body .= "<tr>
                    <td style='text-align:left;padding:10px 0 10px 10px;'>$val</td>
                    <td>$Total_Qty[$key]</td>
                    <td>$Receive_Qty[$key]</td>
                    <td>$text_diff</td>
                              </tr>";
        }
        $body .= "</tbody>
        </table>
        </div>
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
  $mail->send();

  //$mail->addAttachment('images/phpmailer_mini.png');
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

function getTHmonthFromnum($month)
{
  $TH = '';
  switch ($month) {
    case '1':
      $TH = 'มกราคม';
      break;
    case '2':
      $TH = 'กุมภาพันธ์';
      break;
    case '3':
      $TH = 'มีนาคม';
      break;
    case '4':
      $TH = 'เมษายน';
      break;
    case '5':
      $TH = 'พฤษภาคม';
      break;
    case '6':
      $TH = 'มิถุนายน';
      break;
    case '7':
      $TH = 'กรกฎาคม';
      break;
    case '8':
      $TH = 'สิงหาคม';
      break;
    case '9':
      $TH = 'กันยายน';
      break;
    case '10':
      $TH = 'ตุลาคม';
      break;
    case '11':
      $TH = 'พฤศจิกายน';
      break;
    case '12':
      $TH = 'ธันวาคม';
      break;
  }
  return $TH;
}
