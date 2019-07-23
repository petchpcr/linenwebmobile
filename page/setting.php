<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
    header("location:../index.html");
}

$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/setting_lang.xml');
$json = json_encode($xml);
$array = json_decode($json, TRUE);
$genxml = simplexml_load_file('../xml/Language/general_lang.xml');
$json = json_encode($genxml);
$genarray = json_decode($json, TRUE);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/gijgo.min.js" type="text/javascript"></script>
    <link href="../css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
    <script>
        $(document).ready(function(e) {
            load_site();
        });

        function back() {
            window.location.href = "menu.php";
        }

        function logout(num) {
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/setting.php';
            $.ajax({
                url: URL,
                dataType: 'text',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(result) {
                    try {
                        var temp = $.parseJSON(result);
                    } catch (e) {
                        console.log('Error#542-decode error');
                    }

                    if (temp["status"] == 'success') {
                        if (temp["form"] == 'load_site') {
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                var picture = temp[i]['picture'];
                                if (temp[i]['picture'] == null || temp[i]['picture'] == "") {
                                    picture = "logo.png";
                                }

                                var Str = "<button onclick='show_doc(\"" + temp[i]['HptCode'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'>";
                                Str += "<div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>";
                                Str += "<img class='hpt_img' src='../img/" + picture + "'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>";
                                Str += "<img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name'>" + temp[i]['HptName'] + "</div></div></div></button>";

                                $("#hospital").append(Str);
                            }
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        swal({
                            title: '',
                            text: '<?php $genarray['NotFoundHpt'][$language] ?>',
                            type: 'warning',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            showConfirmButton: false,
                            timer: 2000,
                            confirmButtonText: 'Error!!'
                        })
                    } else {
                        console.log(temp['msg']);
                    }
                }
            });
        }
    </script>
</head>

<body>
    <header data-role="header">
        <div class="head-bar d-flex justify-content-between">
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i>กลับ</button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php $genarray['logout'][$language] ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3" style="font-family:sans-serif;">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center my-4">
            <h4 class="text-truncate"><?php $genarray['AllHospital'][$language] ?></h4>
        </div>
        <div id="hospital"></div>
    </div>
</body>

</html>