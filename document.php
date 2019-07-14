<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:index.html");
    }
    $siteCode = $_GET['siteCode'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="shortcut icon" href="favicon.ico">
	<link rel="stylesheet" href="css/themes/default/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
	<script src="js/jquery.js"></script>
	<script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script src="dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="dist/css/sweetalert2.min.css">
    <script>
        $(document).ready(function (e) {
            load_doc();
        });

        function load_doc(){
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_doc'
            };
            senddata(JSON.stringify(data));
        }

        function show_process(DocNo){
            window.location.href='process.php?DocNo='+DocNo;
        }

        function confirm_doc(DocNo){
            swal({
                title: "ยืนยันการรับเอกสาร",
                text: "คุณได้รับเอกสารผ้าสกปรกนี้แล้วใช่หรือไม่",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                cancelButtonClass: "btn-danger",
                confirmButtonText: "ใช่",
                cancelButtonText: "ไม่ใช่",
                closeOnConfirm: true,
                closeOnCancel: true,
            }).then(result => {
                confirm_yes(DocNo);
            })
        }

        function confirm_yes(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'confirm_yes'
            };
            senddata(JSON.stringify(data));
        }

        function back(){
            window.location.href="main.php";
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
            var URL = 'process/document.php';
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
                    if (temp["form"] == 'load_doc') {

                        $("#HptName").text(temp[0]['HptName']);

                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var status_class = "";
                            var status_text = "";
                            var status_line = "";
                            var on_click = "";

                            if(temp[i]['IsProcess'] == 0 || temp[i]['IsProcess'] == null){
                                status_class = "status4";
                                status_text = "ไม่ทำงาน";
                                status_line = "StatusLine_4";
                            }
                            else if(temp[i]['IsProcess'] == 1){
                                status_class = "status3";
                                status_text = "กำลังดำเนินการ";
                                status_line = "StatusLine_3";
                            }
                            else if(temp[i]['IsProcess'] == 3){
                                status_class = "status2";
                                status_text = "เสร็จสิ้น";
                                status_line = "StatusLine_2";
                            }
                            else if(temp[i]['IsProcess'] == 2){
                                status_class = "status1";
                                status_text = "หยุดชั่วขณะ";
                                status_line = "StatusLine_1";
                            }

                            if(temp[i]['IsStatus'] > 0){
                                if(temp[i]['IsReceive'] == 0){
                                    on_click = "onclick='confirm_doc(\""+temp[i]['DocNo']+"\")'";
                                }
                                else if(temp[i]['IsReceive'] == 1){
                                    on_click = "onclick='show_process(\""+temp[i]['DocNo']+"\")'";
                                }

                                var Str = "<button "+on_click+" class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5'>";
                                    Str += "<div class='row justify-content-end align-items-center'><div class='card "+status_class+"'>"+status_text+"</div>";
                                    Str += "<img src='img/"+status_line+".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                    Str += "<div class='text-truncate font-weight-bold'>"+temp[i]['DocNo']+"</div><div class='font-weight-light'>"+temp[i]['DepName']+"</div></div></div></button>";

                                $("#document").append(Str);
                            }
                            
                        }
                    } 
                    else if(temp["form"] == 'confirm_yes'){
                        show_process(temp['DocNo']);
                    }
                    else if(temp["form"] == 'show_process'){
                        window.location.href='process.php?siteCode='+temp['siteCode'];
                    }
                    else if(temp["form"] == 'logout'){
                        window.location.href='index.html';
                    }
                } else if (temp['status'] == "failed") {
                    swal({
                    title: '',
                    text: temp['msg'],
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
            <title>Document</title>
            <a onclick="back()" class="footer-button-left ui-btn-left ui-btn ui-icon-carat-l ui-btn-icon-left ui-btn-inline ui-corner-all ui-mini">กลับ</a>
            <h1 class="ui-title" role="heading" aria-level="1"><?php echo $UserName?> : <?php echo $UserFName?></h1>
            <a onclick="logout(1)" class="ui-btn-right ui-btn ui-btn-b ui-icon-power ui-btn-icon-right ui-btn-inline ui-corner-all ui-mini">ออก</a>
        </header>
        <div data-role="content" style="font-family:sans-serif;">

            <div align="center" style="margin:1rem 0;"><img src="img/logo.png" width="220" height="45"/></div>
            <div class="text-center my-4"><h4 id="HptName" class="text-truncate"></h4></div>
            <div id="document">

            <!-- <button on_click="" class='btn btn-mylight btn-block' style='align-items: center !important;'>
                <div class="row">
                    <div class='my-col-5'>
                        <div class='row justify-content-end align-items-center'>        
                            <div class='card status1'>หยุดชั่วขณะ</div>
                            <img src='img/StatusLine_1.png' height='50'/>
                        </div>
                    </div>

                    <div class='my-col-7 text-left'>
                        <div class='text-truncate font-weight-bold'>9999999999999999</div>
                        <div class='font-weight-light'>Hospital / Department</div>
                    </div>
                </div>
            </button> -->

            </div>
        </div>

	</section>			
</body>
</html>