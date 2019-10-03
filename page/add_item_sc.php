<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$siteCode = $_GET['siteCode'];
$Menu = $_GET['Menu'];
$DocNo = $_GET['DocNo'];
$DepCode = $_GET['DepCode'];
// $Userid = $_GET['user'];
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
	<title>Login</title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var DocNo = "<?php echo $DocNo ?>";
		var DepCode = "<?php echo $DepCode ?>";
		var new_i_code = [];
		var new_i_name = [];
		var new_i_qty = [];
		var new_i_par = [];

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_items();

		});

		// function
		function load_items() {
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

		function choose_items() {
			var Search = $("#search_items").val();
			var data = {
				'Search': Search,
				'DepCode': DepCode,
				'STATUS': 'choose_items'
			};
			senddata(JSON.stringify(data));
		}

		function select_item(num) {
			var id = "#chchk" + num;
			if ($(id).is(':checked')) {
				$(id).prop("checked", false);
			} else {
				$(id).prop("checked", true);
			}
		}

		function select_chk() {
			new_i_code = [];
			new_i_name = [];
			new_i_qty = [];
			$(".chk-item").each(function() {
				var code, name, qty;
				if ($(this).is(':checked')) {
						console.log(1);
					var qty_id = "#chqty" + $(this).attr("data-i");
					code = $(this).attr("data-code");
					name = $(this).attr("data-name");
					qty = $(qty_id).val();
					new_i_code.push(code);
					new_i_name.push(name);
					new_i_qty.push(qty);
				} else {
					var Dcode = new_i_code.indexOf(code); // หา Index ของคำนั้น
					if (Dcode != -1) {
						new_i_code.splice(Dcode, 1); // ลบ Index ที่หาเจอ
					}

					var Dname = new_i_name.indexOf(name); // หา Index ของคำนั้น
					if (Dname != -1) {
						new_i_name.splice(Dname, 1); // ลบ Index ที่หาเจอ
					}

					var Dqty = new_i_qty.indexOf(qty); // หา Index ของคำนั้น
					if (Dqty != -1) {
						new_i_qty.splice(Dqty, 1); // ลบ Index ที่หาเจอ
					}
				}
			});
			console.log(new_i_code);
			console.log(new_i_name);
			console.log(new_i_qty);
			get_par();
		}

		function get_par() {
			var new_code = new_i_code.join(',');
			var data = {
				'DepCode': DepCode,
				'new_code': new_code,
				'STATUS': 'get_par'
			};
			senddata(JSON.stringify(data));
		}

		function ar_to_site() {
			// console.log(new_i_code);
			// console.log(new_i_name);
			// console.log(new_i_qty);
			$("#item").empty();
			new_i_code.forEach(function(val, i) {
				var order = Number(new_i_par[i]-new_i_qty[i]);
				var Str = "<tr onclick='view_item(\""+val+"\","+i+")' id='list"+i+"'>";
						Str +=  "<td>";
						Str +=  "	<div class='row'>";
						Str +=  "		<div class='col-3 d-flex align-items-center'>"+new_i_name[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+new_i_par[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+new_i_qty[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+order+"</div>";
						Str +=  "		</div>";
						Str +=  "</td>";
						Str += "</tr>";

				$("#item").append(Str);
			});
		}

		function view_item(code,i) {
			var index = new_i_code.indexOf(code);
			$("#newqty").val(new_i_qty[index]);
			$("#viewname").text(new_i_name[index]);
			$("#viewname").attr("data-code",code);
			$("#md_editqty").modal('show');
		}

		function edit_qty() {
			var index = new_i_code.indexOf($("#viewname").attr("data-code"));
			if (Number($("#newqty").val()) > Number(new_i_par[index])) {
				var Title = "จำนวนผิดพลาด";
				var Text = "จำนวนสูงสุดคือ "+new_i_par[index];
				var Type = "warning";
				AlertError(Title, Text, Type);
			} else {
				new_i_qty[index] = $("#newqty").val();
				ar_to_site();
				$("#md_editqty").modal('hide');
			}
			
		}

		function add_item() {
			var new_code = new_i_code.join(',');
			var new_qty = new_i_qty.join(',');
			var new_par = new_i_par.join(',');

			var data = {
				'DocNo': DocNo,
				'new_code': new_code,
				'new_qty': new_qty,
				'new_par': new_par,
				'STATUS': 'add_item'
			};
			senddata(JSON.stringify(data));
		}

		function show_quantity(ItemCode) {
			var data = {
				'DocNo': DocNo,
				'ItemCode': ItemCode,
				'STATUS': 'show_quantity'
			};
			senddata(JSON.stringify(data));
		}

		function save_checkpass() {
			var qty = $("#qc_qty").val();
			var lost = Number($("#qc_lost").val());
			var fail = Number($("#qc_fail").val());
			var sum = Number($("#qc_qty").val());
			var pass = Number(sum - fail - lost);

			var claim = Number($("#claim_qty").val());
			var rewash = Number($("#rewash_qty").val());

			if ($("#claim_qty").val() == "" || $("#claim_qty").val() == null) {
				claim = Number(0);
			}
			if ($("#rewash_qty").val() == "" || $("#rewash_qty").val() == null) {
				rewash = Number(0);
			}
			var sum_cr = Number(claim + rewash);
			var sum_lost_cr = Number(fail + lost);

			Title = "<?php echo $array['InvalidNum'][$language]; ?>";
			Type = "warning";

			if (pass < 0) {
				Text = "<?php echo $array['numData'][$language]; ?> " + sum + " <?php echo $array['numFromAll'][$language]; ?> " + qty + " !";
				AlertError(Title, Text, Type);
			} else if (sum_lost_cr > sum) {
				Text = "<?php echo $array['numRepair'][$language]; ?> " + fail + " <?php echo $array['numLost'][$language]; ?> " + lost + " <?php echo $array['numFromAll'][$language]; ?> " + sum + " !";
				AlertError(Title, Text, Type);
			} else if (sum_cr != fail) {
				Text = "<?php echo $array['numRepair'][$language]; ?> " + sum_cr + " <?php echo $array['numFromNP'][$language]; ?> " + fail + " !";
				AlertError(Title, Text, Type);
			} else if (lost > sum) {
				Text = "<?php echo $array['numLost'][$language]; ?> " + lost + " <?php echo $array['numFromAll'][$language]; ?> " + sum + " !";
				AlertError(Title, Text, Type);
			} else {
				var ItemCode = $("#qc_qty").attr("data-itemcode");
				var data = {
					'DocNo': DocNo,
					'ItemCode': ItemCode,
					'pass': pass,
					'fail': fail,
					'lost': lost,
					'claim': claim,
					'rewash': rewash,
					'STATUS': 'save_checkpass'
				};
				senddata(JSON.stringify(data));
			}
		}

		function show_question(ItemCode) {
			var data = {
				'DocNo': DocNo,
				'ItemCode': ItemCode,
				'STATUS': 'show_question'
			};
			senddata(JSON.stringify(data));
		}

		function close_question() {
			var ItemCode = $("#item_code").text();

			var data = {
				'DocNo': DocNo,
				'ItemCode': ItemCode,
				'STATUS': 'close_question'
			};
			senddata(JSON.stringify(data));
		}

		function save_checklist() {
			var max = Number($("#qc_fail").val());
			var over_max = 0;
			var sum_amount = 0;
			var ItemCode = $("#item_code").text();
			var sum = Number($("#save_checklist").attr("data-sumquestion"));
			var arr_question = [];
			var arr_amount = [];
			for (var i = 0; i < sum; i++) {
				var id = "#question" + i;
				var QuestID = $(id).attr("data-question");
				var Amount = Number($(id).val());
				sum_amount = Number(sum_amount) + Number(Amount);
				arr_question.push(QuestID);
				arr_amount.push(Amount);
				if (Amount > max) {
					over_max = 1;
				}
			}
			Title = "<?php echo $array['InvalidNum'][$language]; ?>";
			Type = "warning";
			if (over_max == 1) {
				arr_question = [];
				arr_amount = [];
				Text = "<?php echo $array['maxNumList'][$language]; ?> " + max + " !";
				AlertError(Title, Text, Type);
			} else if (sum_amount < max) {
				arr_question = [];
				arr_amount = [];
				Text = "<?php echo $array['numData'][$language]; ?> " + sum_amount + " <?php echo $array['numFromAll'][$language]; ?> " + max + " !";
				AlertError(Title, Text, Type);
			} else {
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

		function claim_detail(DocNo, ItemCode) {
			var data = {
				'DocNo': DocNo,
				'ItemCode': ItemCode,
				'STATUS': 'claim_detail'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			var siteCode = '<?php echo $siteCode; ?>';
			var Menu = '<?php echo $Menu; ?>';
			window.location.href = 'qc.php?siteCode=' + siteCode + '&Menu=' + Menu;
		}

		function AlertError(Title, Text, Type) {
			swal({
				title: Title,
				text: Text,
				type: Type,
				showConfirmButton: true,
				confirmButtonColor: '#3085d6',
				confirmButtonText: '<?php echo $genarray['yes2'][$language]; ?>'
			})
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);

			$.ajax({
				url: '../process/add_item_sc.php',
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
							if (temp['cnt'] == 0) {
								choose_items();
							} else {

							}
							var IsStatus = temp['IsStatus'];
							var op_claim = 0;
							for (var i = 0; i < temp['cnt']; i++) {
								var st_color = 0;
								var CheckList = Number(temp[i]['IsCheckList']);
								var Fail = Number(temp[i]['Fail']);
								var Claim = Number(temp[i]['Claim']);
								var Rewash = Number(temp[i]['Rewash']);
								var Lost = Number(temp[i]['Lost']);
								var img = "../img/Status_5.png";
								var detail = "<button onclick='event.cancelBubble=true;show_claim_detail(\"" + temp[i]['ItemCode'] + "\");' class='btn btn-info btn-block px-0' style='max-width:150px;'><?php echo $array['view'][$language]; ?></button>";
								var classItemQTY = "";

								if (Fail == 0 && Lost == 0) { // ผ่าน สีเขียว
									classItemQTY = "itemQTY";
									detail = "-";
								}
								var onclick = "";
								if (IsStatus < 3 && IsStatus > 0) {
									onclick = "onclick='show_quantity(\"" + temp[i]['ItemCode'] + "\")'";
								}
								var num = i + 1;
								var status = "multi_status" + num;
								var status_id = "#multi_status" + num;
								var Str = "<tr " + onclick + "><td><div class='row'>";
								Str += "<div scope='row' class='col-2 d-flex align-items-center justify-content-center'>" + num + "</div>";
								Str += "<div class='col-6 d-flex align-items-center'><div class='row'><div class='col-12 text-truncate font-weight-bold p-1'>" + temp[i]['ItemName'] + "</div>";
								Str += "<div class='col-12 text-black-50 p-1 " + classItemQTY + "' id = '" + temp[i]['ItemCode'] + "' data-qty = '" + temp[i]['Qty'] + "'><?php echo $array['numberSize'][$language]; ?> " + temp[i]['Qty'] + " / <?php echo $array['weight'][$language]; ?> " + temp[i]['Weight'] + " </div></div></div>";
								Str += "<div class='col-2 d-flex align-items-center justify-content-center py-0 px-1'>" + detail + "</div>";
								Str += "<div class='col-2 d-flex align-items-center justify-content-center'><div id='" + status + "' class='row pb-1 px-1'></div></div></td></tr>";

								$("#item").append(Str);

								if (Fail > 0) {
									op_claim++;
									if (Claim > 0) { // เคลม สีแดง
										st_color++;
										$(status_id).append("<div class='col-12 text-center p-0 d-flex justify-content-center font-weight-bold'><div class='st_color" + num + " my-bg-red mt-1 px-2'><?php echo $array['claim'][$language]; ?></div></div>");
									}
									if (Rewash > 0) { // ส่งซัก สีเหลือง
										st_color++;
										$(status_id).append("<div class='col-12 text-center p-0 d-flex justify-content-center font-weight-bold'><div class='st_color" + num + " my-bg-yellow mt-1 px-2'><?php echo $array['rewash'][$language]; ?></div></div>");
									}
								}

								if (Lost > 0) { // ผ้าค้างโรงซัก สีเทา
									op_claim++;
									st_color++;
									$(status_id).append("<div class='col-12 text-center p-0 d-flex justify-content-center font-weight-bold'><div class='st_color" + num + " my-bg-silver mt-1 px-2'><?php echo $array['remain'][$language]; ?></div></div>");
								}

								if (Fail == 0 && Lost == 0) { // ผ่าน สีเขียว
									$(status_id).append("<div class='col-12 text-center p-0 d-flex justify-content-center font-weight-bold'><div class='my-bg-green mt-1 px-2'><?php echo $array['pass'][$language]; ?></div></div>");
								}

								if (st_color >= 2) {
									$(".st_color" + num + "").removeClass("my-bg-red");
									$(".st_color" + num + "").removeClass("my-bg-yellow");
									$(".st_color" + num + "").removeClass("my-bg-silver");

									$(".st_color" + num + "").addClass("my-bg-blue");
								}
							}

						} else if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var chk_id = "chchk" + i;
								var qty_id = "chqty" + i;
								$Str = "<div onclick='select_item(" + i + ")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								$Str += "	<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>";
								$Str += "		<div class='mr-auto text-truncate'>" + temp[i]['ItemName'] + "</div>";
								$Str += "		<input onclick='event.cancelBubble=true;' onkeydown='make_number()' id='" + qty_id + "' value='1' type='text' class='form-control text-center numonly mx-2' style='max-width:100px;'>";
								$Str += "		<input class='m-0 chk-item' type='checkbox' id='" + chk_id + "' data-code='" + temp[i]['ItemCode'] + "' data-name='" + temp[i]['ItemName'] + "' data-i='" + i + "'>";
								$Str += "	</div>";
								$Str += "</div>";

								$("#choose_item").append($Str);
							}
							$("#md_chooseitem").modal('show');

						} else if (temp["form"] == 'get_par') {
							new_i_par = [];
							new_i_par = temp['ar_par'].split(',');
							ar_to_site();
							$("#md_chooseitem").modal('hide');

						} else if (temp["form"] == 'show_quantity') {
							$(".item_name").text(temp['ItemName']);
							$("#qc_qty").attr("data-itemcode", temp['ItemCode']);
							$("#qc_qty").val(temp['Qty']);
							var Pass = temp['Pass'];
							var Fail = temp['Fail'];
							var Lost = temp['Lost'];
							var Claim = temp['Claim'];
							var Rewash = temp['Rewash'];
							if (temp['Pass'] == 0) {
								Pass = "";
							}
							if (temp['Fail'] == 0) {
								Fail = "";
							}
							if (temp['Lost'] == 0) {
								Lost = "";
							}
							if (temp['Claim'] == 0) {
								Claim = "";
							}
							if (temp['Rewash'] == 0) {
								Rewash = "";
							}
							$("#qc_pass").val(Pass);
							$("#qc_fail").val(Fail);
							$("#qc_lost").val(Lost);
							$("#claim_qty").val(Claim);
							$("#rewash_qty").val(Rewash);

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
								var qty = temp[i]['Qty'];
								if (temp[i]['Qty'] == 0) {
									qty = "";
								}
								var Str = "<div class='my-btn btn-block alert alert-info py-1 px-3 mb-2'><div class='col-12 text-left font-weight-bold pr-0'>";
								Str += "<div>" + temp[i]['Question'] + "</div></div><div class='col-12 text-truncate p-0'><div class='form-check form-check-inline m-0'>";
								Str += "<?php echo $array['numNP'][$language]; ?><input onkeydown='make_number()' id='question" + i + "' class='form-control text-center m-2 numonly' type='text' ";
								Str += "data-itemcode='" + temp['ItemCode'] + "' data-question='" + temp[i]['QuestionId'] + "' value='" + qty + "' placeholder='0'><?php echo $array['numberSize'][$language]; ?></div></div></div>";
								$("#question").append(Str);
								sum_question++;
							}
							$("#save_checklist").attr('data-sumquestion', sum_question);
							$("#md_question").modal('show');

						} else if (temp["form"] == 'save_checkpass') {
							$("#md_checkpass").modal('hide');
							if (temp['unfail'] == 1 || temp['unfail'] == '1') {
								load_items();
							} else {
								show_question(temp['ItemCode']);
							}

						} else if (temp["form"] == 'close_question') {
							load_items();

						} else if (temp["form"] == 'show_claim_detail') {
							$("#detail").empty();
							if (temp['cntLost'] > 0) {
								var Str = "<div class='my-btn btn-block alert alert-secondary py-1 px-3 mb-2'>";
								Str += "<div class='col-12 text-truncate p-0'><div class='form-check form-check-inline font-weight-bold m-0'>";
								Str += "<?php echo $array['remain'][$language]; ?><input onkeydown='make_number()' class='form-control text-center m-2 numonly' type='text' ";
								Str += "value='" + temp['Lost'] + "' disabled><?php echo $array['numberSize'][$language]; ?></div></div></div>";

								$("#detail").append(Str);
							}
							if (temp['cnt'] > 0) {
								for (var i = 0; i < temp['cnt']; i++) {
									if (temp[i]['Qty'] != 0) {
										var Str = "<div class='my-btn btn-block alert alert-info py-1 px-3 mb-2'><div class='col-12 text-left font-weight-bold pr-0'>";
										Str += "<div>" + temp[i]['Question'] + "</div></div><div class='col-12 text-truncate p-0'><div class='form-check form-check-inline m-0'>";
										Str += "<?php echo $array['numNP'][$language]; ?><input onkeydown='make_number()' id='question" + i + "' class='form-control text-center m-2 numonly' type='text' ";
										Str += "value='" + temp[i]['Qty'] + "' disabled><?php echo $array['numberSize'][$language]; ?></div></div></div>";

										$("#detail").append(Str);
									}
								}
							}
							$("#md_detail").modal('show');

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
							save_item_stock();

						} else if (temp["form"] == 'save_item_stock') {
							var Menu = "<?php echo $Menu ?>";
							var siteCode = "<?php echo $siteCode ?>";

							window.location.href = 'signature.php?Menu=' + Menu + '&DocNo=' + DocNo + '&siteCode=' + siteCode;

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}

					} else if (temp['status'] == "failed") {
						var message = "";
						if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
						} else if (temp["form"] == 'save_checklist') {

						}
					}
				}
			});
		}
		// end display
	</script>
</head>

<body>
	<header data-role="header">
		<div class="head-bar d-flex justify-content-between">
			<div style="width:139.14px;">
				<button onclick="back()" class="head-btn btn-primary"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-primary" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3 mb-5">
		<div align="center" style="margin:1rem 0;">
			<div class="mb-3">
				<img src="../img/logo.png" width="156" height="40" />
			</div>
			<div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div>
		</div>
		<div class="text-center mb-3">
			<div class="text-truncate font-weight-bold" style="font-size:25px;"><?php echo $genarray['Document'][$language]; ?></div>
			<div id="DocNo" class="text-truncate font-weight-bold" style="font-size:25px;"></div>
		</div>
		<div class="row justify-content-center px-3">
			<table class="table table-hover col-lg-9 col-md-10 col-sm-12">
				<thead>
					<tr class="bg-primary text-white">
						<th scope="col">
							<div class="row">
								<div class="col-3 text-center p-0">รายการ</div>
								<div class="col-3 text-center p-0">Par</div>
								<div class="col-3 text-center p-0">Left</div>
								<div class="col-3 text-center p-0">Order</div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="item">

					<!-- <tr>
						<td>
							<div class='row'>
								<div class='col-3 d-flex align-items-center'>list</div>
								<div class='col-3 d-flex align-items-center justify-content-center'> par </div>
								<div class='col-3 d-flex align-items-center justify-content-center'> left </div>
								<div class='col-3 d-flex align-items-center justify-content-center'> order </div>
							</div>
						</td>
					</tr> -->

				</tbody>
			</table>
		</div>
	</div>

	<div id="add_doc" class="fixed-bottom d-flex justify-content-center py-2 bg-white">
		<div class="col-lg-9 col-md-10 col-sm-12">
			<div class="row d-flex justify-content-center">
				<button onclick="choose_items()" class="btn btn-primary btn-block mr-3" style="max-width:250px;" type="button">
					<i class="fas fa-plus mr-1"></i>เพิ่มรายการ
				</button>
				<button onclick="add_item()" class="btn btn-success btn-block m-0 ml-3" style="max-width:250px;" type="button">
					<i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="md_chooseitem" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

						<!-- <div onclick='select_item()' class='btn btn-block alert alert-info py-1 px-3 mb-2'>
							<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>
								<div class='mr-auto text-truncate'>ItemName</div>
								<input onkeydown='make_number()' type='text' class='form-control text-center numonly mx-2' style='max-width:100px;'>
								<input class='m-0 chk-item' type='checkbox' id='' data-name='' value=''>
							</div>
						</div> -->

					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="select_chk()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="modal fade" id="md_editqty" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $array['addList'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">

					<div id="editqty">

						<div class='btn btn-block alert alert-info py-1 px-3 mb-2'>
							<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>
								<div id='viewname' class='mr-auto text-truncate'></div>
								<input onkeydown='make_number()' id='newqty' type='text' class='form-control text-center numonly mx-2' style='max-width:100px;'>
								<input class='m-0' type='checkbox' checked disabled>
							</div>
						</div>

					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="edit_qty()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
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
							<button type="button" class="btn btn-block btn-secondary" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
						</div>
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
					<?php echo $array['allPass'][$language]; ?>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="save_qc()" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
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