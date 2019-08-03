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

        $(document).ready(function(e) {
            var DocNo = "<?php echo $DocNo ?>";
            $("#DocNo").text(DocNo);
            load_items();
            // $('#md_question').on('hidden.bs.modal', function (e) {
            //     close_question();
            // })
            // $("#md_detail").modal('show');
        });

        function load_items() {
            var DocNo = "<?php echo $DocNo ?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'load_items'
            };
            senddata(JSON.stringify(data));
        }

        function make_number() {
            $('.numonly').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, ''); //<-- replace all other than given set of values\
                this.value = Number(this.value);
            });
        }

        function show_quantity(ItemCode) {
            var DocNo = '<?php echo $DocNo ?>';
            var data = {
                'DocNo': DocNo,
                'ItemCode': ItemCode,
                'STATUS': 'show_quantity'
            };
            senddata(JSON.stringify(data));
        }

        function save_checkpass() {
            var qty = $("#qc_qty").val();
            var pass = Number($("#qc_pass").val());
            var fail = Number($("#qc_fail").val());
            var sum = Number(pass+fail);
            if (sum != qty) {
                Title = "จำนวนไม่ถูกต้อง";
                Text = "จำนวนข้อมูล "+sum+" จากทั้งหมด "+qty+" !";
                Type = "warning";
                AlertError(Title,Text,Type);
            }
            else {
                var DocNo = '<?php echo $DocNo ?>';
                var ItemCode = $("#qc_qty").attr("data-itemcode");
                var data = {
                    'DocNo': DocNo,
                    'ItemCode': ItemCode,
                    'pass': pass,
                    'fail': fail,
                    'STATUS': 'save_checkpass'
                };
                senddata(JSON.stringify(data));
            }
            
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

        // function chk_items(num) {
        //     var DocNo = '<?php echo $DocNo ?>';
        //     var ItemCode = $("#question"+num).data("itemcode");
        //     var Question = $("#question"+num).data("question");
        //     var chk_id = "#chk" + num;
        //     var unchk_id = "#unchk" + num;
        //     var IsStatus = 1;

        //     if ($(chk_id).is(':checked') == true && $(unchk_id).is(':checked') == false) {
        //         IsStatus = 0;
        //         $(unchk_id).prop("checked", true);

        //     } else if ($(chk_id).is(':checked') == false && $(unchk_id).is(':checked') == true) {
        //         $(chk_id).prop("checked", true);
        //     }

        //     var data = {
        //         'DocNo': DocNo,
        //         'IsStatus': IsStatus,
        //         'ItemCode': ItemCode,
        //         'question': Question,
        //         'STATUS': 'chk_items'
        //     };
        //     senddata(JSON.stringify(data));
        // }

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

        function save_checklist(){
            var max = Number($("#qc_fail").val());
            var over_max = 0;
            var sum_amount = 0;
            var DocNo = '<?php echo $DocNo ?>';
            var ItemCode = $("#item_code").text();
            var sum = Number($("#save_checklist").attr("data-sumquestion"));
            var arr_question = [];
            var arr_amount = [];
            for (var i = 0; i < sum; i++) {
                var id = "#question"+i;
                var QuestID = $(id).attr("data-question");
                var Amount = Number($(id).val());
                sum_amount = Number(sum_amount) + Number(Amount);
                arr_question.push(QuestID);
                arr_amount.push(Amount);
                if (Amount > max) {
                    over_max = 1;
                }
            }
            Title = "จำนวนไม่ถูกต้อง";
            Type = "warning";
            if (over_max == 1) {
                arr_question = [];
                arr_amount = [];
                Text = "จำนวนสูงสุดของแต่ละชิ้นคือ "+max+" !";
                AlertError(Title,Text,Type);
            }
            else if (sum_amount < max) {
                arr_question = [];
                arr_amount = [];
                Text = "จำนวนข้อมูล "+sum_amount+" จากทั้งหมด "+max+" !";
                AlertError(Title,Text,Type);
            }
            else {
                var question = arr_question.join(',');
                var amount = arr_amount.join(',');

                var data = {
                    'DocNo': DocNo,
                    'ItemCode': ItemCode,
                    'question': question,
                    'amount': amount,
                    'STATUS': 'save_checklist'
                };
                senddata(JSON.stringify(data));
            }
        }

        function claim_detail(DocNo,ItemCode) {
            var data = {
                'DocNo': DocNo,
                'ItemCode': ItemCode,
                'STATUS': 'claim_detail'
            };
            senddata(JSON.stringify(data));
        }

        function show_claim_detail(ItemCode){
            var DocNo = '<?php echo $DocNo ?>';
            var data = {
                'DocNo': DocNo,
                'ItemCode': ItemCode,
                'STATUS': 'show_claim_detail'
            };
            senddata(JSON.stringify(data));
        }

        function create_claim() {
            var DocNo = '<?php echo $DocNo ?>';
            var Userid = '<?php echo $Userid ?>';

            var data = {
                'DocNo': DocNo,
                'Userid': Userid,
                'STATUS': 'create_claim'
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
            var Menu = '<?php echo $Menu; ?>';
            window.location.href = 'qc.php?siteCode=' + siteCode + '&Menu=' + Menu;
        }

        function logout(num) {
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
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
                            var op_claim = 0;
                            var test = temp['cnt'];
                            if (temp['cnt'] > 0) {
                                for (var i = 0; i < temp['cnt']; i++) {
                                    var CheckList = Number(temp[i]['IsCheckList']);
                                    var img = "";
                                    var detail = "<button onclick='event.cancelBubble=true;show_claim_detail(\""+temp[i]['ItemCode']+"\");' class='btn btn-info'>เรียกดู</button>";
                                    switch (CheckList) {
                                        case 0:
                                            img = "../img/Status_3.png"; // เขียว
                                            detail = "-";
                                            break;
                                        case 1:
                                            img = "../img/Status_1.png"; // ส้ม
                                            op_claim++;
                                            break;
                                        case 2:
                                            img = "../img/Status_1.png"; 
                                            op_claim++;
                                            break;
                                        case 3:
                                            img = "../img/Status_2.png"; // แดง
                                            op_claim++;
                                            break;
                                        default:
                                            img = "../img/Status_4.png"; // เทา
                                            detail = "-";
                                    }
                                    
                                    var num = i+1;
                                    var Str = "<tr onclick='show_quantity(\""+temp[i]['ItemCode']+"\")'><td><div class='row'>";
                                        Str += "<div scope='row' class='col-2 d-flex align-items-center justify-content-center'>"+num+"</div>";
                                        Str += "<div class='col-6'><div class='row'><div class='col-12 text-truncate font-weight-bold p-1'>"+temp[i]['ItemName']+"</div>";
                                        Str += "<div class='col-12 text-black-50 p-1'>จำนวน "+temp[i]['Qty']+" / น้ำหนัก "+temp[i]['Weight']+" </div></div></div>";
                                        Str += "<div class='col-2 d-flex align-items-center justify-content-center p-0'>"+detail+"</div>";
                                        Str += "<div class='col-2 d-flex align-items-center justify-content-center'><img src='"+img+"' height='40px'></div></div></td></tr>";

                                    $("#item").append(Str);
                                }

                                if (op_claim > 0) {
                                    $("#claim-btn").show();
                                    $("#save-btn").hide();
                                } else {
                                    $("#claim-btn").hide();
                                    $("#save-btn").show();
                                }
                            }
                            else {
                                $("#claim-btn").hide();
                                $("#save-btn").hide();

                                Title = "ข้อมูลว่างเปล่า";
                                Text = "ยังไม่มีข้อมูลรายการ !";
                                Type = "info";
                                AlertError(Title,Text,Type);
                            }

                        } else if (temp["form"] == 'show_quantity') {
                            $(".item_name").text(temp['ItemName']);
                            $("#qc_qty").attr("data-itemcode",temp['ItemCode']);
                            $("#qc_qty").val(temp['Qty']);
                            var Pass = temp['Pass'];
                            var Fail = temp['Fail'];
                            if (temp['Pass'] == null || temp['Pass'] == "") {
                                Pass = 0;
                            }
                            if (temp['Fail'] == null || temp['Fail'] == "") {
                                Fail = 0;
                            }
                            $("#qc_pass").val(Pass);
                            $("#qc_fail").val(Fail);

                            $("#md_checkpass").modal('show');

                        } else if (temp["form"] == 'show_question') {
                            $("#item_code").text(temp['ItemCode']);
                            $(".item_name").text(temp['ItemName']);
                            $("#question").empty();
                            var sum_question = 0;
                            for (var i = 0; i < temp['cnt']; i++) {
                                var chk = "";
                                var unchk = "";
                                if (temp[i]['IsStatus'] == 1) {
                                    chk = "checked";
                                } else if (temp[i]['IsStatus'] == 0) {
                                    unchk = "checked";
                                }
                                var Str = "<div class='my-btn btn-block alert alert-info py-1 px-3 mb-2'><div class='col-12 text-left font-weight-bold pr-0'>";
                                    Str += "<div>"+temp[i]['Question']+"</div></div><div class='col-12 text-truncate p-0'><div class='form-check form-check-inline m-0'>";
                                    Str += "ไม่ผ่าน<input onkeydown='make_number()' id='question"+i+"' class='form-control text-center m-2 numonly' type='text' ";
                                    Str += "data-itemcode='"+temp['ItemCode']+"' data-question='"+temp[i]['QuestionId']+"' value='"+temp[i]['Qty']+"'>จำนวน</div></div></div>";
                                $("#question").append(Str);
                                sum_question++;
                            }
                            $("#save_checklist").attr('data-sumquestion',sum_question);
                            $("#md_question").modal('show');

                        } else if (temp["form"] == 'save_checkpass') {
                            $("#md_checkpass").modal('hide');
                            if (temp['unfail'] == 1) {
                                load_items();
                            } else {
                                show_question(temp['ItemCode']);
                            }

                        } else if (temp["form"] == 'close_question') {
                            // var DocNo = temp['DocNo'];
                            // var ItemCode = temp['ItemCode'];
                            // claim_detail(DocNo,ItemCode);
                            load_items();

                        } else if (temp["form"] == 'show_claim_detail') {
                            if (temp['cnt'] > 0) {
                                $("#detail").empty();
                                for (var i = 0; i < temp['cnt']; i++) {
                                    if (temp[i]['Qty'] != 0){
                                        var Str = "<div class='my-btn btn-block alert alert-info py-1 px-3 mb-2'><div class='col-12 text-left font-weight-bold pr-0'>";
                                            Str += "<div>"+temp[i]['Question']+"</div></div><div class='col-12 text-truncate p-0'><div class='form-check form-check-inline m-0'>";
                                            Str += "ไม่ผ่าน<input onkeydown='make_number()' id='question"+i+"' class='form-control text-center m-2 numonly' type='text' ";
                                            Str += "value='"+temp[i]['Qty']+"' disabled>จำนวน</div></div></div>";

                                        $("#detail").append(Str);
                                    }
                                }
                                $("#md_detail").modal('show');
                            }
                            

                        } else if (temp["form"] == 'claim_detail') {
                            $("#md_claim").modal('show');

                        } else if (temp["form"] == 'save_checklist') {
                            $("#md_question").modal('hide');
                            load_items();

                        } else if (temp["form"] == 'create_claim') {
                            save_qc();

                        } else if (temp["form"] == 'create_rewash') {
                            var NewDocNo = temp['NewDocNo'];
                            send_rewash(NewDocNo);

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
                        else if (temp["form"] == 'save_checklist') {
                            
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
                            <div class="col-2 text-center p-0"><?php echo $array['no'][$language]; ?></div>
                            <div class="col-6 text-center p-0"><?php echo $array['List'][$language]; ?></div>
                            <div class="col-2 text-center p-0">สาเหตุ</div>
                            <div class="col-2 text-center p-0"><?php echo $array['Status'][$language]; ?></div>
                        </div>
                    </th>
                    </tr>
                </thead>
                <tbody id="item">

                    <!-- <tr>
                    <td>
                        <div class="row">
                            <div scope="row" class="col-2 d-flex align-items-center justify-content-center">1</div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-12 text-truncate font-weight-bold p-1">ผ้าเช็ดปาก</div>
                                    <div class="col-12 text-black-50 p-1">จำนวน 0 / น้ำหนัก 0 </div>
                                </div>
                            </div>
                            <div class="col-2 d-flex align-items-center justify-content-center p-0">
                                <button class="btn btn-info">เรียกดู</button>
                            </div>
                            <div class="col-2 d-flex align-items-center justify-content-center p-0">
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
                    <button onclick="create_claim()" id="claim-btn" class="btn btn-danger btn-block" type="button">
                        <i class="fas fa-times mr-1"></i><?php echo $array['sendClaim'][$language]; ?>
                    </button>
                    <button onclick="save_qc()" id="save-btn" class="btn btn-success btn-block" type="button">
                        <i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="md_checkpass" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate"><?php echo $array['Checkamount'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div class="font-weight-bold mb-2 item_name"></div>
                    <div id="amount">

                        <div class="form-group text-left">
                            <label>จำนวนทั้งหมด</label>
                            <input type="text" class="form-control" id="qc_qty" disabled>
                        </div>

                        <div class="form-group text-left">
                            <label>จำนวนที่ผ่าน</label>
                            <input onkeydown='make_number()' type="text" class="form-control numonly" id="qc_pass">
                        </div>

                        <div class="form-group text-left">
                            <label>จำนวนที่ไม่ผ่าน</label>
                            <input onkeydown='make_number()' type="text" class="form-control numonly" id="qc_fail">
                        </div>

                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                            <button onclick="save_checkpass()" type="button" class="btn btn-success mx-2"><?php echo $genarray['save'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <div class="font-weight-bold mb-2 item_name"></div>
                    <div id="question">

                        <!-- <button onclick="chk_items(10)" id="question0" data-itemcode="99999" data-question="121" class="my-btn btn-block alert alert-info py-1 px-3 mb-2">
                            <div class='col-12 text-left font-weight-bold pr-0'>
                                Ambitioni dedisse scripsisse iudicaretur. Cras mattis iudicium purus sit amet fermentum. Donec sed odio operae, eu vulputate felis rhoncus. Praeterea iter est quasdam res quas ex communi. At nos hinc posthac, sitientis piros Afros. Petierunt uti sibi concilium totius Galliae in diem certam indicere. Cras mattis iudicium purus sit amet fermentum.
                            </div>
                            <div class="col-12 text-truncate p-0">
                                <div class="form-check form-check-inline m-0">
                                    ไม่ผ่าน
                                    <input class="form-control text-center m-2 numonly" type="text">
                                    จำนวน
                                </div>
                            </div>
                        </button> -->

                    </div>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                            <button onclick="save_checklist()" type="button" id="save_checklist" class="btn btn-success mx-2"><?php echo $genarray['save'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="md_detail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate"><?php echo $array['Notthrough'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <div id="item_code" hidden></div>
                    <div class="font-weight-bold mb-2 item_name"></div>

                    <div id="detail"></div>

                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-block btn-secondary mx-2" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="md_claim_rewash" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <div class="font-weight-bold mb-2 item_name"></div>

                    <div class="row alert alert-info mb-3 mx-2 p-0">
                        <div class="text-truncate font-weight-bold ml-3 mt-2">ส่งเคลม</div>

                        <div class="row w-100 mx-2">
                            <div class="d-flex align-items-center col-md-8 col-7">
                                <div class="text-truncate">Repair</div>
                            </div>
                            <div class="d-flex align-items-center col-md-4 col-5 pl-0">
                                <input type="text" class="form-control rounded text-center bg-white my-2 mr-1 numonly">
                                <div class="">จำนวน</div>
                            </div>
                        </div>

                        <div class="row w-100 mx-2">
                            <div class="d-flex align-items-center col-md-8 col-7">
                                <div class="text-truncate">Damage</div>
                            </div>
                            <div class="d-flex align-items-center col-md-4 col-5 pl-0">
                                <input type="text" class="form-control rounded text-center bg-white my-2 mr-1 numonly">
                                <div class="">จำนวน</div>
                            </div>
                        </div>
                    </div>

                    <div class="row alert alert-info mb-3 mx-2 p-0">
                        <div class="text-truncate font-weight-bold ml-3 mt-2">ส่งซักอีกครั้ง</div>

                        <div class="row w-100 mx-2">
                            <div class="d-flex align-items-center col-md-8 col-7">
                                <div class="text-truncate">Rewash</div>
                            </div>
                            <div class="d-flex align-items-center col-md-4 col-5 pl-0">
                                <input type="text" class="form-control rounded text-center bg-white my-2 mr-1 numonly" disabled>
                                <div class="">จำนวน</div>
                            </div>
                        </div>
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