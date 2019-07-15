<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:../index.html");
    }
    $Menu = $_GET['Menu'];
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
        $(document).ready(function (e) {
            load_site();
        });

        function ImgToText(){
            var ocrad = "../img/0.png";
            var str = OCRAD(ocrad);
            alert(str);
        }

        function load_site(){
            var data = {
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function select_date(SiteCode){
            
        }

        function show_doc(SiteCode){
            var data = {
                'SiteCode': SiteCode,
                'STATUS': 'show_doc'
            };
            senddata(JSON.stringify(data));
        }

        function back(){
            window.location.href="menu.php";
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
            var URL = '../process/hospital.php';
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
                    if (temp["form"] == 'load_site') {
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var picture = temp[i]['picture'];
                            if(temp[i]['picture'] == null || temp[i]['picture'] == ""){
                                picture = "logo.png";
                            }

                            var Str = "<button onclick='select_date(\""+temp[i]['HptCode']+"\")' class='btn btn-mylight btn-block' style='align-items: center !important;'>";
                                Str += "<div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>";
                                Str += "<img class='hpt_img' src='../img/"+picture+"'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>";
                                Str += "<img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name'>"+temp[i]['HptName']+"</div></div></div></button>";

                            $("#hospital").append(Str);
                        }
                    } 
                    else if(temp["form"] == 'show_doc'){
                        var Menu = <?php echo $Menu;?>;
                        window.location.href='document.php?siteCode='+temp['siteCode']+'&Menu='+Menu;
                    }
                    else if(temp["form"] == 'logout'){
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
            <button onclick='back()' class='footer-button-left ui-btn-left ui-btn ui-icon-carat-l ui-btn-icon-left ui-btn-inline ui-corner-all ui-mini'>กลับ</button>
            <h1 class="ui-title" role="heading" aria-level="1"><?php echo $UserName?> : <?php echo $UserFName?></h1>
            <a onclick="logout(1)" class="ui-btn-right ui-btn ui-btn-b ui-icon-power ui-btn-icon-right ui-btn-inline ui-corner-all ui-mini">ออก</a>
        </header>
        <div data-role="content" style="font-family:sans-serif;">
            <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
            <div class="text-center my-4"><h4 class="text-truncate">All Hospital</h4></div>
            <div id="hospital"></div>
        </div>

	</section>			
</body>
</html>
