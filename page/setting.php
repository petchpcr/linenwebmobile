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

    <?php 
        require 'script_css.php'; 
        require 'logout_fun.php';
    ?>

    <script>
        $(document).ready(function(e) {
            $("#lang").val('<?php echo $language; ?>');
        });

        function back() {
            window.location.href = "menu.php";
        }

        function load_site_fac() {
            var data = {
                'STATUS': 'load_site_fac'
            };
            senddata(JSON.stringify(data));
        }

        function load_site() {
            var data = {
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function show_fac(HptCode) {
            var data = {
                'HptCode' : HptCode,
                'STATUS': 'show_fac'
            };
            senddata(JSON.stringify(data));
        }

        function LoadDeliveryFacNhealth () {
            var data = {
                'STATUS': 'LoadDeliveryFacNhealth'
            };
            senddata(JSON.stringify(data));
        }
        
        function AddFacNhealth() {
            var FacCode = $("#from_fac").val();
            var HptCode = $("#to_hpt").val();
            var SendTime = $("#new_send_time").val();
            if (SendTime == null || SendTime == "" || SendTime == 0) {
                Title = "เวลาไม่ถูกต้อง";
                Type = "warning";
                Text = "โปรดกรอกเวลาขนส่ง !";
                AlertError(Title,Text,Type);
            }
            else {
                var data = {
                    'FacCode': FacCode,
                    'HptCode': HptCode,
                    'SendTime': SendTime,
                    'STATUS': 'AddFacNhealth'
                };
                senddata(JSON.stringify(data));
            }
        }

        function EditFacNhealth(count) {
            var arr_fac_FacCode = [];
            var arr_fac_HptCode = [];
            var arr_fac_sentTime = [];
            for (var i = 0; i < count; i++) {
                var id = "#fac_nhealth_time"+i;
                var FacCode = $(id).attr("data-FacCode");
                var HptCode = $(id).attr("data-HptCode");
                var SendTime = $(id).val();
                arr_fac_FacCode.push(FacCode);
                arr_fac_HptCode.push(HptCode);
                arr_fac_sentTime.push(SendTime);
            }
            var str_FacCode = arr_fac_FacCode.join(',');
            var str_HptCode = arr_fac_HptCode.join(',');
            var str_sentTime = arr_fac_sentTime.join(',');

            var data = {
                'str_FacCode': str_FacCode,
                'str_HptCode': str_HptCode,
                'str_sentTime': str_sentTime,
                'STATUS': 'EditFacNhealth'
            };
            senddata(JSON.stringify(data));
        }

        function save_lang() {
            var lang = $("#lang").val();
            var Userid = '<?php echo $Userid; ?>';

            var data = {
                'lang': lang,
                'Userid': Userid,
                'STATUS': 'save_lang'
            };
            senddata(JSON.stringify(data));
        }

        function save() {
            save_lang()
        }

        function make_number() {
            $('.numonly').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, ''); //<-- replace all other than given set of values\
                this.value = Number(this.value);
            });
        }

        function AlertError(Title,Text,Type){
            swal({
                title: Title,
                text: Text,
                type: Type,
                showConfirmButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'ตกลง'
            })
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
                    if($("#lang").val() == "th"){
                        var lang = '<?php echo $genarray['savesuccess']["th"]; ?>';
                    }else{
                        var lang = '<?php echo $genarray['savesuccess']["en"]; ?>';
                    }
                    if (temp["status"] == 'success') {
                        if (temp["form"] == 'load_site_fac') {
                            if (temp['cnt_Hpt'] > 0) {
                                $("#to_hpt").empty();
                                for (var i = 0; i < temp['cnt_Hpt']; i++) {
                                    var Str = "<option value='"+temp[i]['HptCode']+"'>"+temp[i]['HptName']+"</option>";
                                    $("#to_hpt").append(Str);
                                }
                            }
                            if (temp['cnt_Fac'] > 0) {
                                $("#from_fac").empty();
                                for (var i = 0; i < temp['cnt_Fac']; i++) {
                                    var Str = "<option value='"+temp[i]['FacCode']+"'>"+temp[i]['FacName']+"</option>";
                                    $("#from_fac").append(Str);
                                }
                            }
                            $("#new_send_time").val("");
                            $("#md_add_fac_nhealth").modal("show");
                        }
                        else if (temp["form"] == 'load_site') {
                            if (temp['count'] > 0) {
                                $("#show_hpt_fac").empty();
                                for (var i = 0; i < temp['count']; i++) {
                                    var picture = temp[i]['picture'];
                                    if (temp[i]['picture'] == null || temp[i]['picture'] == "") {
                                        picture = "logo.png";
                                    }
                                    
                                    var Str = "<button onclick='show_fac(\"" + temp[i]['HptCode'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'>";
                                        Str += "<div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>";
                                        Str += "<img class='hpt_img' src='../img/" + picture + "'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>";
                                        Str += "<img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name'>" + temp[i]['HptName'] + "</div></div></div></button>";
                                    $("#show_hpt_fac").append(Str);
                                }
                                
                                $("#btn_edit_fac_send_time").prop("disabled",true);
                                $("#show_hpt_fac").show();
                                $("#show_fac_nhealth_time").hide();
                                $("#md_fac_nhealth").modal("show");
                            }
                        }
                        else if (temp["form"] == 'show_fac') {
                            if (temp['count'] > 0) {
                                $("#show_fac_nhealth_time").empty();
                                for (var i = 0; i < temp['count']; i++) {
                                    var Str = "<div class='input-group my-3'><div class='input-group-prepend'><label class='input-group-text'>"+temp[i]['FacName']+"</label></div>";
                                        Str += "<input onkeydown='make_number()' id='fac_nhealth_time"+i+"' type='text' class='form-control text-center numonly' ";
                                        Str += "data-HptCode='"+temp[i]['HptCode']+"' data-FacCode='"+temp[i]['FacCode']+"' value='"+temp[i]['SendTime']+"'>";
                                        Str += "<div class='input-group-append'><span class='input-group-text'>นาที</span></div></div>";
                                    $("#show_fac_nhealth_time").append(Str);
                                }
                                $("#btn_edit_fac_send_time").attr("onclick","EditFacNhealth("+temp['count']+")");
                                $("#btn_edit_fac_send_time").prop("disabled",false);
                                $("#show_hpt_fac").hide();
                                $("#show_fac_nhealth_time").show();
                            }
                        }
                        else if (temp["form"] == 'save_lang') {
                            swal({
                                title: '',
                                text: lang,
                                type: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                showConfirmButton: false,
                                timer: 2000,
                                confirmButtonText: 'Error!!'
                            })
                            setTimeout( 'window.location.href = "menu.php"', 1000 );
                        }
                        else if (temp["form"] == 'AddFacNhealth') {
                            $("#md_add_fac_nhealth").modal('hide');
                            swal({
                                title: '',
                                text: lang,
                                type: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                showConfirmButton: false,
                                timer: 1000,
                                confirmButtonText: 'Error!!'
                            })
                        }
                        else if (temp["form"] == 'EditFacNhealth') {
                            $("#md_fac_nhealth").modal('hide');
                            swal({
                                title: '',
                                text: lang,
                                type: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                showConfirmButton: false,
                                timer: 1000,
                            })
                        }
                        else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        swal({
                            title: '',
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
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        
        <div class="modal-body text-center">
            <div class="row">
                <div class="col-auto">ตั้งค่าภาษา<i class="fas fa-lg fa-language ml-2"></i></div>
                <div class="col"><hr></div>
            </div>
            <div id="set_language">
                <div class="input-group mb-5">
                    <div class="input-group-prepend">
                        <label class="input-group-text"><?php echo $array['changelang'][$language]; ?></label>
                    </div>
                    <select id="lang" class="custom-select">
                        <option value="th"><?php echo $array['th'][$language]; ?></option>
                        <option value="en"><?php echo $array['en'][$language]; ?></option>
                    </select>
                </div>
            </div>
            
            <div id="set_fac_nhealth">
                <div class="row">
                    <div class="col-auto">ตั้งค่าเวลาขนส่ง<i class="fas fa-truck ml-2"></i></div>
                    <div class="col"><hr></div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-12">
                        <button onclick="load_site_fac()" class="btn btn-block btn-outline-primary mb-2">
                            <i class="fas fa-plus mr-2"></i>เพิ่มเวลาขนส่ง
                        </button>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <button onclick="load_site()" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-edit mr-2"></i>แก้ไขเวลาขนส่ง
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer text-center">
            <div class="row w-100 d-flex align-items-center">
                <div class="col-12 text-right">
                    <button id="btn_save" onclick="save()" type="button" class="btn btn-primary"><i class="fas fa-save mr-2"></i><?php echo $genarray['save'][$language]; ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!--------------------------------------- Modal Factory to N health --------------------------------------->
    <div class="modal fade" id="md_add_fac_nhealth" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-plus mr-2"></i>เพิ่มเวลาขนส่ง</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div class="form-row">
                    <div class="col-md-6 col-sm-12 col-12">
                        <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <label class="input-group-text" style="width:60px;">จาก</label>
                            </div>
                            <select id="from_fac" class="custom-select"></select>                   
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 col-12">
                        <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <label class="input-group-text" style="width:60px;">ไปยัง</label>
                            </div>
                            <select id="to_hpt" class="custom-select"></select>                   
                        </div>
                    </div>
                    </div>
                    <div class="input-group my-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" style="width:60px;">ใช้เวลา</label>
                        </div>
                        <input onkeydown='make_number()' id="new_send_time" type="text" class="form-control text-center numonly" placeholder="0">
                        <div class="input-group-append">
                            <span class="input-group-text">นาที</span>
                        </div>                     
                    </div>

                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-center">
                            <button onclick="AddFacNhealth()" type="button" class="btn btn-primary mx-3"><?php echo $genarray['save'][$language]; ?></button>
                            <button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="md_fac_nhealth" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-edit mr-2"></i>เปลี่ยนเวลาขนส่ง</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div id="show_hpt_fac">
                        <!-- <button onclick='' class='btn btn-mylight btn-block' style='align-items: center !important;'>
                        <div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>
                        <img class='hpt_img' src='../img/logo_1.png'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>
                        <img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name'>444444444</div></div></div></button> -->
                    </div>
                    <div id="show_fac_nhealth_time"></div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-center">
                            <button id="btn_edit_fac_send_time" type="button" class="btn btn-primary mx-3"><?php echo $genarray['save'][$language]; ?></button>
                            <button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>

</html>