<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:../index.html");
    }
    $Menu = $_GET['Menu'];
    $siteCode = $_GET['siteCode'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <script src="../js/jquery-3.3.1.min.js"></script>
    
	<link rel="shortcut icon" href="../favicon.ico">
	<link rel="stylesheet" href="../fontawesome/css/all.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <script src="../js/gijgo.min.js" type="text/javascript"></script>
    <link href="../css/gijgo.min.css" rel="stylesheet" type="text/css"/>
    
    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
    <script>
        $(document).ready(function (e) {
            load_site();
            load_doc();
        });

        function load_site(){
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function load_doc(){
            var search = $('#datepicker').val();
            var searchDate = new Date(search);
            var siteCode = "<?php echo $siteCode?>";
            var Menu = "<?php echo $Menu?>";
            var data = {
                'search': searchDate,
                'siteCode': siteCode,
                'Menu': Menu,
                'STATUS': 'load_doc'
            };
            senddata(JSON.stringify(data));
        }

        function show_process(DocNo){
            var Menu = <?php echo $Menu;?>;
            window.location.href='process.php?Menu='+Menu+'&DocNo='+DocNo;
        }

        function back(){
            var Menu = <?php echo $Menu;?>;
            window.location.href="hospital.php?Menu="+Menu;
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
            var URL = '../process/clean.php';
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
                    if(temp["form"] == 'load_site'){
                        $("#HptName").text(temp['HptName']);
                    }

                    else if (temp["form"] == 'load_doc') {
                        $(".btn.btn-mylight.btn-block").remove();
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var status_class = "";
                            var status_text = "";
                            var status_line = "";
                            var on_click = "";

                            if(temp[i]['IsStatus'] == 0 || temp[i]['IsStatus'] == null){
                                status_class = "status4";
                                status_text = "ไม่ทำงาน";
                                status_line = "StatusLine_4";
                            }
                            else if(temp[i]['IsStatus'] == 1){
                                status_class = "status2";
                                status_text = "เสร็จสิ้น";
                                status_line = "StatusLine_2";
                            }
                            
                            var Str = "<button "+on_click+" class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5'>";
                                Str += "<div class='row justify-content-end align-items-center'><div class='card "+status_class+"'>"+status_text+"</div>";
                                Str += "<img src='../img/"+status_line+".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                Str += "<div class='text-truncate font-weight-bold'>"+temp[i]['DocNo']+"</div><div class='font-weight-light'>"+temp[i]['DepName']+"</div></div></div></button>";

                            $("#document").append(Str);
                            
                        }
                    }
                    else if(temp["form"] == 'show_process'){
                        window.location.href='process.php?siteCode='+temp['siteCode'];
                    }
                    else if(temp["form"] == 'logout'){
                        window.location.href='../index.html';
                    }
                } else if (temp['status'] == "failed") {
                    if(temp["form"] == 'load_doc'){
                        $(".btn.btn-mylight.btn-block").remove();
                        swal({
                            title: '',
                            text: "ไม่พบข้อมูลในวันที่เลือก",
                            type: 'warning',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            showConfirmButton: false,
                            timer: 2000,
                            confirmButtonText: 'Data found'
                        })

                    } else {
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
                    }
                    
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
            <button  onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i>กลับ</button >
            <div class="head-text text-truncate align-self-center"><?php echo $UserName?> : <?php echo $UserFName?></div>
            <button  onclick="logout(1)" class="head-btn btn-dark" role="button">ออก<i class="fas fa-power-off ml-1"></i></button >
        </div>
    </header>
    <div class="px-3" style="font-family:sans-serif;">

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
        <div class="text-center my-4"><h4 id="HptName" class="text-truncate"></h4></div>
        <div id="document">
        <div class="d-flex justify-content-center mb-3">
            <input id="datepicker" class="text-truncate text-center" width="276" placeholder="เลือกวันที่สร้างเอกสาร"/>
            <button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button">ค้นหา</button>
        </div>

        <!-- <button on_click="" class='btn btn-block' style='align-items: center !important;'>
            <div class="row">
                <div class='my-col-5'>
                    <div class='row justify-content-end align-items-center'>        
                        <div class='card status1'>หยุดชั่วขณะ</div>
                        <img src='../img/StatusLine_1.png' height='50'/>
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

    <script>
        $('#datepicker').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
        });
    </script>

</body>
</html>