<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
    header("location:../index.html");
}
$siteCode = $_GET['siteCode'];
$Menu = $_GET['Menu'];
$DocNo = $_GET['DocNo'];
$DepCode = $_GET['DepCode'];
$Userid = $_GET['user'];
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
    <link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <script src="../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.css">

    <script src="../js/gijgo.min.js" type="text/javascript"></script>
    <link href="../css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
    <script>

        $(document).ready(function(e) {
            var DocNo = "<?php echo $DocNo ?>";
            $("#DocNo").text(DocNo);
            load_items();
        });

        function load_items() {
            var DocNo = "<?php echo $DocNo ?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'load_items'
            };
            senddata(JSON.stringify(data));
        }

        function show_question(ItemCode) {
            var DocNo = '<?php echo $DocNo ?>';
            var data = {
                'DocNo': DocNo,
                'ItemCode': ItemCode,
                'STATUS': 'show_question'
            };
            senddata(JSON.stringify(data));
        }

        function chk_items(num) {
            var DocNo = '<?php echo $DocNo ?>';
            var ItemCode = $("#question"+num).data("itemcode");
            var Question = $("#question"+num).data("question");
            var chk_id = "#chk" + num;
            var unchk_id = "#unchk" + num;
            var IsStatus = 1;

            if ($(chk_id).is(':checked') == true && $(unchk_id).is(':checked') == false) {
                IsStatus = 0;
                $(unchk_id).prop("checked", true);

            } else if ($(chk_id).is(':checked') == false && $(unchk_id).is(':checked') == true) {
                $(chk_id).prop("checked", true);
            }

            var data = {
                'DocNo': DocNo,
                'IsStatus': IsStatus,
                'STATUS': 'chk_items'
            };
            senddata(JSON.stringify(data));
        }

        function back() {
            var siteCode = '<?php echo $siteCode; ?>';
            var Menu = <?php echo $Menu; ?>;
            window.location.href = 'qc.php?siteCode=' + siteCode + '&Menu=' + Menu;
        }

        function logout(num) {
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var URL = '../process/qc_view.php';
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
                        if (temp["form"] == 'load_items') {
                            $("#item").empty();
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                var CheckList = temp[i]['IsCheckList'];
                                var img = "";

                                if(CheckList == 0 || CheckList == null){
                                    img = "../img/Status_4.png";
                                }
                                else if(CheckList == 1){
                                    img = "../img/Status_3.png";
                                }
                                else if(CheckList == 2){
                                    img = "../img/Status_1.png";
                                }

                                var num = i+1;
                                var Str = "<tr onclick='show_question(\""+temp[i]['ItemCode']+"\")'><td><div class='row'><div scope='row' class='col-3 d-flex align-items-center justify-content-center'>"+num+"</div>";
                                    Str += "<div class='col-6'><div class='row'><div class='col-12 text-truncate font-weight-bold p-1'>"+temp[i]['ItemName']+"</div>";
                                    Str += "<div class='col-12 text-black-50 p-1'>จำนวน "+temp[i]['Qty']+" / น้ำหนัก "+temp[i]['Weight']+" </div></div></div>";
                                    Str += "<div class='col-3 d-flex align-items-center justify-content-center'><img src='"+img+"' height='40px'></div></div></td></tr>";

                                $("#item").append(Str);
                            }
                        } else if (temp["form"] == 'show_question') {
                            $("#item_name").text(temp['ItemName']);
                            // $("#question").empty();
                            var length = Object.keys(temp).length;
                            // alert(length);
                            for (var i = 0; i < (Object.keys(temp).length - 5); i++) {
                                var id = "#text_question"+i;
                                // var Str = "<button onclick='chk_items('chk"+i+"')' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
                                //     Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>";
                                //     Str += "<div id='1111'>"+temp[i]['Question']+"</div></div><div class='col-12 text-truncate text-right'>";
                                //     Str += "<div class='form-check'><input class='m-0' type='radio' id='chk"+i+"' value='1' checked>ผ่าน";
                                //     Str += "<input class='ml-3' type='radio' id='unchk"+i+"' value='2'>ไม่ผ่าน</div></div></button>";

                                var Str = "<button onclick='chk_items("+i+")' id='question"+i+"' data-itemcode='"+temp[i]['ItemCode']+"' data-question='"+temp[i]['QuestionId']+"' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
                                    Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>";
                                    Str += "<div>"+temp[i]['Question']+"</div></div><div class='col-12 text-truncate text-right p-0'><div class='form-check form-check-inline m-0'>";
                                    Str += "<input class='form-check-input' type='radio' name='radio"+i+"' id='chk"+i+"' checked>ผ่าน";
                                    Str += "<input class='form-check-input ml-3' type='radio' name='radio"+i+"' id='unchk"+i+"'>ไม่ผ่าน</div></div></button>";

                                // alert("array : "+i);
                                // alert(temp[i]['Question']);
                                $("#question").append(Str);
                            }
                            $("#md_question").modal('show');
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }

                    } else if (temp['status'] == "failed") {
                        var message = "";
                        if (temp["form"] == 'choose_items') {
                            $("#choose_item").empty();
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
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i>กลับ</button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button">ออก<i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3 mb-5" style="font-family:sans-serif;">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center mb-3">
            <h4 class="text-truncate">เอกสาร</h4>
            <div id="DocNo" class="text-truncate"></div>
        </div>
        <p class="text-justify">Ambitioni dedisse scripsisse iudicaretur. Cras mattis iudicium purus sit amet fermentum. Donec sed odio operae, eu vulputate felis rhoncus. Praeterea iter est quasdam res quas ex communi. At nos hinc posthac, sitientis piros Afros. Petierunt uti sibi concilium totius Galliae in diem certam indicere. Cras mattis iudicium purus sit amet fermentum.</p>

        <div class="row justify-content-center px-3">
            <table class="table table-hover col-lg-9 col-md-10 col-sm-12">
                <thead>
                    <tr class="bg-primary text-white">
                    <th scope="col">
                        <div class="row">
                            <div class="col-3 text-center">สำดับ</div>
                            <div class="col-6 text-center">รายการ</div>
                            <div class="col-3 text-center">สถานะ</div>
                        </div>
                    </th>
                    </tr>
                </thead>
                <tbody id="item">

                    <!-- <tr>
                    <td>
                        <div class="row">
                            <div scope="row" class="col-3 d-flex align-items-center justify-content-center">1</div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-12 text-truncate font-weight-bold p-1">ผ้าเช็ดปาก</div>
                                    <div class="col-12 text-black-50 p-1">จำนวน 0 / น้ำหนัก 0 </div>
                                </div>
                            </div>
                            <div class="col-3 d-flex align-items-center justify-content-center">
                                <img src="../img/Status_4.png" height="40px">
                            </div>
                        </div>
                    </td>
                    </tr> -->

                </tbody>
            </table>
        </div>
    </div>

    <div id="add_doc" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
        <div class="col-lg-9 col-md-10 col-sm-12">
            <div class="row p-1">
                <div class="col-6">
                    <button onclick="" class="btn btn-danger btn-block" type="button" data-toggle="modal" data-target="#">
                        <i class="fas fa-times mr-1"></i>ส่งเคลม
                    </button>
                </div>
                <div class="col-6">
                    <button onclick="" class="btn btn-success btn-block" type="button" data-toggle="modal" data-target="#">
                        <i class="fas fa-save mr-1"></i>บันทึก
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="md_question" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate">หัวข้อตรวจสอบ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div id="item_name"></div>
                    <div id="question">

                        <button onclick="chk_items(0)" id="question0" data-itemcode="99999" data-question="121" class="btn btn-block alert alert-info py-1 px-3 mb-2">
                            <div class="col-12 text-justify pr-0 border border-danger">
                                99 X 99 สีขาว 999999999999999999999999999999999999999999999999999
                            </div>
                            <div class="col-12 text-truncate text-right p-0">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="radio0" id="chk0" value="option1" checked>
                                    ผ่าน
                                    <input class="form-check-input ml-3" type="radio" name="radio0" id="unchk0" value="option2">
                                    ไม่ผ่าน
                                </div>
                            </div>
                        </button>

                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-6 text-right">
                            <button id="btn_add_items" onclick="select_chk()" type="button" class="btn btn-success m-2">ยืนยัน</button>
                        </div>
                        <div class="col-6 text-left">
                            <button type="button" class="btn btn-danger m-2" data-dismiss="modal">ยกเลิก</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>