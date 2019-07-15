<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:../index.html");
    }
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="shortcut icon" href="../favicon.ico">
	<link rel="stylesheet" href="../css/themes/default/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
	<script src="../js/jquery.js"></script>
	<script src="../js/jquery.mobile-1.4.5.min.js"></script>
    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
    <script>
        function menu_click(num) {
            window.location.href='hospital.php?Menu='+num;
        }

        function logout(num){
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/menu.php';
            $.ajax({
                url: URL,
                dataType: 'text',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (result) {
                try {
                    var temp = $.parseJSON(result);
                } catch (e) {
                    console.log('Error#542-decode error');
                }

                if (temp["status"] == 'success') {
                    if(temp["form"] == 'logout'){
                        window.location.href='../index.html';
                    }
                } else if (temp['status'] == "failed") {
                    swal({
                    title: '',
                    text: "ไม่พบข้อมูลในโรงพยาบาล",
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
    <section data-role="page">

        <header data-role="header">
            <title>Select Site</title>
            <h1 class="ui-title" role="heading" aria-level="1"><?php echo $UserName?> : <?php echo $UserFName?></h1>
            <a onclick="logout(1)" class="ui-btn-right ui-btn ui-btn-b ui-icon-power ui-btn-icon-right ui-btn-inline ui-corner-all ui-mini">ออก</a>
        </header>
        <div data-role="content" style="font-family:sans-serif;">
            <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
            <div class="text-center my-4"><h4 class="text-truncate">Menu</h4></div>
            <div id="hospital"></div>
        </div>

        <div class="row px-4">
            <div class="my-col-menu">
                <button onclick="menu_click(1)" type="button" class="btn btn-mylight">
                    <img src="../img/laundry.png">
                    <div class="text-truncate">โรงงานซัก</div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(2)" type="button" class="btn btn-mylight">
                    <img src="../img/laundry.png">
                    <div class="text-truncate">ผ้าสะอาด</div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(3)" type="button" class="btn btn-mylight">
                    <img src="../img/tshirt.png">
                    <div class="text-truncate">ผ้าสกปรก</div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(4)" type="button" class="btn btn-mylight">
                    <img src="../img/QC.png">
                    <div class="text-truncate">QC</div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(5)" type="button" class="btn btn-mylight">
                    <img src="../img/Report.png">
                    <div class="text-truncate">รายงาน</div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(6)" type="button" class="btn btn-mylight">
                    <img src="../img/Report.png">
                    <div class="text-truncate">ตั้งค่า</div>
                </button>
            </div>
        </div>
	</section>			
</body>
</html>
