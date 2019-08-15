<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if ($Userid == "") {
        header("location:../index.html");
    }
    $Menu = $_GET['Menu'];
    $siteCode = $_GET['siteCode'];
    $language = $_SESSION['lang'];
    $xml = simplexml_load_file('../xml/Language/clean_lang.xml');
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
    <title><?php echo $genarray['titleclean'][$language].$genarray['titleDocument'][$language];?></title>

    <?php 
        require 'script_css.php'; 
        require 'logout_fun.php';
    ?>

    <script>
        $(document).ready(function(e) {
            load_dep();
            load_doc();
            load_site();
        });

        var depCode;

        function load_site() {
            console.log("<?php echo date("Y-m-d"); ?>");
            $('#datepicker').val("<?php echo date("Y-m-d"); ?>");
            var siteCode = "<?php echo $siteCode ?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function change_doc() {
            var slt = $("#DocName").val();
            if (slt == 0) {
                $("#btn_confirm").prop('disabled', true);
            } else {
                $("#btn_confirm").prop('disabled', false);
            }
        }

        function load_dep(){
            var siteCode = "<?php echo $siteCode?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_dep'
            };
            senddata(JSON.stringify(data));
        }

        function load_doc() {
            var search = $('#datepicker').val();
            // var searchDate = new Date(search);
            var siteCode = "<?php echo $siteCode ?>";
            var Menu = "<?php echo $Menu ?>";
            var data = {
                'search': search,
                'siteCode': siteCode,
                'Menu': Menu,
                'STATUS': 'load_doc'
            };
            senddata(JSON.stringify(data));
        }

        function show_process(DocNo) {
            var siteCode = "<?php echo $siteCode ?>";
            var Menu = '<?php echo $Menu; ?>';
            window.location.href = 'clean_view.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + DocNo;
        }

        function back() {
            var Menu = '<?php echo $Menu; ?>';
            window.location.href="menu.php";
        }

        function add_clean() {
            var siteCode = "<?php echo $siteCode ?>";
            var Menu = '<?php echo $Menu ?>';
            var slt = $("#DocName").val();
            //var DepCode = $("#DepName").val(); 
            //window.location.href='ref_dirty.php?siteCode='+siteCode+'&DepCode=224&Menu='+Menu; // Handle(DepCode = 224)
            if (slt == 1) {
                window.location.href='ref_dirty.php?siteCode='+siteCode+'&DepCode='+depCode+'&Menu='+Menu; // Handle(DepCode = 224)
            }
            // else if (slt == 2) {
            //     window.location.href='ref_claim.php?siteCode='+siteCode+'&DepCode=224&Menu='+Menu; // Handle(DepCode = 224)
            // }
            else if (slt == 3) {
                window.location.href='ref_rewash.php?siteCode='+siteCode+'&DepCode='+depCode+'&Menu='+Menu; // Handle(DepCode = 224)
            }
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
                success: function(result) {
                    try {
                        var temp = $.parseJSON(result);
                    } catch (e) {
                        console.log('Error#542-decode error');
                    }

                    if (temp["status"] == 'success') {
                        if (temp["form"] == 'load_site') {
                            $("#HptName").text(temp['HptName']);
                        } else if (temp["form"] == 'load_doc') {
                            $(".btn.btn-mylight.btn-block").remove();
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                var status_class = "";
                                var status_text = "";
                                var status_line = "";

                                if (temp[i]['IsStatus'] == 0) {
                                    status_class = "status4";
                                    status_text = "กำลังสร้างเอกสาร";
                                    status_line = "StatusLine_4";
                                } 
                                else if (temp[i]['IsStatus'] == 1) {
                                    status_class = "status1";
                                    status_text = "สร้างเอกสารเสร็จสิ้น";
                                    status_line = "StatusLine_1";
                                } 
                                else if (temp[i]['IsStatus'] == 2) {
                                    status_class = "status1";
                                    status_text = "กำลังตรวจสอบคุณภาพ";
                                    status_line = "StatusLine_1";
                                }
                                else if (temp[i]['IsStatus'] == 3) {
                                    status_class = "status5";
                                    status_text = "ส่งเคลม";
                                    status_line = "StatusLine_5";
                                }
                                else {
                                    status_class = "status3";
                                    status_text = "เสร็จสิ้น";
                                    status_line = "StatusLine_3";
                                }

                                var Str = "<button onclick='show_process(\"" + temp[i]['DocNo'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
                                Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
                                Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['DocNo'] + "</div><div class='font-weight-light'>" + temp[i]['DepName'] + "</div></div></div></button>";

                                $("#document").append(Str);

                            }
                        } else if (temp["form"] == 'show_process') {
                            window.location.href = 'process.php?siteCode=' + temp['siteCode'];
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                        else if(temp["form"] == 'load_dep'){
                            depCode = temp["DepCode"];
                        }
                    } else if (temp['status'] == "failed") {
                        if (temp["form"] == 'load_doc') {
                            $(".btn.btn-mylight.btn-block").remove();
                            var search = $('#datepicker').val();
                            console.log(search);
                            swal({
                                title: '',
                                text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?>'+search,
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
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3">

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center my-4">
            <div id="HptName" class="text-truncate font-weight-bold" style="font-size:25px;"></div>
        </div>
        <div id="document">
            <div class="d-flex justify-content-center mb-3">
                <input id="datepicker" class="text-truncate text-center" width="276" placeholder='<?php echo $genarray['CreateDocDate'][$language]; ?>' disabled/>
                <button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
            </div>
            <div id="add_doc" class="fixed-bottom pb-4 px-3 bg-white">
                
                <!-- <button class="btn btn-primary btn-block" type="button" onclick="add_clean()">  -->
                <button class="btn btn-primary btn-block" type="button" data-toggle="modal" data-target="#choose_doc">
                    <i class="fas fa-plus mr-1"></i><?php echo $genarray['createdocno'][$language]; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="choose_doc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['confirmCreatedocno'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <?php echo $genarray['docfirst'][$language].$array['CreateCleanLinenDoc'][$language]; ?>
                    <div class="input-group my-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="inputGroupSelect01"><?php echo $genarray['selecttypedoc'][$language]; ?></label>
                        </div>
                        <select onchange="change_doc()" id="DocName" class="custom-select">
                            <option value="0" selected><?php echo $genarray['docfirst'][$language]; ?></option>
                            <option value="1"><?php echo $array['refDocDirty'][$language]; ?></option>
                            <!-- <option value="2"><?php echo $array['refDocClaim'][$language]; ?></option> -->
                            <option value="3"><?php echo $array['refDocRewash'][$language]; ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-6 text-right">
                            <button id="btn_confirm" onclick="add_clean()" type="button" class="btn btn-primary m-2" disabled><?php echo $genarray['yes'][$language]; ?></button>
                        </div>
                        <div class="col-6 text-left">
                            <button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
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