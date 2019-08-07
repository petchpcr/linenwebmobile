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
    $language = $_SESSION['lang'];
    $xml = simplexml_load_file('../xml/Language/dirty_lang.xml');
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    $genxml = simplexml_load_file('../xml/Language/general_lang.xml');
    $json = json_encode($genxml);
    $genarray = json_decode($json, TRUE);
    require '../getTimeZone.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
        $Menu = $_GET['Menu'];
        if ($Menu == 'dirty') {
            echo "<title>" . $genarray['titledirty'][$language] . $genarray['titleDocument'][$language] . "</title>";
        }else{
            echo "<title>" . $genarray['titlefactory'][$language] . $genarray['titleDocument'][$language] . "</title>";
        }
    ?>

    <?php 
        require 'script_css.php'; 
        require 'logout_fun.php';
    ?>

    <script>
        $(document).ready(function (e) {
            
            var Menu = '<?php echo $Menu;?>';
            if(Menu == 'factory'){ $("#add_doc").remove(); }
            load_dep();
            load_site();
            load_doc();
            load_Fac();
        });

        function load_dep(){
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_dep'
            };
            senddata(JSON.stringify(data));
        }

        function load_Fac(){
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'STATUS': 'load_Fac'
            };
            senddata(JSON.stringify(data));
        }


        function load_site(){
            $('#datepicker').val("<?php echo date("Y-m-d"); ?>");
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function load_doc(){
            var search = $('#datepicker').val();
            // var searchDate = new Date(search);
            var siteCode = "<?php echo $siteCode?>";
            var Menu = "<?php echo $Menu?>";
            if(Menu=='factory'){
                var status = 'load_doc_procees';
            }else{
                var status = 'load_doc';
            }
            var data = {
                'search': search,
                'siteCode': siteCode,
                'Menu': Menu,
                'STATUS': status
            };
            senddata(JSON.stringify(data));
        }

        function show_process(DocNo){
            var siteCode = '<?php echo $siteCode ?>';
            var Menu = '<?php echo $Menu ?>';
            if(Menu == 'dirty'){
                window.location.href='dirty_view.php?siteCode='+siteCode+'&Menu='+Menu+'&DocNo='+DocNo;
            }
            else if(Menu == 'factory'){
                window.location.href='process.php?siteCode='+siteCode+'&Menu='+Menu+'&DocNo='+DocNo;
            }
        }

        function receive_zero(DocNo){
            swal({
                title: 'ยืนยันการรับเอกสาร',
                text: "คุณได้รับเอกสาร ผ้าสกปรกนี้แล้วใช่หรือไม่ ?",
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ไม่ใช่'
                }).then((result) => {
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

        function change_dep(){
            var slt = $("#DepName").val();
            var sltFac = $("#FacName").val();
            if (slt == 0 || sltFac == 0) {
                $("#btn_add_dirty").prop('disabled',true);
            }
            else {
                $("#btn_add_dirty").prop('disabled',false);
            }
        }

        function add_dirty(){
            var Userid = "<?php echo $Userid?>";
            var siteCode = "<?php echo $siteCode?>";
            var DepCode = $("#DepName").val();
            var FacCode = $("#FacName").val();
            var data = {
                'Userid': Userid,
                'siteCode': siteCode,
                'DepCode': DepCode,
                'FacCode': FacCode,
                'STATUS': 'add_dirty'
            };
            senddata(JSON.stringify(data));
        }

        function back(){
            var Menu = '<?php echo $Menu;?>';
            if(Menu == "factory"){
                window.location.href="hospital.php?Menu=factory";
            }else{
                window.location.href="menu.php";
            }
            
           
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/dirty.php';
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
                    if(temp["form"] == 'load_dep'){
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var Str = "<option value="+temp[i]['DepCode']+">"+temp[i]['DepName']+"</option>";
                            $("#DepName").append(Str);
                        }

                    }
                    else if(temp["form"] == 'load_Fac'){
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var Str = "<option value="+temp[i]['FacCode']+">"+temp[i]['FacName']+"</option>";
                            $("#FacName").append(Str);
                        }

                    }
                    else if(temp["form"] == 'load_site'){
                        $("#HptName").text(temp['HptName']);
                    }
                    else if (temp["form"] == 'load_doc') {
                        
                        $(".btn.btn-mylight.btn-block").remove();
                        for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                            var status_class = "";
                            var status_text = "";
                            var status_line = "";
                            var Menu = '<?php echo $Menu;?>';

                            if (Menu == 'dirty') {
                                if (temp[i]['IsStatus'] == 0) {
                                    status_class = "status1";
                                    status_text = "หยุดชั่วขณะ";
                                    status_line = "StatusLine_1";
                                }
                                else if (temp[i]['IsStatus'] == 1 || temp[i]['IsStatus'] == 3) {
                                    status_class = "status2";
                                    status_text = "เสร็จสิ้น";
                                    status_line = "StatusLine_2";
                                }
                                else {
                                    status_class = "status3";
                                    status_text = "กำลังดำเนินการ";
                                    status_line = "StatusLine_3";
                                }

                                var Str = "<button onclick='show_process(\""+temp[i]['DocNo']+"\")' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5'>";
                                    Str += "<div class='row justify-content-end align-items-center'><div class='card "+status_class+"'>"+status_text+"</div>";
                                    Str += "<img src='../img/"+status_line+".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                    Str += "<div class='text-truncate font-weight-bold'>"+temp[i]['DocNo']+"</div><div class='font-weight-light'>"+temp[i]['DepName']+"</div></div></div></button>";

                                $("#document").append(Str);

                            } else if (Menu == 'factory') {

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
                                else if(temp[i]['IsProcess'] == 2){
                                    status_class = "status1";
                                    status_text = "หยุดชั่วขณะ";
                                    status_line = "StatusLine_1";
                                }
                                else if(temp[i]['IsProcess'] == 3){
                                    if (temp[i]['Signature'] == null) {
                                        status_class = "status3";
                                        status_text = "กำลังดำเนินการ";
                                        status_line = "StatusLine_3";
                                    }
                                    else {
                                        status_class = "status2";
                                        status_text = "เสร็จสิ้น";
                                        status_line = "StatusLine_2";
                                    }
                                }
                                
                                var onclick = "show_process(\""+temp[i]['DocNo']+"\")";
                                if (temp[i]['IsReceive'] == 0) {
                                    onclick = "receive_zero(\""+temp[i]['DocNo']+"\")";
                                }

                                if(temp[i]['IsStatus'] > 0){
                                
                                    var Str = "<button onclick='"+onclick+"' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5'>";
                                        Str += "<div class='row justify-content-end align-items-center'><div class='card "+status_class+"'>"+status_text+"</div>";
                                        Str += "<img src='../img/"+status_line+".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                        Str += "<div class='text-truncate font-weight-bold'>"+temp[i]['DocNo']+"</div><div class='font-weight-light'>"+temp[i]['DepName']+"</div></div></div></button>";

                                    $("#document").append(Str);
                                }
                            }
                        }
                    } 
                    else if(temp["form"] == 'confirm_yes'){
                        show_process(temp['DocNo']);
                    }
                    else if(temp["form"] == 'add_dirty'){
                        var Userid = temp['user']
                        var siteCode = temp['siteCode']
                        var DepCode = temp['DepCode']
                        var DocNo = temp['DocNo']
                        var Menu = '<?php echo $Menu;?>';
                        window.location.href='add_items.php?siteCode='+siteCode+'&DepCode='+DepCode+'&DocNo='+DocNo+'&Menu='+Menu+'&user='+Userid;
                    }
                    else if(temp["form"] == 'logout'){
                        window.location.href='../index.html';
                    }
                } else if (temp['status'] == "failed") {
                    if(temp["form"] == 'load_doc'){
                        $(".btn.btn-mylight.btn-block").remove();
                        swal({
                            title: '',
                            text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?>'+$('#datepicker').val(),
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
            <button onclick='back()' class='head-btn btn-light'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3 pb-4 mb-5">

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
        <div class="text-center my-4"><h4 id="HptName" class="text-truncate"></h4></div>
        <div id="document">
            <div class="d-flex justify-content-center mb-3">
                <input id="datepicker" class="text-truncate text-center" width="276" placeholder='<?php echo $genarray['CreateDocDate'][$language]; ?>' disabled/>
                <button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
            </div>
            <div id="add_doc" class="fixed-bottom pb-4 px-3 bg-white">
                <button class="btn btn-primary btn-block" type="button" data-toggle="modal" data-target="#exampleModal">
                    <i class="fas fa-plus mr-1"></i><?php echo $genarray['createdocno'][$language]; ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['confirmCreatedocno'][$language]; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                    <?php echo $genarray['chooseDepartment'][$language].$array['CreateDirtyLinenDoc'][$language]; ?>
                    <div class="input-group my-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01"><?php echo $genarray['chooseDep'][$language]; ?></label>
                        </div>
                        <select onchange="change_dep()" id="DepName" class="custom-select">
                            <option value="0" selected><?php echo $genarray['chooseDepartmentPl'][$language]; ?></option>
                        </select>
                    </div>
                    <div class="input-group my-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01"><?php echo $array['chooseFactory'][$language]; ?></label>
                        </div>
                        <select onchange="change_dep()" id="FacName" class="custom-select">
                            <option value="0" selected><?php echo $array['chooseFactoryPl'][$language]; ?></option>
                        </select>
                    </div>
                </div>
            <div class="modal-footer text-center">
                <div class="row w-100 d-flex align-items-center m-0">
                    <div class="col-6 text-right">
                        <button id="btn_add_dirty" onclick="add_dirty()" type="button" class="btn btn-success m-2" disabled><?php echo $genarray['confirm'][$language]; ?></button>
                    </div>
                    <div class="col-6 text-left">
                        <button type="button" class="btn btn-danger m-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        $('#datepicker').datepicker({
            // uiLibrary: 'bootstrap4',
            size: 'large',
            format: 'yyyy-mm-dd'
        });
    </script>

</body>
</html>