<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
    header("location:../index.html");
}
$DepCode = $_GET['depCode'];
$language = $_SESSION['lang'];
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
    $Menu = $_GET['Menu'];
    if ($Menu == 'dirty') {
        echo "<title>" . $genarray['titledirty'][$language] . $array['title'][$language] . "</title>";
    } else if ($Menu == 'factory') {
        echo "<title>" . $genarray['titlefactory'][$language] . $array['title'][$language] . "</title>";
    } else if ($Menu == 'clean') {
        echo "<title>" . $genarray['titleclean'][$language] . $array['title'][$language] . "</title>";
    } else if ($Menu == 'qc') {
        echo "<title>" . $genarray['titleQC'][$language] . $array['title'][$language] . "</title>";
    }
    ?>

    <?php
    require 'script_css.php';
    require 'logout_fun.php';
    require '../getTimeZone.php';
    ?>

    <script>
        $(document).ready(function(e) {
            $('#datepicker').val("<?php echo date("Y-m-d"); ?>");
            load_Doc();
        });

        function load_Doc() {
            var DepCode = '<?php echo $DepCode; ?>';
            var search = $('#datepicker').val();
            var data = {
                'search': search,
                'DepCode': DepCode,
                'STATUS': 'load_Doc'
            };
            senddata(JSON.stringify(data));
        }

        function show_doc(DocNo) {
            window.location.href = 'shelfcount_detail.php?depCode=' + depCode+'&DocNo='+DocNo;
        }

        function back() {
            window.location.href = "shelfcount_dep.php";
            //logout(1);
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/shelfcount.php';
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
                        if (temp["form"] == 'load_Doc') {
                            var DepCode = '<?php echo $DepCode; ?>';
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {

                                if (temp[i]['IsStatus'] == 0) {
                                    status_class = "status1";
                                    status_text = "หยุดชั่วขณะ";
                                    status_line = "StatusLine_1";
                                } else if (temp[i]['IsStatus'] == 1 || temp[i]['IsStatus'] == 2) {
                                    status_class = "status3";
                                    status_text = "กำลังดำเนินการ";
                                    status_line = "StatusLine_3";
                                } else {
                                    status_class = "status2";
                                    status_text = "เสร็จสิ้น";
                                    status_line = "StatusLine_2";
                                }

                                var Str = "<button onclick='show_process(\"" + temp[i]['DocNo'] + "\",0)' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
                                Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
                                Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div><div class='my-col-7 text-left'>";
                                Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['DocNo'] + "</div><div class='font-weight-light'>" + DepCode + "</div></div></div></button>";

                                $("#shelfcount").append(Str);
                            }
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        swal({
                            title: '',
                            text: "<?php echo $genarray['notfoundDocInDate'][$language]; ?>",
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

            <div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3 pb-4 mb-5">

        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div id="shelfcount">
            <div class="d-flex justify-content-center mb-3">
                <input id="datepicker" class="text-truncate text-center" width="276" placeholder='<?php echo $genarray['CreateDocDate'][$language]; ?>' disabled />
                <button onclick="load_Doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
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