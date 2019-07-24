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
// $Userid = $_GET['user'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/QC_lang.xml');
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

        var arr_claim_code = [];
        var arr_claim_qty = [];
        var arr_claim_weight = [];
        var arr_claim_unit = [];

        var arr_rewash_code = [];
        var arr_rewash_qty = [];
        var arr_rewash_weight = [];
        var arr_rewash_unit = [];

        $(document).ready(function(e) {
            var DocNo = "<?php echo $DocNo ?>";
            $("#DocNo").text(DocNo);
            load_items();
            $('#md_question').on('hidden.bs.modal', function (e) {
                close_question();
            })
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
                'ItemCode': ItemCode,
                'question': Question,
                'STATUS': 'chk_items'
            };
            senddata(JSON.stringify(data));
        }

        function close_question() {
            var DocNo = '<?php echo $DocNo ?>';
            var ItemCode = $("#item_code").text();

            var data = {
                'DocNo': DocNo,
                'ItemCode': ItemCode,
                'STATUS': 'close_question'
            };
            senddata(JSON.stringify(data));
        }

        function claim_click() {
            create_claim();
            create_rewash();
        }

        function create_claim() {
            if (arr_claim_code.length > 0) {
                var DocNo = '<?php echo $DocNo ?>';
                var Userid = '<?php echo $Userid ?>';

                var data = {
                    'DocNo': DocNo,
                    'Userid': Userid,
                    'STATUS': 'create_claim'
                };
                senddata(JSON.stringify(data));
            }
        }

        function send_claim(NewDocNo) {
            var claim_code = arr_claim_code.join(',');
            var claim_qty = arr_claim_qty.join(',');
            var claim_weight = arr_claim_weight.join(',');
            var claim_unit = arr_claim_unit.join(',');

            var data = {
                'DocNo': NewDocNo,
                'claim_code': claim_code,
                'claim_qty': claim_qty,
                'claim_weight': claim_weight,
                'claim_unit': claim_unit,
                'STATUS': 'send_claim'
            };
            senddata(JSON.stringify(data));
        }

        function create_rewash() {
            if (arr_rewash_code.length > 0) {
                var DocNo = '<?php echo $DocNo ?>';
                var Userid = '<?php echo $Userid ?>';

                var data = {
                    'DocNo': DocNo,
                    'Userid': Userid,
                    'STATUS': 'create_rewash'
                };
                senddata(JSON.stringify(data));
            }
        }

        function send_rewash(NewDocNo) {
            var rewash_code = arr_rewash_code.join(',');
            var rewash_qty = arr_rewash_qty.join(',');
            var rewash_weight = arr_rewash_weight.join(',');
            var rewash_unit = arr_rewash_unit.join(',');

            var data = {
                'DocNo': NewDocNo,
                'rewash_code': rewash_code,
                'rewash_qty': rewash_qty,
                'rewash_weight': rewash_weight,
                'rewash_unit': rewash_unit,
                'STATUS': 'send_rewash'
            };
            senddata(JSON.stringify(data));
        }

        function save_qc() {
            var DocNo = $("#DocNo").text();
            var data = {
                'DocNo': DocNo,
                'STATUS': 'save_qc'
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
                            arr_claim_code = [];
                            arr_claim_qty = [];
                            arr_claim_weight = [];
                            arr_claim_unit = [];

                            arr_rewash_code = [];
                            arr_rewash_qty = [];
                            arr_rewash_weight = [];
                            arr_rewash_unit = [];

                            $("#item").empty();
                            var op_claim = 0;
                            var test = temp['cnt'];
                            
                            for (var i = 0; i < temp['cnt']; i++) {
                                var CheckList = Number(temp[i]['IsCheckList']);
                                var img = "";
                                if (CheckList == 3 || CheckList == 4 || CheckList == 6) {
                                }

                                switch (CheckList) {
                                    case 0:
                                        img = "../img/Status_3.png"; // เขียว
                                        break;
                                    case 1:
                                        img = "../img/Status_1.png"; // ส้ม
                                        arr_claim_code.push(temp[i]['ItemCode']);
                                        arr_claim_qty.push(temp[i]['Qty']);
                                        arr_claim_weight.push(temp[i]['Weight']);
                                        arr_claim_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    case 2:
                                        img = "../img/Status_2.png"; // แดง
                                        arr_claim_code.push(temp[i]['ItemCode']);
                                        arr_claim_qty.push(temp[i]['Qty']);
                                        arr_claim_weight.push(temp[i]['Weight']);
                                        arr_claim_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    case 3:
                                        img = "../img/Status_1.png";
                                        arr_rewash_code.push(temp[i]['ItemCode']);
                                        arr_rewash_qty.push(temp[i]['Qty']);
                                        arr_rewash_weight.push(temp[i]['Weight']);
                                        arr_rewash_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    case 4:
                                        img = "../img/Status_2.png";
                                        arr_rewash_code.push(temp[i]['ItemCode']);
                                        arr_rewash_qty.push(temp[i]['Qty']);
                                        arr_rewash_weight.push(temp[i]['Weight']);
                                        arr_rewash_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    case 5:
                                        img = "../img/Status_1.png";
                                        arr_claim_code.push(temp[i]['ItemCode']);
                                        arr_claim_qty.push(temp[i]['Qty']);
                                        arr_claim_weight.push(temp[i]['Weight']);
                                        arr_claim_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    case 6:
                                        img = "../img/Status_2.png";
                                        arr_rewash_code.push(temp[i]['ItemCode']);
                                        arr_rewash_qty.push(temp[i]['Qty']);
                                        arr_rewash_weight.push(temp[i]['Weight']);
                                        arr_rewash_unit.push(temp[i]['UnitCode']);
                                        op_claim++;
                                        break;
                                    default:
                                        img = "../img/Status_4.png"; // เทา
                                        op_claim++;

                                }
                                
                                var num = i+1;
                                var Str = "<tr onclick='show_question(\""+temp[i]['ItemCode']+"\")'><td><div class='row'><div scope='row' class='col-3 d-flex align-items-center justify-content-center'>"+num+"</div>";
                                    Str += "<div class='col-6'><div class='row'><div class='col-12 text-truncate font-weight-bold p-1'>"+temp[i]['ItemName']+"</div>";
                                    Str += "<div class='col-12 text-black-50 p-1'>จำนวน "+temp[i]['Qty']+" / น้ำหนัก "+temp[i]['Weight']+" </div></div></div>";
                                    Str += "<div class='col-3 d-flex align-items-center justify-content-center'><img src='"+img+"' height='40px'></div></div></td></tr>";

                                $("#item").append(Str);
                            }
                            // alert(arr_rewash_code[0]);
                            // alert(arr_rewash_code[1]);
                            if (op_claim > 0) {
                                $("#claim-btn").show();
                                $("#save-btn").hide();
                            } else {
                                $("#claim-btn").hide();
                                $("#save-btn").show();
                            }

                        } else if (temp["form"] == 'show_question') {
                            $("#item_code").text(temp['ItemCode']);
                            $("#item_name").text(temp['ItemName']);
                            $("#question").empty();
                            var length = Object.keys(temp).length;
                            // alert(length);
                            for (var i = 0; i < (Object.keys(temp).length - 5); i++) {
                                var chk = "";
                                var unchk = "";
                                if (temp[i]['IsStatus'] == 1) {
                                    chk = "checked";
                                } else if (temp[i]['IsStatus'] == 0) {
                                    unchk = "checked";
                                }
                                var Str = "<button onclick='chk_items("+i+")' id='question"+i+"' data-itemcode='"+temp['ItemCode']+"' data-question='"+temp[i]['QuestionId']+"' class='my-btn btn-block alert alert-info py-1 px-3 mb-2'>";
                                    Str += "<div class='col-12 text-left font-weight-bold pr-0'>";
                                    Str += "<div>"+temp[i]['Question']+"</div></div><div class='col-12 text-truncate text-right p-0'><div class='form-check form-check-inline m-0'>";
                                    Str += "<input class='form-check-input' type='radio' name='radio"+i+"' id='chk"+i+"' "+chk+">ผ่าน";
                                    Str += "<input class='form-check-input ml-3' type='radio' name='radio"+i+"' id='unchk"+i+"' "+unchk+">ไม่ผ่าน</div></div></button>";

                                // alert("array : "+i);
                                // alert(temp[i]['QuestionId']);
                                $("#question").append(Str);
                            }
                            $("#md_question").modal('show');

                        } else if (temp["form"] == 'close_question') {
                            load_items();

                        } else if (temp["form"] == 'create_claim') {
                            var NewDocNo = temp['NewDocNo'];
                            send_claim(NewDocNo);

                        } else if (temp["form"] == 'create_rewash') {
                            var NewDocNo = temp['NewDocNo'];
                            send_rewash(NewDocNo);

                        } else if (temp["form"] == 'send_claim' || temp["form"] == 'send_rewash') {
                            //alert("10222");
                            save_qc();

                        } else if (temp["form"] == 'save_qc') {
                            var siteCode = '<?php echo $siteCode ?>';
                            var Menu = '<?php echo $Menu ?>';
                            window.location.href = 'qc.php?siteCode='+siteCode+'&Menu='+Menu;
                            
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
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3 mb-5" style="font-family:sans-serif;">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center mb-3">
            <h4 class="text-truncate"><?php echo $genarray['Document'][$language]; ?></h4>
            <div id="DocNo" class="text-truncate"></div>
        </div>
        <div class="row justify-content-center px-3">
            <table class="table table-hover col-lg-9 col-md-10 col-sm-12">
                <thead>
                    <tr class="bg-primary text-white">
                    <th scope="col">
                        <div class="row">
                            <div class="col-3 text-center"><?php echo $array['no'][$language]; ?></div>
                            <div class="col-6 text-center"><?php echo $array['List'][$language]; ?></div>
                            <div class="col-3 text-center"><?php echo $array['Status'][$language]; ?></div>
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
            <div class="row py-1 px-3">
                    <button onclick="claim_click()" id="claim-btn" class="btn btn-danger btn-block" type="button" data-toggle="modal" data-target="#">
                        <i class="fas fa-times mr-1"></i><?php echo $array['sendClaim'][$language]; ?>
                    </button>
                    <button onclick="save_qc()" id="save-btn" class="btn btn-success btn-block" type="button" data-toggle="modal" data-target="#">
                        <i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
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
                    <h5 class="modal-title text-truncate"><?php echo $array['Checktopic'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div id="item_code" hidden></div>
                    <div id="item_name" class="font-weight-bold mb-2"></div>
                    <div id="question">

                        <!-- <button onclick="chk_items(10)" id="question0" data-itemcode="99999" data-question="121" class="my-btn btn-block alert alert-info py-1 px-3 mb-2">
                            <div class='col-12 text-left font-weight-bold pr-0'>
                                Ambitioni dedisse scripsisse iudicaretur. Cras mattis iudicium purus sit amet fermentum. Donec sed odio operae, eu vulputate felis rhoncus. Praeterea iter est quasdam res quas ex communi. At nos hinc posthac, sitientis piros Afros. Petierunt uti sibi concilium totius Galliae in diem certam indicere. Cras mattis iudicium purus sit amet fermentum.
                            </div>
                            <div class="col-12 text-truncate text-right p-0">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="radio10" id="chk10" value="option1" checked>
                                    ผ่าน
                                    <input class="form-check-input ml-3" type="radio" name="radio10" id="unchk10" value="option2">
                                    ไม่ผ่าน
                                </div>
                            </div>
                        </button> -->

                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-block btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>