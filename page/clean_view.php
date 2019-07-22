<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:../index.html");
    }
    $siteCode = $_GET['siteCode'];
    $Menu = $_GET['Menu'];
    $DocNo = $_GET['DocNo'];
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
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'siteCode': siteCode,
                'DocNo': DocNo,
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function load_doc(){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'load_doc'
            };
            senddata(JSON.stringify(data));
        }

        function back(){
            var siteCode = '<?php echo $siteCode;?>';
            var Menu = <?php echo $Menu;?>;
            window.location.href='clean.php?siteCode='+siteCode+'&Menu='+Menu;
        }

        function logout(num){
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
        }
        function movetoAddItem(){
          var Userid = '<?php echo $Userid;?>';
          var DocNo = "<?php echo $DocNo?>";
          var siteCode = '<?php echo $siteCode;?>';
          var Menu = <?php echo $Menu;?>;
          var DepCode = $("#add_doc").data("depcode");
          window.location.href='add_items.php?siteCode='+siteCode+'&DocNo='+DocNo+'&Menu='+Menu+'&user='+Userid+'&DepCode='+DepCode;
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/clean_view.php';
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
                        var HosDep = "<b>โรงพยาบาล : </b>"+temp['HptName']+" / "+temp['DepName'];
                        $("#add_doc").attr("data-depcode",temp['DepCode']);
                        $("#HptName").html(HosDep);
                    }
                    else if (temp["form"] == 'load_doc') {
                        // var RefDocNo = "<b>เอกสารอ้างอิง : </b>"+temp['RefDocNo'];
                        // if(temp['RefDocNo'] == null || temp['RefDocNo'] == ""){
                        //     RefDocNo = "<b>เอกสารอ้างอิง : </b>";
                        // }
                        $("#RefDocNo").html(RefDocNo);
                        var FName = "<b>ผู้บันทึก : </b>"+temp['FName'];
                        $("#FName").html(FName);
                        var Date = "<b>วันที่ : </b>"+temp['xdate']+" <b>เวลา : </b>"+temp['xtime'];
                        $("#Date").html(Date);
                        var Weight = "<b>น้ำหนักรวม : </b>"+temp['Total']+" กิโลกรัม";
                        $("#Weight").html(Weight);
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var num = i+1;
                            var Str = "<tr><td><div class='row'>";
                                Str += "<div scope='row' class='col-5 d-flex align-items-center justify-content-center'>"+num+"</div>";
                                Str += "<div class='col-7'><div class='row'><div class='col-12 text-truncate font-weight-bold mb-1'>"+temp[i]['ItemName']+"</div>";
                                Str += "<div class='col-12 text-black-50 mb-1'>จำนวน "+temp[i]['Qty']+" / น้ำหนัก "+temp[i]['Weight']+" </div></div></div></div></td></tr>";

                            $("#item").append(Str);
                        }
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
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-10 col-sm-12 mb-3">
                <div id="HptName" class="text-truncate"></div>
                <div id="HptName" class="text-truncate"></div>
                <div id="RefDocNo"></div>
                <div id="FName"></div>
                <div id="Date"></div>
                <div id="Weight"></div>
            </div>
        </div>
        <div class="row justify-content-center px-3">
            <table class="table table-hover col-lg-9 col-md-10 col-sm-12">
                <thead>
                    <tr class="bg-primary text-white">
                    <th scope="col">
                        <div class="row">
                            <div class="col-5 text-center">สำดับ</div>
                            <div class="col-7 text-center">รายการ</div>
                        </div>
                    </th>
                    </tr>
                </thead>
                <tbody id="item">
                    <!-- <tr>
                    <td>
                        <div class="row">
                            <div scope="row" class="col-5 d-flex align-items-center justify-content-center">1</div>
                            <div class="col-7">
                                <div class="row">
                                    <div class="col-12 text-truncate font-weight-bold p-1">ผ้าเช็ดปาก</div>
                                    <div class="col-12 text-black-50 p-1">จำนวน 0 / น้ำหนัก 0 </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    </tr> -->
                </tbody>
            </table>
        </div>


        <div id="add_doc" data-depcode="" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
            <div class="col-lg-9 col-md-10 col-sm-12">

                <div class="row">
                    <div class="col-12">
                      <button class="btn btn-primary btn-block" type="button" onclick="movetoAddItem()">
                          <i class="fas fa-plus mr-1"></i>สร้างเอกสาร
                      </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

</body>
</html>
