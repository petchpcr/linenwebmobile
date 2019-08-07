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
$refDoc = $_GET['RefDocNo'];
$DepCode = $_GET['DepCode'];
$Userid = $_GET['user'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/clean&dirty_view_lang.xml');
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
    $Menu = $_GET['Menu'];
    if ($Menu == 'dirty') {
        echo "<title>" . $genarray['titledirty'][$language] . $genarray['titleCreatedocno'][$language] . "</title>";
    } else if ($Menu == 'clean') {
        echo "<title>" . $genarray['titleclean'][$language] . $genarray['titleCreatedocno'][$language] . "</title>";
    }
    ?>

    <?php 
        require 'script_css.php'; 
        require 'logout_fun.php';
    ?>
    
    <script>
        var arr_old_items = [];
        var arr_new_items = [];
        var arr_del_items = [];

        $(document).ready(function(e) {
            var DocNo = "<?php echo $DocNo ?>";
            $("#DocNo").text(DocNo);
            load_items();
        });

        function load_items() {
            arr_old_items = [];
            arr_new_items = [];
            arr_del_items = [];
            var DocNo = "<?php echo $DocNo ?>";
            var data = {
                'DocNo': DocNo,
                'STATUS': 'load_items'
            };
            senddata(JSON.stringify(data));
        }

        function choose_items() {
            var DepCode = "<?php echo $DepCode ?>";
            var refDoc = "<?php echo $refDoc ?>";
            console.log(refDoc);
            var Search = $("#search_items").val();
            var data = {
                'DepCode': DepCode,
                'Search': Search,
                'refDoc' : refDoc,
                'STATUS': 'choose_items'
            };
            senddata(JSON.stringify(data));
        }

        function chk_items(chk) {
            var id = "#" + chk;
            if ($(id).is(':checked')) {
                $(id).prop("checked", false);
            } else {
                $(id).prop("checked", true);
            }
            var test = $(id).data("name");
        }

        function select_chk() {
            $("#md_item").modal('hide');
            var last_item = $('.item:last').data("num");
            if (last_item == null || last_item == '') {
                last_item = 0;
            }
            var num = Number(last_item) + 1;

            $(".chk-item").each(function() {
                if ($(this).is(':checked')) {
                    var id = "weight" + num;
                    var name = $(this).data('name');
                    var code = $(this).val();
                    var qty = 0;
                    var unit = 1;
                    var Menu = '<?php echo $Menu; ?>';
                    if (Menu == 'dirty') {
                        var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
                        Str += "<div class='text-truncate font-weight-bold'>" + name + "</div></div>";
                        Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
                        Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item new numonly' ";
                        Str += "id='" + id + "' data-code='" + code + "' data-qty='" + qty + "' data-unit='" + unit + "' data-num='" + num + "'data-oldnum=0 placeholder='0.0'>";
                        Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'><button onclick='del_items(" + num + ")' class='btn btn-danger align-self-start mt-1 mr-1 px-2 py-0 rounded-circle'>x</button></div></div>";
                    }else{
                        var idqty= id+"qty";
                        var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'><div class='col'><div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
                        Str += "<div class='text-truncate font-weight-bold'>" + name + "</div></div><div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><?php echo $array['numberSize'][$language]; ?>";
                        Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 itemnum numonly'";
                        Str += "id='" + idqty + "' placeholder='0'>";
                        Str += "<button onclick='del_items(" + num + ")' class='btn btn-danger align-self-start mt-1 mr-1 px-2 py-0 rounded-circle'>x</button></div></div>";
                        Str += "<div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'></div>";
                        Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><?php echo $array['weight'][$language]; ?><input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item new numonly' ";
                        Str += "data-code='" + code + "' data-qty='" + qty + "' data-unit='" + unit + "' id='" + id + "' data-num='" + num + "' data-oldnum=0 placeholder='0.0'>";
                        Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'></div></div></div>";
                    }              

                    $("#items").append(Str);
                    arr_new_items.push(code);
                    num++;
                    cal_weight();
                    cal_num();
                };
            });
        }

        function del_items(num) {
            var item = "#item" + num;
            var input = "#weight" + num;
            var have = 0;
            var code = $(input).data("code");

            for (var i = 0; i < arr_del_items.length; i++) {
                if (arr_del_items[i] == code) {
                    have = 1;
                }
            }

            var old_i = arr_old_items.indexOf(code); // หา Index ของคำนั้น
            if (old_i != -1) {
                arr_old_items.splice(old_i, 1);
            } // ลบ Index ที่หาเจอ

            var new_i = arr_new_items.indexOf(code); // หา Index ของคำนั้น
            if (new_i != -1) {
                arr_new_items.splice(new_i, 1);
            } // ลบ Index ที่หาเจอ

            if (have == 0) {
                arr_del_items.push(code);
            }

            $(item).remove();
            // console.log("Del : " + arr_del_items);
            // console.log("Old : " + arr_old_items);
            // console.log("New : " + arr_new_items);
            cal_weight();
            cal_num();
        }

        function show_weight(num) {
            var id = "#weight" + num;
            var val = $(id).val();
            $("#val_weight").val(val);
            $("#val_weight").attr("data-num", num);
            $("#md_weight").modal('show');
        }

        function change_weight() {
            var num = $("#val_weight").attr("data-num");
            var val = $("#val_weight").val();
            var id = "#weight" + num;
            $(id).val(val);
            cal_weight();
            $("#md_weight").modal('hide');
        }

        function cal_weight() {
            $("#sum_weight").val("");
            var sum_weight = Number(0);

            $(".item").each(function() {
                var id = $(this).attr("id");
                var weight = Number($("#" + id).val());
                if (weight == null || weight == "") {
                    weight = Number(0);
                }
                // console.log(weight);
                sum_weight = Number(sum_weight) + Number(weight);
            });
            currencyFormat(sum_weight);
        }

        function cal_num() {
            $("#sum_num").val("");
            var sum_num = Number(0);

            $(".itemnum").each(function() {
                var id = $(this).attr("id");
                var num = Number($("#" + id).val());
                if (num == null || num == "") {
                    num = Number(0);
                }
                sum_num = Number(sum_num) + Number(num);
            });
            var price = sum_num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
            $("#sum_num").val(price);
        }

        function make_number() {
            $('.numonly').on('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, ''); //<-- replace all other than given set of values
                var num1 = this.value.length;
                var num2 = (this.value.replace(/[^0-9]/g, '')).length;
                if ((num1 - num2) >= 2) {
                    this.value = $("#" + this.id).data("oldnum");
                } else if ((num1 - num2) == 0) {
                    this.value = Number(this.value);
                }
                $("#" + this.id).data("oldnum", $("#" + this.id).val());
                //console.log($("#"+this.id).data("weight"));
                //console.log(num1);
                //console.log(num2);
                cal_weight();
                cal_num();
            });
        }

        function currencyFormat(num) {
            var price = num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
            $("#sum_weight").val(price);
        }
        

        function add_item() {
            var DocNo = "<?php echo $DocNo ?>";
            var Userid = "<?php echo $Userid ?>";
            var Menu = '<?php echo $Menu; ?>';
            var arr_old_Qty = [];
            var arr_old_UnitCode = [];
            var arr_old_weight = [];
            $(".old").each(function() {
                var qtyid = $(this).attr("id")+'qty';
                var qty = $('#'+qtyid).val();
                if (Menu == 'clean') {
                    if (qty == null || qty == "") {
                        qty = 0;
                    }
                    // console.log(qty);
                    arr_old_Qty.push(qty);
                }else{
                    arr_old_Qty.push(0);
                }
                // console.log(qtyid);
                arr_old_UnitCode.push($(this).data("unit"));
                var val = $(this).val();
                var weight = 0;
                if (val != null && val != "") {
                    weight = val;
                }
                // console.log(weight);

                arr_old_weight.push(weight);
            });
            // console.log(arr_old_weight);

            var old_i = arr_old_items.join(',');
            var old_qty = arr_old_Qty.join(',');
            var old_unit = arr_old_UnitCode.join(',');
            var old_weight = arr_old_weight.join(',');

            var arr_new_Qty = [];
            var arr_new_UnitCode = [];
            var arr_new_weight = [];
            $(".new").each(function() {
                var qtyid = $(this).attr("id")+'qty';
                var qty = $('#'+qtyid).val();
                if (Menu == 'clean') {
                    if (qty == null || qty == "") {
                        qty = 0;
                    }
                    // console.log(qty);
                    arr_new_Qty.push(qty);
                }else{
                    arr_new_Qty.push(0);
                }
                arr_new_UnitCode.push($(this).data("unit"));
                var val = $(this).val();
                var weight = 0;
                if (val != null && val != "") {
                    weight = val;
                }
                arr_new_weight.push(weight);
            });

            var new_i = arr_new_items.join(',');
            var new_qty = arr_new_Qty.join(',');
            var new_unit = arr_new_UnitCode.join(',');
            var new_weight = arr_new_weight.join(',');

            var del_i = arr_del_items.join(',');

            var data = {
                'DocNo': DocNo,
                'refDocNo': '<?php echo $refDoc; ?>',
                'Userid': Userid,
                'old_i': old_i,
                'old_qty': old_qty,
                'old_unit': old_unit,
                'old_weight': old_weight,
                'new_i': new_i,
                'new_qty': new_qty,
                'new_unit': new_unit,
                'new_weight': new_weight,
                'del_i': del_i,
                'STATUS': 'add_item'
            };
            senddata(JSON.stringify(data));
        }

        function back() {
            var siteCode = '<?php echo $siteCode; ?>';
            var Menu = '<?php echo $Menu; ?>';
            if (Menu == 'dirty') {
                window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu;
            } else {
                window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu;
            }

        }

        function senddata(data) {
            var form_data = new FormData();
            form_data.append("DATA", data);
            var Menu = '<?php echo $Menu; ?>';
            if (Menu == 'dirty') {
                var URL = '../process/add_items_dirty.php';
            } else if (Menu == 'clean') {
                var URL = '../process/add_items_clean.php';
            }

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
                            $("#items").empty();
                            if (Menu == 'dirty') {
                                for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                    var num = Number(i) + 1;
                                    var id = "weight" + num;
                                    var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
                                    Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['ItemName'] + "</div></div><div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
                                    Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item old numonly' ";
                                    Str += "data-code='" + temp[i]['ItemCode'] + "' data-qty='" + temp[i]['Qty'] + "' data-unit='" + temp[i]['UnitCode'] + "' id='" + id + "' data-num='" + num + "' data-oldnum=" + temp[i]['Weight'] + " value='" + temp[i]['Weight'] + "' placeholder='0.0'>";
                                    Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'><button onclick='del_items(" + num + ")' class='btn btn-danger align-self-start mt-1 mr-1 px-2 py-0 rounded-circle'>x</button></div></div>";
                                    $("#items").append(Str);
                                    arr_old_items.push(temp[i]['ItemCode']);
                                            
                                    cal_weight();
                                    cal_num();
                                }
                            } else {
                                for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                    var num = Number(i) + 1;
                                    var id = "weight" + num;
                                    var idqty= id+"qty";
                                    var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'><div class='col'><div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
                                    Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['ItemName'] + "</div></div><div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><?php echo $array['numberSize'][$language]; ?>";
                                    Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 itemnum numonly'";
                                    Str += "id='" + idqty + "' value='" + temp[i]['Qty']+"' placeholder='0'>";
                                    Str += "<button onclick='del_items(" + num + ")' class='btn btn-danger align-self-start mt-1 mr-1 px-2 py-0 rounded-circle'>x</button></div></div>";      
                                    Str += "<div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'></div>";
                                    Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><?php echo $array['weight'][$language]; ?><input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item old numonly' ";
                                    Str += "data-code='" + temp[i]['ItemCode'] + "' data-qty='" + temp[i]['Qty'] + "' data-unit='" + temp[i]['UnitCode'] + "' id='" + id + "' data-num='" + num + "' data-oldnum=" + temp[i]['Weight'] + " value='" + temp[i]['Weight'] + "' placeholder='0.0'>";
                                    Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'></div></div></div>";
                                    $("#items").append(Str);
                                    //console.log(Str);
                                    arr_old_items.push(temp[i]['ItemCode']);
                                            
                                    cal_weight();
                                    cal_num();
                                }
                            }
                        } else if (temp["form"] == 'choose_items') {
                            var HptName = temp['HptName'];
                            var DepName = temp['DepName'];
                            //var cnt_items = $('#items').children().length; // นับลูกที่อยู่ข้างในไอดีนั้น
                            //var cnt_items = $("div[class*='old']").length; // นับคลาสทั้งหมดใน div ตามชื่อที่เลือกไว้
                            $("#choose_item").empty();
                            for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
                                var have_old = 0;
                                var have_new = 0;

                                if (arr_old_items.length > 0) {
                                    for (var ii = 0; ii < arr_old_items.length; ii++) {
                                        if (arr_old_items[ii] == temp[i]['ItemCode']) {
                                            have_old = 1;
                                        }
                                    }
                                }

                                if (arr_new_items.length > 0) {
                                    for (var iii = 0; iii < arr_new_items.length; iii++) {
                                        if (arr_new_items[iii] == temp[i]['ItemCode']) {
                                            have_new = 1;
                                        }
                                    }
                                }

                                if (have_old == 0 && have_new == 0) {
                                    var num = i + 1;
                                    var chk = "chk" + num;
                                    var Str = "<button onclick='chk_items(\"" + chk + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
                                    Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'><div>" + temp[i]['ItemName'] + "</div>";
                                    Str += "<input class='m-0 chk-item' type='checkbox' id='" + chk + "' data-name='" + temp[i]['ItemName'] + "' value='" + temp[i]['ItemCode'] + "'></div><hr class='m-0'><div class='col-12 text-truncate text-left'>" + HptName + " / " + DepName + "</div></button>";

                                    $("#choose_item").append(Str);
                                }
                            }
                        } else if (temp["form"] == 'add_item') {
                            swal({
                                title: '',
                                text: '<?php echo $genarray['savesuccess'][$language]; ?>',
                                type: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                showConfirmButton: false,
                                timer: 1200,
                                confirmButtonText: 'Error!!'
                            })
                            setTimeout('back()', 1500);
                            //load_items();
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
                        var message = "";
                        if (temp["form"] == 'choose_items') {
                            $("#choose_item").empty();
                        }
                        else if (temp["form"] == 'load_items') {
                            choose_items();
                            $("#md_item").modal('show');
                        }
                        else if (temp["form"] == 'add_item') {
                            alert("error ADD ITEM");
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
            <button data-toggle="modal" data-target="#exampleModal" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3 mb-5">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="text-center mb-3">
            <h4 class="text-truncate"><?php echo $genarray['docno'][$language]; ?></h4>
            <div id="DocNo" class="text-truncate"></div>
        </div>
        <div class="row justify-content-center px-3 mb-5">
            <div id="items" class="col-lg-9 col-md-10 col-sm-12 pb-3" style = "margin-bottom:100px;">
            
            </div>
        </div>
    </div>

    <div id="add_doc" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
        <div class="col-lg-9 col-md-10 col-sm-12">
            <?php
                if ($Menu == 'clean') {
                    echo '<div class="form-row my-2">
                            <div class="col-12 input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">'.$array['numberSum'][$language].'</span>
                            </div>
                            <input id="sum_num" type="text" class="form-control text-center bg-white" placeholder="0.0" disabled>
                            <div class="input-group-append">
                                <span class="input-group-text">'.$array['piece'][$language].'</span>
                            </div>

                        </div>
                    </div>';
                }
            ?>
            <div class="form-row my-2">
                <div class="col-12 input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><?php echo $array['weightSum'][$language]; ?></span>
                    </div>
                    <input id="sum_weight" type="text" class="form-control text-center bg-white" placeholder="0.0" disabled>
                    <div class="input-group-append">
                        <span class="input-group-text"><?php echo $array['KG'][$language]; ?></span>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <button onclick="choose_items()" class="btn btn-primary btn-block" type="button" data-toggle="modal" data-target="#md_item">
                        <i class="fas fa-plus mr-1"></i><?php echo $array['addList'][$language]; ?>
                    </button>
                </div>
                <div class="col-6">
                    <button onclick="add_item()" class="btn btn-success btn-block" type="button" data-toggle="modal" data-target="#">
                        <i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="md_item" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $array['addList'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <input onkeyup="choose_items()" id="search_items" class="form-control mb-3" type="text" placeholder="<?php echo $array['searchitem'][$language]; ?>">

                    <div id="choose_item">
                    </div>

                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-6 text-right">
                            <button id="btn_add_items" onclick="select_chk()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
                        </div>
                        <div class="col-6 text-left">
                            <button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="md_weight" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['Getweight'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
                    <input onkeydown="make_number()" id="val_weight" class="form-control mb-3 numonly" type="text">
                </div>

                <div class="modal-footer text-center">
                    <div class="w-100 d-flex justify-content-center m-0">
                        <!-- <div class="col-6 text-right"> -->
                        <button id="btn_add_items" onclick="change_weight()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
                        <!-- </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['confirm'][$language]; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <?php echo $genarray['YNwantToExit'][$language]; ?>
                </div>
                <div class="modal-footer text-center">
                    <div class="row w-100 d-flex align-items-center m-0">
                        <div class="col-6 text-right">
                            <button id="btn_add_dirty" onclick="back()" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
                        </div>
                        <div class="col-6 text-left">
                            <button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>