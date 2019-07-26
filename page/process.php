<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    $ShowSign = "";
    if($Userid==""){
      header("location:../index.html");
    }
    $Menu = $_GET['Menu'];
    $siteCode = $_GET['siteCode'];    
    $DocNo = $_GET['DocNo'];
    $language = $_SESSION['lang'];
    $xml = simplexml_load_file('../xml/Language/fac_process_lang.xml');
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
    <title><?php echo $genarray['titlefactory'][$language].$array['title'][$language];?></title>

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
            load_process();
        });

        function countdown(){
            var status = $("#h_status").text();
            var stop = $("#hw_stop").text();
            var cdnWash = new Date($("#hw_end").text());
            var cur_date = new Date();
            // cur_date.format("YYYY-MM-DD HH:mm");
            
            // alert("Now : "+cur_date);
            // alert("Status : "+status);
            if(status == 1 || status == 0 || status == "" || status == null){ // สถานะไม่ได้ทำอะไร หรือ กำลังซัก
                // alert("Stop : "+stop);
                if(stop != "" || stop != null){ // ไม่ได้หยุดเวลาไว้
                    // alert("End Time : "+cdnWash);
                    if(cdnWash != "" || cdnWash != null){ // มีเวลาสิ้นสุด(เคยกดเริ่มไปแล้ว)
                        if(cur_date < cdnWash){ //ถ้ายังซักไม่เสร็จ
                            var differ = cdnWash-cur_date;
                            // alert("Differ : "+differ);
                            var ms = differ % 1000;
                            differ = (differ - ms) / 1000;
                            var secs = differ % 60;
                            differ = (differ - secs) / 60;
                            var mins = differ % 60;
                            var hrs = (differ - mins) / 60;

                            if(secs < 10){secs = "0" + secs;}
                            if(mins < 10){mins = "0" + mins;}
                            if(hrs < 10){hrs = "0" + hrs;}

                            var countdown = hrs + ':' + mins + ':' + secs;
                            $("#countdown").text(countdown);
                            setTimeout('countdown()', 1000 );
                        }
                        else if(cur_date >= cdnWash) { // ถ้าซักเสร็จแล้ว
                            var DocNo = "<?php echo $DocNo?>";
                            auto_end_wash(DocNo);
                        }
                        else{
                            setTimeout('countdown()', 1000 );
                        }
                    }
                    else{
                        setTimeout('countdown()', 1000 );
                    }
                }
                else{
                    setTimeout('countdown()', 1000 );
                }
                
            }
            
        }

        function load_process(){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'load_process'
            };
            senddata(JSON.stringify(data));
        }

        function insert_process(){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'insert_process'
            };
            senddata(JSON.stringify(data));
        }

        function start_wash(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'start_wash'
            };
            senddata(JSON.stringify(data));
        }

        function set_end_wash(W_End){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'W_End': W_End,
                'DocNo': DocNo,
                'STATUS': 'set_end_wash'
            };
            senddata(JSON.stringify(data));
        }

        function stop_wash(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'stop_wash'
            };
            senddata(JSON.stringify(data));
        }
        
        function do_end_wash(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'do_end_wash'
            };
            senddata(JSON.stringify(data));
        }

        function auto_end_wash(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'auto_end_wash'
            };
            senddata(JSON.stringify(data));
        }

        function start_pack(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'start_pack'
            };
            senddata(JSON.stringify(data));
        }

        function end_pack(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'end_pack'
            };
            senddata(JSON.stringify(data));
        }

        function start_send(DocNo){
            var data = {
                'DocNo': DocNo,
                'STATUS': 'start_send'
            };
            senddata(JSON.stringify(data));
        }

        function end_send(DocNo){
            var siteCode = '<?php echo $siteCode;?>';
            var data = {
                'siteCode': siteCode,
                'DocNo': DocNo,
                'STATUS': 'end_send'
            };
            senddata(JSON.stringify(data));
        }

        function back(site){
            var Menu = '<?php echo $Menu;?>';
            window.location.href='dirty.php?Menu='+Menu+'&siteCode='+site;
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
            var URL = '../process/process.php';
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
                        if (temp["form"] == 'load_process') {
                            $(".head-btn.btn-light").remove();
                            var Back = "<button onclick='back(\""+temp['HptCode']+"\")' class='head-btn btn-light'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button>";
                            $("#user").before(Back);
                            $("#h_status").text("");
                            $("#h_status").text(temp['IsStatus']);
                            if(temp['IsStatus'] == 0 || temp['IsStatus'] == null){ //-----ยังไม่ได้ทำอะไร
                                $("#W_Status").attr("src","../img/Status_4.png");
                                $("#P_Status").attr("src","../img/Status_4.png");
                                $("#S_Status").attr("src","../img/Status_4.png");
                                $("#W_Status_text").text("No Process");
                                $("#P_Status_text").text("No Process");
                                $("#S_Status_text").text("No Process");
                                $("#W_Use_text").hide();
                                $("#P_Use_text").hide();
                                $("#S_Use_text").hide();
                                $("#W_Sum_btn").show();
                                $("#P_Sum_btn").hide();
                                $("#S_Sum_btn").hide();
                                $("#W_Start").text("--:--:--");
                                $("#countdown").text("--:--:--");
                                $("#W_End").text("--:--:--");
                                $("#P_Start").text("--:--:--");
                                $("#P_End").text("--:--:--");
                                $("#S_Start").text("--:--:--");
                                $("#S_End").text("--:--:--");

                                $("#W_First_btn").show();
                                $("#W_Start_btn").hide();
                                $("#W_Stop_btn").hide();
                                $("#W_End_btn").hide();
                            }
                            else if(temp['IsStatus'] == 1){ //-----กำลังซัก
                                $("#W_Status").attr("src","../img/Status_1.png");
                                $("#P_Status").attr("src","../img/Status_4.png");
                                $("#S_Status").attr("src","../img/Status_4.png");
                                $("#W_Status_text").text("Wait Process");
                                $("#P_Status_text").text("No Process");
                                $("#S_Status_text").text("No Process");
                                $("#W_Use_text").hide();
                                $("#P_Use_text").hide();
                                $("#S_Use_text").hide();
                                $("#W_First_btn").remove();
                                $("#W_Sum_btn").show();
                                $("#P_Sum_btn").hide();
                                $("#S_Sum_btn").hide();
                                $("#countdown").text("00:00:00");
                                $("#P_Start").text("--:--:--");
                                $("#P_End").text("--:--:--");
                                $("#S_Start").text("--:--:--");
                                $("#S_End").text("--:--:--");

                                var W_Start = new Date(temp['WashStartTime']);
                                var W_End = new Date(temp['WashEndTime']);
                                $("#W_Start").text(W_Start.toLocaleTimeString()); 
                                $("#W_End").text(W_End.toLocaleTimeString());  
                                $("#hw_start").text(temp['WashStartTime']);
                                $("#hw_end").text(temp['WashEndTime']);

                                if(temp['IsStop'] == 1){ // ถ้าหยุด
                                    $("#hw_stop").text(temp['WashStopTime']);
                                    $("#hw_stop").show();
                                    var W_Stop = new Date(temp['WashStopTime']);
                                    var differ = W_End-W_Stop;

                                    var ms = differ % 1000;
                                    differ = (differ - ms) / 1000;
                                    var secs = differ % 60;
                                    differ = (differ - secs) / 60;
                                    var mins = differ % 60;
                                    var hrs = (differ - mins) / 60;

                                    if(secs < 10){secs = "0" + secs;}
                                    if(mins < 10){mins = "0" + mins;}
                                    if(hrs < 10){hrs = "0" + hrs;}

                                    var countdown = hrs + ':' + mins + ':' + secs;
                                    $("#show_stop").text(countdown);
                                    $("#W_End").text("--:--:--");

                                    $("#W_Stop_btn").hide();
                                    $("#W_Start_btn").show();
                                    $("#W_Status").attr("src","../img/Status_2.png");
                                    $("#W_Status_text").text("Stop Process");
                                    $("#countdown").hide();
                                    $("#show_stop").show();
                                }else{
                                    $("#hw_stop").text(null);
                                    $("#hw_stop").hide();
                                    $("#countdown").show();
                                    $("#show_stop").hide();
                                    if(temp['WashStartTime'] == null){ // ถ้ากดเริ่มครั้งแรก
                                        $("#W_Stop_btn").hide();
                                        $("#W_Start_btn").show();
                                        $("#W_Status").attr("src","../img/Status_4.png");
                                        $("#W_Status_text").text("No Process");
                                    }else{                              // ถ้าเคยกดเริ่มแล้ว
                                        $("#W_Stop_btn").show();
                                        $("#W_Start_btn").hide();
                                        $("#W_End_btn").show();
                                        $("#W_Status").attr("src","../img/Status_1.png");
                                        $("#W_Status_text").text("Wait Process");
                                    }
                                }
                                setTimeout( 'countdown()', 1000 );
                            }
                            else if(temp['IsStatus'] == 2){ //-----กำลังแพคของ
                                $("#W_Status").attr("src","../img/Status_3.png");
                                $("#P_Status").attr("src","../img/Status_1.png");  
                                $("#S_Status").attr("src","../img/Status_4.png");
                                $("#W_Status_text").text("Success Process");
                                $("#P_Status_text").text("Wait Process");
                                $("#S_Status_text").text("No Process");
                                $("#P_Use_text").hide();
                                $("#S_Use_text").hide();
                                $("#W_Sum_btn").remove();
                                $("#P_Sum_btn").show();
                                $("#S_Sum_btn").hide();
                                $("#cnd").remove();
                                $("#W_Use_text").show();
                                $("#S_Start").text("--:--:--");
                                $("#S_End").text("--:--:--");

                                var W_Start = new Date(temp['WashStartTime']);
                                var W_End = new Date(temp['WashEndTime']);
                                
                                $("#W_Use").text(temp['WashUseTime']+" นาที");
                                $("#W_Start").text(W_Start.toLocaleTimeString());
                                $("#W_End").text(W_End.toLocaleTimeString());

                                if(temp['PackStartTime'] == null){ // ถ้ากดเริ่มครั้งแรก
                                    $("#P_Start_btn").show();
                                    $("#P_End_btn").hide();
                                    $("#P_Start").text("--:--:--");
                                    $("#P_End").text("--:--:--");
                                }
                                else if(temp['PackStartTime'] != null){ // ถ้าเคยกดเริ่มแล้ว
                                    $("#P_Start_btn").hide();
                                    $("#P_End_btn").show();
                                    var P_Start = new Date(temp['PackStartTime']);
                                    $("#hp_start").text(temp['PackStartTime']);
                                    $("#P_Start").text(P_Start.toLocaleTimeString());
                                    $("#P_End").text("--:--:--");
                                }
                            }
                            else if(temp['IsStatus'] == 3){ //-----กำลังขนส่ง
                                $("#W_Status").attr("src","../img/Status_3.png");
                                $("#P_Status").attr("src","../img/Status_3.png");
                                $("#S_Status").attr("src","../img/Status_1.png");
                                $("#W_Status_text").text("Success Process");
                                $("#P_Status_text").text("Success Process");
                                $("#S_Status_text").text("Wait Process");
                                $("#P_Start_text").removeClass("col-lg-6");
                                $("#P_End_text").removeClass("col-lg-6");
                                $("#P_Start_text").addClass("col-lg-4");
                                $("#P_End_text").addClass("col-lg-4");
                                $("#P_Use_text").show();
                                $("#S_Use_text").hide();
                                $("#W_Sum_btn").remove();
                                $("#P_Sum_btn").remove();
                                $("#S_Sum_btn").show();
                                $("#cnd").remove();
                                $("#W_Use_text").show();
                                
                                var W_Start = new Date(temp['WashStartTime']);
                                var W_End = new Date(temp['WashEndTime']);
                                var P_Start = new Date(temp['PackStartTime']);
                                var P_End = new Date(temp['PackEndTime']);
                                
                                $("#W_Use").text(temp['WashUseTime']+" นาที");
                                $("#P_Use").text(temp['PackUseTime']+" นาที");
                                $("#W_Start").text(W_Start.toLocaleTimeString());
                                $("#W_End").text(W_End.toLocaleTimeString());
                                $("#P_Start").text(P_Start.toLocaleTimeString());
                                $("#P_End").text(P_End.toLocaleTimeString());

                                
                                if(temp['SendStartTime'] == null){ // ถ้ากดเริ่มครั้งแรก
                                    $("#S_Start_btn").show();
                                    $("#S_End_btn").hide();
                                    $("#S_Start").text("--:--:--");
                                    $("#S_End").text("--:--:--");
                                }
                                else if(temp['SendStartTime'] != null){ // ถ้าเคยกดเริ่มแล้ว
                                    $("#S_Start_btn").hide();
                                    $("#S_End_btn").show();
                                    var S_Start = new Date(temp['SendStartTime']);
                                    $("#hs_start").text(temp['SendStartTime']);
                                    $("#S_Start").text(S_Start.toLocaleTimeString());
                                    $("#S_End").text("--:--:--");
                                }
                            }
                            else if(temp['IsStatus'] == 4){ //-----เสร็จสิ้น

                                if(temp['Signature'] == null || temp['Signature'] == ""){
                                    swal({
                                        title: "ยืนยันการขนส่ง",
                                        text: "การขนส่งเสร็จสิ้น โปรดเซ็นต์ชื่อเพื่อยืนยัน",
                                        type: "warning",
                                        showCancelButton: false,
                                        confirmButtonClass: "btn-success",
                                        cancelButtonClass: "btn-danger",
                                        confirmButtonText: "ตกลง",
                                        cancelButtonText: "ไม่ใช่",
                                        closeOnConfirm: true,
                                        closeOnCancel: true,
                                    }).then(result => {
                                        var Menu = "<?php echo $Menu?>";
                                        window.location.href='signature.php?Menu='+Menu+'&DocNo='+temp['DocNo'];
                                    })
                                }
                                else{
                                    var ck = temp['Signature'];
                                    $("#show_sign").html(ck);
                                    $("#sign_zone").removeAttr("hidden");
                                }

                                $("#W_Sum_btn").remove();
                                $("#P_Sum_btn").remove();
                                $("#S_Sum_btn").remove();
                                $("#W_Status").attr("src","../img/Status_3.png");
                                $("#P_Status").attr("src","../img/Status_3.png");
                                $("#S_Status").attr("src","../img/Status_3.png");
                                $("#W_Status_text").text("Success Process");
                                $("#P_Status_text").text("Success Process");
                                $("#S_Status_text").text("Success Process");
                                $("#P_Start_text").removeClass("col-lg-6");
                                $("#P_End_text").removeClass("col-lg-6");
                                $("#P_Start_text").addClass("col-lg-4");
                                $("#P_End_text").addClass("col-lg-4");
                                $("#S_Start_text").removeClass("col-lg-6");
                                $("#S_End_text").removeClass("col-lg-6");
                                $("#S_Start_text").addClass("col-lg-4");
                                $("#S_End_text").addClass("col-lg-4");
                                $("#cnd").remove();
                                $("#W_Use_text").show();
                                $("#P_Use_text").show();
                                $("#S_Use_text").show();

                                var W_Start = new Date(temp['WashStartTime']);
                                var W_End = new Date(temp['WashEndTime']);
                                var P_Start = new Date(temp['PackStartTime']);
                                var P_End = new Date(temp['PackEndTime']);
                                var S_Start = new Date(temp['SendStartTime']);
                                var S_End = new Date(temp['SendEndTime']);
                                var S_Over = temp['SendOverTime'].substring(0, 1);

                                if (S_Over == '-') {
                                    $("#S_Head_use").text("เกินเวลา");
                                    $("#S_Head_use").css("color","red");
                                    $("#S_Use").css("color","red");
                                    $("#S_Use").text(temp['SendOverTime'].substring(1)+" นาที");

                                } else {
                                    $("#S_Head_use").text("ใช้เวลา");
                                    $("#S_Use").text(temp['SendUseTime']+" นาที");
                                }

                                $("#W_Use").text(temp['WashUseTime']+" นาที");
                                $("#P_Use").text(temp['PackUseTime']+" นาที");
                                $("#W_Start").text(W_Start.toLocaleTimeString());
                                $("#W_End").text(W_End.toLocaleTimeString());
                                $("#P_Start").text(P_Start.toLocaleTimeString());
                                $("#P_End").text(P_End.toLocaleTimeString());
                                $("#S_Start").text(S_Start.toLocaleTimeString());
                                $("#S_End").text(S_End.toLocaleTimeString());
                                
                            }
                        }
                        else if (temp["form"] == 'insert_process'){
                            load_process();
                        }
                        else if (temp["form"] == 'start_wash'){
                            if(temp['WashStopTime'] != null){ // ถ้ากดเริ่ม หลังจากหยุด
                                var stopTime = new Date(temp['WashStopTime']);
                                var endTime = new Date(temp['WashEndTime']);
                                var differ = endTime-stopTime;
                                var current = new Date();
                                current.setMilliseconds(current.getMilliseconds() + differ);
                                set_end_wash(current);
                            }
                            else{
                                var millitime = temp['processt']*60000;
                                var W_End = new Date(temp['WashStartTime']);
                                W_End.setMilliseconds(W_End.getMilliseconds() + millitime);
                                set_end_wash(W_End);
                            }
                        }
                        else if (temp["form"] == 'set_end_wash'){
                            load_process();
                        }
                        else if (temp["form"] == 'stop_wash'){
                            load_process();
                        }
                        else if (temp["form"] == 'do_end_wash' || temp["form"] == 'auto_end_wash'){
                            load_process();
                        }
                        else if (temp["form"] == 'start_pack'){
                            load_process();
                        }
                        else if (temp["form"] == 'end_pack'){
                            load_process();
                        }
                        else if (temp["form"] == 'start_send'){                    
                            load_process();
                        }
                        else if (temp["form"] == 'end_send'){
                            load_process();
                        }
                        else if(temp["form"] == 'logout'){
                            window.location.href='../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        if(temp["form"] == 'load_process'){
                            insert_process();
                        }
                        else if(temp["form"] == 'insert_process'){
                            swal({
                            title: '',
                            text: '<?php echo $genarray['errorToAddData'][$language];?>',
                            type: 'warning',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            showConfirmButton: false,
                            timer: 2000,
                            confirmButtonText: 'Error!!'
                            })
                        }
                    }
                }
            });
        }
    </script>
</head>

<body>

    <header data-role="header">
        <div class="head-bar d-flex justify-content-between">
            <div id="user" class="head-text text-truncate align-self-center"><?php echo $UserName?> : <?php echo $UserFName?></div>
            <button  onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button >
        </div>
    </header>
    <div class="px-3" style="font-family:sans-serif;">

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
        <div class="text-center my-4"><h4 class="text-truncate"><?php echo $DocNo;?></h4></div>
        <div id="h_status" hidden></div>
        <div id="hw_start" hidden></div>
        <div id="hw_stop" hidden></div>
        <div id="hw_end" hidden></div>
        <div id="hp_start" hidden></div>
        <div id="hp_end" hidden></div>
        <div id="hs_start" hidden></div>

        <div id="process">
            <div class="card mt-3" style="padding:1rem;">
                <div class="row">
                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_1.png" height="90px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['Wash'][$language]; ?></div>
                        </div>
                    </div>

                    <div class="col-4 text-left align-self-center text-center">
                        <div class="row">
                            <div id="W_Start_text" class="col-lg-4 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
                                <label id="W_Start" class='font-weight-light'></label>
                            </div>
                            <div id="cnd" class="col-lg-4 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['countdown'][$language]; ?></div>
                                <label id="countdown" class='font-weight-light'>00:00:00</label>
                                <label id="show_stop" class='font-weight-light'></label>
                            </div>
                            <div id="W_End_text" class="col-lg-4 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
                                <label id="W_End" class='font-weight-light'></label>
                            </div>
                            <div id="W_Use_text" class="col-lg-4 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Processtime'][$language]; ?></div>
                                <label id="W_Use" class='font-weight-light'></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 text-center"><img id="W_Status" height="40px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div id="W_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
                            <div class="col-md-6 col-sm-none"></div>
                        </div>
                    </div>
                </div>
                <div id="W_Sum_btn" class="row mt-4">
                    <div class="col-md-2 col-sm-none"></div>
                    <div class="col-md-8 col-sm-12" id="W_First_btn"><button id="W_First_btn_sub" onclick="start_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['StartWash'][$language]; ?></button></div>
                    <div class="col-md-4 col-sm-6" id="W_Start_btn"><button id="W_Start_btn_sub" onclick="start_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Continue'][$language]; ?></button></div>
                    <div class="col-md-4 col-sm-6" id="W_Stop_btn"><button onclick="stop_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-danger btn-block"><?php echo $array['Stop'][$language]; ?></button></div>
                    <div class="col-md-4 col-sm-6" id="W_End_btn"><button onclick="do_end_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
                    <div class="col-md-2 col-sm-none"></div>
                </div>
            </div>

            <div class="card mt-4" style="padding:1rem;">
                <div class="row">
                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_2.png" height="90px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['pack'][$language]; ?></div>
                        </div>
                    </div>

                    <div class="col-4 text-left align-self-center text-center">
                        <div class="row">
                            <div id="P_Start_text" class="col-lg-6 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
                                <label id="P_Start" class='font-weight-light'></label>
                            </div>
                            <div id="P_End_text" class="col-lg-6 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
                                <label id="P_End" class='font-weight-light'></label>                                    
                            </div>
                            <div id="P_Use_text" class="col-lg-4 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Processtime'][$language]; ?></div>
                                <label id="P_Use" class='font-weight-light'></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 text-center"><img id="P_Status" height="40px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div id="P_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
                            <div class="col-md-6 col-sm-none"></div>
                        </div>
                    </div>
                </div>
                <div id="P_Sum_btn" class="row mt-4">
                    <div class="col-md-2 col-sm-none"></div>
                    <div class="col-md-8 col-sm-12" id="P_Start_btn"><button onclick="start_pack('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Startpack'][$language]; ?></button></div>
                    <div class="col-md-8 col-sm-12" id="P_End_btn"><button onclick="end_pack('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
                    <div class="col-md-2 col-sm-none"></div>

                </div>
            </div>

            <div class="card mt-4" style="padding:1rem;">
                <div class="row">
                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_3.png" height="90px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['shipping'][$language]; ?></div>
                        </div>
                    </div>

                    <div class="col-4 text-left align-self-center text-center">
                        <div class="row">
                            <div id="S_Start_text" class="col-lg-6 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
                                <label id="S_Start" class='font-weight-light'></label>
                            </div>
                            <div id="S_End_text" class="col-lg-6 col-md-12 col-sm-12">
                                <div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
                                <label id="S_End" class='font-weight-light'></label> 
                            </div>
                            <div id="S_Use_text" class="col-lg-4 col-md-12 col-sm-12">
                                <div id="S_Head_use" class="head_text"></div>
                                <label id="S_Use" class='font-weight-light'></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-4 align-self-center">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 text-center"><img id="S_Status" height="40px"/></div>
                            <div class="col-md-6 col-sm-none"></div>
                            <div id="S_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
                            <div class="col-md-6 col-sm-none"></div>
                        </div>
                    </div>
                </div>
                <div id="S_Sum_btn" class="row mt-4">
                    <div class="col-md-2 col-sm-none"></div>
                    <div class="col-md-8 col-sm-12" id="S_Start_btn"><button onclick="start_send('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Startshipping'][$language]; ?></button></div>
                    <div class="col-md-8 col-sm-12" id="S_End_btn"><button onclick="end_send('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
                    <div class="col-md-2 col-sm-none"></div>

                </div>
            </div>

            <div id="sign_zone" class="mx-3" hidden>
                <div class="text-center">
                    <div class="row justify-content-center">
                        <div class="card my-2 p-2">
                            <div id="show_sign"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>