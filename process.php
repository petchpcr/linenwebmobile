<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    $ShowSign = "";
    if($Userid==""){
      header("location:index.html");
    }
    $DocNo = $_GET['DocNo'];
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
    <link rel="stylesheet" href="dist/css/sweetalert2.min.css">
    <script src="js/jquery.js"></script>
    <script>
        
        $(document).ready(function (e) {
            setTimeout( 'countdown()', 1000 );
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

        function use_time_wash(WashUseTime){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'WashUseTime': WashUseTime,
                'DocNo': DocNo,
                'STATUS': 'use_time_wash'
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

        function use_time_pack(PackUseTime){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'PackUseTime': PackUseTime,
                'DocNo': DocNo,
                'STATUS': 'use_time_pack'
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
            var data = {
                'DocNo': DocNo,
                'STATUS': 'end_send'
            };
            senddata(JSON.stringify(data));
        }

        function cal_overtime(OverTime){
            var DocNo = "<?php echo $DocNo?>";
            var data = {
                'SendOverTime': OverTime,
                'DocNo': DocNo,
                'STATUS': 'cal_overtime'
            };
            senddata(JSON.stringify(data));
        }

        function back(site){
            window.location.href="document.php?siteCode="+site;
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
            var URL = 'process/process.php';
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
                            
                        var Back = "<button onclick='back(\""+temp['HptCode']+"\")' class='footer-button-left ui-btn-left ui-btn ui-icon-carat-l ui-btn-icon-left ui-btn-inline ui-corner-all ui-mini'>กลับ</button>";
                        $("#title").after(Back);
                        $("#h_status").text(temp['IsStatus']);
                        if(temp['IsStatus'] == 0 || temp['IsStatus'] == null){ //-----ยังไม่ได้ทำอะไร
                            $("#W_Status").attr("src","img/Status_4.png");
                            $("#P_Status").attr("src","img/Status_4.png");
                            $("#S_Status").attr("src","img/Status_4.png");
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
                            $("#W_Status").attr("src","img/Status_1.png");
                            $("#P_Status").attr("src","img/Status_4.png");
                            $("#S_Status").attr("src","img/Status_4.png");
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
                                $("#W_Status").attr("src","img/Status_2.png");
                                $("#W_Status_text").text("Stop Process");
                                $("#countdown").hide();
                                $("#show_stop").show();
                            }else{
                                if(temp['WashStartTime'] == null){ // ถ้ากดเริ่มครั้งแรก
                                    $("#W_Stop_btn").hide();
                                    $("#W_Start_btn").show();
                                    $("#W_Status").attr("src","img/Status_4.png");
                                    $("#W_Status_text").text("No Process");
                                }else{                              // ถ้าเคยกดเริ่มแล้ว
                                    $("#W_Stop_btn").show();
                                    $("#W_Start_btn").hide();
                                    $("#W_Status").attr("src","img/Status_1.png");
                                    $("#W_Status_text").text("Wait Process");
                                }
                            }
                        }
                        else if(temp['IsStatus'] == 2){ //-----กำลังแพคของ
                            $("#W_Status").attr("src","img/Status_3.png");
                            $("#P_Status").attr("src","img/Status_1.png");  
                            $("#S_Status").attr("src","img/Status_4.png");
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

                            var W_Use = "ไม่เกิน 1";
                            var W_Start = new Date(temp['WashStartTime']);
                            var W_End = new Date(temp['WashEndTime']);
                            if(temp['WashUseTime'] >= 1){
                                W_Use = temp['WashUseTime'];
                            }
                            $("#W_Use").text(W_Use+" นาที");
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
                            $("#W_Status").attr("src","img/Status_3.png");
                            $("#P_Status").attr("src","img/Status_3.png");
                            $("#S_Status").attr("src","img/Status_1.png");
                            $("#W_Status_text").text("Success Process");
                            $("#P_Status_text").text("Success Process");
                            $("#S_Status_text").text("Wait Process");
                            $("#P_Start_text").removeClass("col-lg-6");
                            $("#P_End_text").removeClass("col-lg-6");
                            $("#P_Start_text").addClass("col-lg-4");
                            $("#P_End_text").addClass("col-lg-4");
                            $("#S_Use_text").hide();
                            $("#W_Sum_btn").remove();
                            $("#P_Sum_btn").remove();
                            $("#S_Sum_btn").show();
                            $("#cnd").remove();
                            $("#W_Use_text").show();
                            
                            var W_Use = "ไม่เกิน 1";
                            var P_Use = "ไม่เกิน 1";
                            var W_Start = new Date(temp['WashStartTime']);
                            var W_End = new Date(temp['WashEndTime']);
                            var P_Start = new Date(temp['PackStartTime']);
                            var P_End = new Date(temp['PackEndTime']);
                            if(temp['WashUseTime'] >= 1){
                                W_Use = temp['WashUseTime'];
                            }
                            if(temp['PackUseTime'] >= 1){
                                P_Use = temp['PackUseTime'];
                            }
                            $("#W_Use").text(W_Use+" นาที");
                            $("#P_Use").text(P_Use+" นาที");
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
                                    window.location.href='signature.php?DocNo='+temp['DocNo'];
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
                            $("#W_Status").attr("src","img/Status_3.png");
                            $("#P_Status").attr("src","img/Status_3.png");
                            $("#S_Status").attr("src","img/Status_3.png");
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

                            var W_Use = "ไม่เกิน 1";
                            var P_Use = "ไม่เกิน 1";
                            var W_Start = new Date(temp['WashStartTime']);
                            var W_End = new Date(temp['WashEndTime']);
                            var P_Start = new Date(temp['PackStartTime']);
                            var P_End = new Date(temp['PackEndTime']);
                            var S_Start = new Date(temp['SendStartTime']);
                            var S_End = new Date(temp['SendEndTime']);
                            if(temp['WashUseTime'] >= 1){
                                W_Use = temp['WashUseTime'];
                            }
                            if(temp['PackUseTime'] >= 1){
                                P_Use = temp['PackUseTime'];
                            }
                            if(temp['SendOverTime'] >= 1){
                            $("#S_Head_use").text("เกินเวลา");
                            $("#S_Head_use").css("color","red");
                            $("#S_Use").css("color","red");
                            $("#S_Use").text(temp['SendOverTime']+" นาที");
                            } else {
                                $("#S_Head_use").text("ใช้เวลา");
                                var useText = "ไม่เกิน 1";
                                if(temp['SendUseTime'] >= 1){
                                    useText = temp['SendUseTime'];
                                }
                                $("#S_Use").text(useText+" นาที");
                            }
                            $("#W_Use").text(W_Use+" นาที");
                            $("#P_Use").text(P_Use+" นาที");
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
                        $("#h_status").text(1);
                        $("#W_First_btn").remove();
                        $("#W_Start_btn").hide();
                        $("#W_Stop_btn").show();
                        $("#W_End_btn").show();
                        $("#W_Status").attr("src","img/Status_1.png");
                        $("#W_Status_text").text("Wait Process");
                        $("#h_status").text("1");

                        if(temp['WashStopTime'] != null){ // ถ้ากดเริ่ม หลังจากหยุด
                            var stopTime = new Date(temp['WashStopTime']);
                            var endTime = new Date(temp['WashEndTime']);
                            var differ = endTime-stopTime;
                            var current = new Date();
                            current.setMilliseconds(current.getMilliseconds() + differ);
                            set_end_wash(current);
                            $("#hw_start").text(temp['WashStartTime']);
                            $("#hw_stop").text(temp['WashStopTime']);
                            $("#hw_end").text(temp['WashEndTime']);
                        }
                        else{
                            var W_Start = new Date(temp['WashStartTime']);
                            $("#hw_start").text(temp['WashStartTime']);
                            $("#W_Start").text(W_Start.toLocaleTimeString());
                            
                            var millitime = temp['processt']*60000;
                            var W_End = new Date(temp['WashStartTime']);
                            W_End.setMilliseconds(W_End.getMilliseconds() + millitime);
                            set_end_wash(W_End);
                        }
                        $("#countdown").text("00:00:00");
                        $("#countdown").show();
                        $("#show_stop").hide();
                        setTimeout( 'countdown()', 1000 );

                    }
                    else if (temp["form"] == 'set_end_wash'){
                        var endtime = new Date(temp['endTime']);
                        $("#hw_end").text(temp['endTime']);
                        $("#W_End").text(endtime.toLocaleTimeString());
                        $("#countdown").text("00:00:00");

                    }
                    else if (temp["form"] == 'stop_wash'){
                        $("#W_Start_btn").show();
                        $("#W_Stop_btn").hide();
                        $("#W_Status").attr("src","img/Status_2.png");
                        $("#W_Status_text").text("Stop Process");
                        var stop = $("#countdown").text();
                        $("#countdown").hide();
                        $("#show_stop").text(stop);
                        $("#show_stop").show();
                        $("#W_End").text("--:--:--");
                        $("#hw_stop").text(temp['stopTime']);
                    }
                    else if (temp["form"] == 'do_end_wash' || temp["form"] == 'auto_end_wash'){
                        $("#h_status").text(2);
                        $("#W_Sum_btn").remove();
                        $("#P_Sum_btn").show();
                        $("#W_Status").attr("src","img/Status_3.png");                        
                        $("#P_Status").attr("src","img/Status_1.png");
                        $("#W_Status_text").text("Success Process");
                        $("#P_Status_text").text("Wait Process");
                        $("#cnd").remove();
                        $("#W_Use_text").show();
                        
                        var endTime = new Date(temp['endTime']);

                        if(temp["form"] == 'do_end_wash'){
                            $("#W_End").text(endTime.toLocaleTimeString());
                        }

                        $("#hw_end").text(temp['endTime']);
                        
                        var start = new Date($("#hw_start").text());
                        var end = new Date($("#hw_end").text());
                        var differ = end-start;

                        var ms = differ % 1000;
                        differ = (differ - ms) / 1000;
                        var secs = differ % 60;
                        differ = (differ - secs) / 60;
                        var mins = differ % 60;
                        var hrs = (differ - mins) / 60;

                        var WashUseTime = (hrs * 60) + mins;

                        use_time_wash(WashUseTime);
                        $("#P_End_btn").hide();
                        $("#P_Start").text("--:--:--");
                        $("#P_End").text("--:--:--");
                    }
                    else if (temp["form"] == 'use_time_wash'){
                        var WashUseTime = temp['WashUseTime'];
                        var TextUseTime = "ไม่เกิน 1";

                        if(WashUseTime >= 1){
                            TextUseTime = WashUseTime;
                        }

                        $("#W_Use").text(TextUseTime+" นาที");
                    }
                    else if (temp["form"] == 'start_pack'){
                        $("#P_Start_btn").remove();
                        $("#P_End_btn").show();
                        
                        var P_Start = new Date(temp['PackStartTime']);
                        $("#hp_start").text(temp['PackStartTime']);
                        $("#P_Start").text(P_Start.toLocaleTimeString());
                    }
                    else if (temp["form"] == 'end_pack'){
                        $("#h_status").text(3);
                        $("#P_Sum_btn").remove();
                        $("#S_Sum_btn").show();
                        $("#P_Status").attr("src","img/Status_3.png");                        
                        $("#S_Status").attr("src","img/Status_1.png");
                        $("#P_Status_text").text("Success Process");
                        $("#S_Status_text").text("Wait Process");
                        $("#P_Start_text").removeClass("col-lg-6");
                        $("#P_End_text").removeClass("col-lg-6");
                        $("#P_Start_text").addClass("col-lg-4");
                        $("#P_End_text").addClass("col-lg-4");
                        $("#P_Use_text").show();

                        var P_Start = new Date($("#hp_start").text());
                        var P_End = new Date(temp['PackEndTime']);
                        $("#hp_end").text(temp['PackEndTime']);
                        $("#P_End").text(P_End.toLocaleTimeString());
                        
                        var differ = P_End-P_Start;

                        var ms = differ % 1000;
                        differ = (differ - ms) / 1000;
                        var secs = differ % 60;
                        differ = (differ - secs) / 60;
                        var mins = differ % 60;
                        var hrs = (differ - mins) / 60;

                        var PackUseTime = (hrs * 60) + mins;

                        use_time_pack(PackUseTime);
                        $("#S_End_btn").hide();
                        $("#S_Start").text("--:--:--");
                        $("#S_End").text("--:--:--");
                    }
                    else if (temp["form"] == 'use_time_pack'){
                        var PackUseTime = temp['PackUseTime'];
                        var TextUseTime = "ไม่เกิน 1";

                        if(PackUseTime >= 1){
                            TextUseTime = PackUseTime;
                        }

                        $("#P_Use").text(TextUseTime+" นาที");
                    }
                    else if (temp["form"] == 'start_send'){
                        $("#S_Start_btn").remove();
                        $("#S_End_btn").show();

                        var S_Start = new Date(temp['SendStartTime']);
                        $("#hs_start").text(temp['SendStartTime']);
                        $("#S_Start").text(S_Start.toLocaleTimeString());                       
                    }
                    else if (temp["form"] == 'end_send'){
                        $("#h_status").text(4);
                        $("#S_Sum_btn").remove();                        
                        $("#S_Status").attr("src","img/Status_3.png");                        
                        $("#S_Status_text").text("Success Process");
                        $("#S_Start_text").removeClass("col-lg-6");
                        $("#S_End_text").removeClass("col-lg-6");
                        $("#S_Start_text").addClass("col-lg-4");
                        $("#S_End_text").addClass("col-lg-4");
                        $("#S_Use_text").show();

                        var S_Start = new Date(temp['SendStartTime']);
                        var S_End = new Date(temp['SendEndTime']);
                        $("#S_End").text(S_End.toLocaleTimeString());

                        var differ = S_End-S_Start;
                            
                        var ms = differ % 1000;
                        differ = (differ - ms) / 1000;
                        var secs = differ % 60;
                        differ = (differ - secs) / 60;
                        var mins = differ % 60;
                        var hrs = (differ - mins) / 60;

                        var OverTime = (hrs * 60) + mins;
                        cal_overtime(OverTime);
                        load_process();
                    }
                    else if(temp["form"] == 'cal_overtime'){
                        if(temp['SendOverTime'] >= 1){
                            $("#S_Head_use").text("เกินเวลา");
                            $("#S_Head_use").css("color","red");
                            $("#S_Use").css("color","red");
                            $("#S_Use").text(temp['SendOverTime']+" นาที");
                        } else {
                            $("#S_Head_use").text("ใช้เวลา");
                            var useText = "ไม่เกิน 1";
                            if(temp['SendUseTime'] >= 1){
                                useText = temp['SendUseTime'];
                            }
                            $("#S_Use").text(useText+" นาที");
                        }
                    }
                    else if(temp["form"] == 'logout'){
                        window.location.href='index.html';
                    }
                } else if (temp['status'] == "failed") {
                    if(temp["form"] == 'load_process'){
                        insert_process();
                    }
                    else if(temp["form"] == 'insert_process'){
                        swal({
                        title: '',
                        text: 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล',
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
    <section data-role="page">

        <header data-role="header">
            <title id="title">Process</title>
            <h1 class="ui-title" role="heading" aria-level="1"><?php echo $UserName;?> : <?php echo $UserFName;?></h1>
            <a onclick="logout(1)" class="ui-btn-right ui-btn ui-btn-b ui-icon-power ui-btn-icon-right ui-btn-inline ui-corner-all ui-mini">ออก</a>
        </header>
        <div data-role="content" style="font-family:sans-serif;">

            <div align="center" style="margin:1rem 0;"><img src="img/logo.png" width="220" height="45"/></div>
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
                                <div class="col-md-6 col-sm-12 text-center"><img src="img/icon_1.png" height="90px"/></div>
                                <div class="col-md-6 col-sm-none"></div>
                                <div class="col-md-6 col-sm-12 text-center font-weight-light">ซักผ้า</div>
                            </div>
                        </div>

                        <div class="col-4 text-left align-self-center text-center">
                            <div class="row">
                                <div id="W_Start_text" class="col-lg-4 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาที่เริ่ม</div>
                                    <label id="W_Start" class='font-weight-light'></label>
                                </div>
                                <div id="cnd" class="col-lg-4 col-md-12 col-sm-12">
                                    <div class="head_text">นับถอยหลัง</div>
                                    <label id="countdown" class='font-weight-light'>00:00:00</label>
                                    <label id="show_stop" class='font-weight-light'></label>
                                </div>
                                <div id="W_End_text" class="col-lg-4 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาสิ้นสุด</div>
                                    <label id="W_End" class='font-weight-light'></label>
                                </div>
                                <div id="W_Use_text" class="col-lg-4 col-md-12 col-sm-12">
                                    <div class="head_text">ใช้เวลา</div>
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
                        <div class="col-md-8 col-sm-12" id="W_First_btn"><button id="W_First_btn_sub" onclick="start_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary">เริ่มซัก</button></div>
                        <div class="col-md-4 col-sm-6" id="W_Start_btn"><button id="W_Start_btn_sub" onclick="start_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary">ทำต่อ</button></div>
                        <div class="col-md-4 col-sm-6" id="W_Stop_btn"><button onclick="stop_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-danger">หยุด</button></div>
                        <div class="col-md-4 col-sm-6" id="W_End_btn"><button onclick="do_end_wash('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success">เสร็จสิ้น</button></div>
                        <div class="col-md-2 col-sm-none"></div>
                    </div>
                </div>

                <div class="card mt-4" style="padding:1rem;">
                    <div class="row">
                        <div class="col-4 align-self-center">
                            <div class="row">
                                <div class="col-md-6 col-sm-none"></div>
                                <div class="col-md-6 col-sm-12 text-center"><img src="img/icon_2.png" height="90px"/></div>
                                <div class="col-md-6 col-sm-none"></div>
                                <div class="col-md-6 col-sm-12 text-center font-weight-light">บรรจุผ้า</div>
                            </div>
                        </div>

                        <div class="col-4 text-left align-self-center text-center">
                            <div class="row">
                                <div id="P_Start_text" class="col-lg-6 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาที่เริ่ม</div>
                                    <label id="P_Start" class='font-weight-light'></label>
                                </div>
                                <div id="P_End_text" class="col-lg-6 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาสิ้นสุด</div>
                                    <label id="P_End" class='font-weight-light'></label>                                    
                                </div>
                                <div id="P_Use_text" class="col-lg-4 col-md-12 col-sm-12">
                                    <div class="head_text">ใช้เวลา</div>
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
                        <div class="col-md-8 col-sm-12" id="P_Start_btn"><button onclick="start_pack('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary">เริ่มบรรจุ</button></div>
                        <div class="col-md-8 col-sm-12" id="P_End_btn"><button onclick="end_pack('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success">เสร็จสิ้น</button></div>
                        <div class="col-md-2 col-sm-none"></div>

                    </div>
                </div>

                <div class="card mt-4" style="padding:1rem;">
                    <div class="row">
                        <div class="col-4 align-self-center">
                            <div class="row">
                                <div class="col-md-6 col-sm-none"></div>
                                <div class="col-md-6 col-sm-12 text-center"><img src="img/icon_3.png" height="90px"/></div>
                                <div class="col-md-6 col-sm-none"></div>
                                <div class="col-md-6 col-sm-12 text-center font-weight-light">ขนส่ง</div>
                            </div>
                        </div>

                        <div class="col-4 text-left align-self-center text-center">
                            <div class="row">
                                <div id="S_Start_text" class="col-lg-6 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาที่เริ่ม</div>
                                    <label id="S_Start" class='font-weight-light'></label>
                                </div>
                                <div id="S_End_text" class="col-lg-6 col-md-12 col-sm-12">
                                    <div class="head_text">เวลาสิ้นสุด</div>
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
                        <div class="col-md-8 col-sm-12" id="S_Start_btn"><button onclick="start_send('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-primary">เริ่มขนส่ง</button></div>
                        <div class="col-md-8 col-sm-12" id="S_End_btn"><button onclick="end_send('<?php echo $DocNo;?>')" type="button" class="btn btn-lg btn-success">เสร็จสิ้น</button></div>
                        <div class="col-md-2 col-sm-none"></div>

                    </div>
                </div>
            </div>

        </div>
        <div id="sign_zone" hidden>
            <div class="text-center">
                <div class="row mx-3 justify-content-center">
                    <div class="card my-2 p-2">
                        <div id="show_sign"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
	<script src="js/jquery.mobile-1.4.5.min.js"></script>
    <script src="dist/js/sweetalert2.min.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
</body>
</html>