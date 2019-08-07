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
        echo "<title>" . $genarray['titlefactory'][$language] . $genarray['titleDocument'][$language] . "</title>";
        require 'script_css.php';
    ?>
    <script>
        var f =true;
        $(document).ready(function(e) {
            load_site();
            load_doc();
        });

        function load_site() {
            $('#datepicker').val("<?php echo date("Y-m-d"); ?>");
            var siteCode = "<?php echo $siteCode ?>";
            var data = {
                'siteCode': siteCode,
                'STATUS': 'load_site'
            };
            senddata(JSON.stringify(data));
        }

        function load_doc() {
            var search = $('#datepicker').val();
            var siteCode = "<?php echo $siteCode ?>";
            var status = 'load_doc_tracking';
            var Menu = "<?php echo $Menu?>";
            var data = {
                'search': search,
                'siteCode': siteCode,
                'Menu': Menu,
                'STATUS': status
            };
            senddata(JSON.stringify(data));
        }

        var x = setInterval(function() {
            load_doc();
            console.log(111);
        }, 1000);

        function show_process(DocNo, From) {
            var siteCode = '<?php echo $siteCode ?>';
            var Menu = '<?php echo $Menu ?>';
            window.location.href = 'track_doc.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + DocNo + '&From=' + From;

        }

        function back() {
            window.location.href = "menu.php";
        }

        function logout(num) {
            if (num == 0) {
                var data = {
                    'Confirm': 1,
                    'STATUS': 'logout'
                };
                senddata(JSON.stringify(data));
            } else if (num == 1) {
                swal({
                    title: '<?php echo $genarray['logout'][$language]; ?>',
                    text: '<?php echo $genarray['wantlogout'][$language]; ?>',
                    type: 'question',
                    showCancelButton: true,
                    showConfirmButton: true,
                    cancelButtonText: '<?php echo $genarray['isno'][$language]; ?>',
                    confirmButtonText: '<?php echo $genarray['yes'][$language]; ?>',
                    reverseButton: true,
                }).then(function() {
                    var data = {
                        'Confirm': num,
                        'STATUS': 'logout'
                    };
                    senddata(JSON.stringify(data));
                });
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
                success: function(result) {
                    try {
                        var temp = $.parseJSON(result);
                    } catch (e) {
                        console.log('Error#542-decode error');
                    }
                    console.log(555);
                    if (temp["status"] == 'success') {
                        if (temp["form"] == 'load_site') {
                            $("#HptName").text(temp['HptName']);
                        } else if (temp["form"] == 'load_doc') {
                            f=true;
                            $(".btn.btn-mylight.btn-block").remove();
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                var status_class = "";
                                var status_text = "";
                                var status_line = "";
                                var onclick = "show_process(\"" + temp[i]['DocNo'] + "\",\"" + temp[i]['From'] + "\")";

                                if (temp[i]['IsProcess'] == 0 || temp[i]['IsProcess'] == null) {
                                    status_class = "status4";
                                    status_text = "โรงซักรับเอกสาร";
                                    status_line = "StatusLine_4";
                                    var onclick = "";
                                } else if (temp[i]['IsProcess'] == 1) {
                                    status_class = "status3";
                                    status_text = "กำลังดำเนินการซัก";
                                    status_line = "StatusLine_3";
                                } else if (temp[i]['IsProcess'] == 2) {
                                    status_class = "status3";
                                    status_text = "กำลังดำเนินการแพ็ค";
                                    status_line = "StatusLine_3";
                                } else if (temp[i]['IsProcess'] == 3) {
                                    status_class = "status3";
                                    status_text = "กำลังดำเนินการขนส่ง";
                                    status_line = "StatusLine_3";
                                } else if (temp[i]['IsProcess'] == 4) {
                                    status_class = "status2";
                                    status_text = "เสร็จสิ้น";
                                    status_line = "StatusLine_2";
                                }

                                if (temp[i]['IsStatus'] > 0) {

                                    var Str = "<button onclick='" + onclick + "' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5'>";
                                    Str += "<div class='row justify-content-end align-items-center'><div class='card " + status_class + "'>" + status_text + "</div>";
                                    Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                    Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['DocNo'] + "</div><div class='font-weight-light'>" + temp[i]['DepName'] + "</div></div></div></button>";

                                    $("#document").append(Str);
                                }
                            }
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        if (temp["form"] == 'load_doc') {
                            if(f){
                                $(".btn.btn-mylight.btn-block").remove();
                                swal({
                                    title: '',
                                    text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?>' + $('#datepicker').val(),
                                    type: 'warning',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    confirmButtonText: 'Data found'
                                })
                                f=false;
                            }

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

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center my-4">
            <h4 id="HptName" class="text-truncate"></h4>
        </div>
        <div id="document">
            <div class="d-flex justify-content-center mb-3">
                <input id="datepicker" class="text-truncate text-center" width="276" placeholder='<?php echo $genarray['CreateDocDate'][$language]; ?>' disabled />
                <button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
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