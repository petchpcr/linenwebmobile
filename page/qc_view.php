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
$From = $_GET['from'];
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

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		$(document).ready(function(e) {
			var DocNo = "<?php echo $DocNo ?>";
			$("#DocNo").text(DocNo);
			load_items();
		});

		// function
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

			//var pass = Number($("#qc_pass").val());
			// var sum = Number(pass + fail);
			
			var fail = Number($("#qc_fail").val());
			var sum = Number($("#qc_qty").val());
			var pass = Number(sum-fail);

			var claim = Number($("#claim_qty").val());
			var rewash = Number($("#rewash_qty").val());

			if ($("#claim_qty").val() == "" || $("#claim_qty").val() == null) {
				claim = Number(0);
			}
			if ($("#rewash_qty").val() == "" || $("#rewash_qty").val() == null) {
				rewash = Number(0);
			}
			var sum_cr = Number(claim + rewash);

			Title = "<?php echo $array['InvalidNum'][$language]; ?>";
			Type = "warning";

			if (pass<0) {
				Text = "<?php echo $array['numData'][$language]; ?> " + sum + " <?php echo $array['numFromAll'][$language]; ?> " + qty + " !";
				AlertError(Title, Text, Type);
			} else if (sum_cr != fail) {
				Text = "<?php echo $array['numRepair'][$language]; ?> " + sum_cr + " <?php echo $array['numFromNP'][$language]; ?> " + fail + " !";
				AlertError(Title, Text, Type);
			} else {
				var DocNo = '<?php echo $DocNo ?>';
				var ItemCode = $("#qc_qty").attr("data-itemcode");
				var data = {
					'DocNo': DocNo,
					'ItemCode': ItemCode,
					'pass': pass,
					'fail': fail,
					'claim': claim,
					'rewash': rewash,
					'STATUS': 'save_checkpass'
				};
				senddata(JSON.stringify(data));
			}
		}
		function save_Allpass() {
				var DocNo = '<?php echo $DocNo ?>';
				$('.itemQTY').each(function( index ) {
					var ItemCode = this.id;
					var pass = $("#"+ItemCode).data('qty');
					console.log($("#"+ItemCode).data('qty'));
						var data = {
						'DocNo': DocNo,
						'ItemCode': ItemCode,
						'pass': pass,
						'fail': 0,
						'claim': 0,
						'rewash': 0,
						'STATUS': 'save_checkpass'
					};
					senddata(JSON.stringify(data));
				});
				save_qc();
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

		function save_checklist() {
			var max = Number($("#qc_fail").val());
			var over_max = 0;
			var sum_amount = 0;
			var DocNo = '<?php echo $DocNo ?>';
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

		function show_claim_detail(ItemCode) {
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
			var From = '<?php echo $From; ?>';
			if (From == 2) {
				var URL = '../process/qc_view_repair.php';
			} else {
				var URL = '../process/qc_view_clean.php';
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
							$("#item").empty();
							var op_claim = 0;
							var test = temp['cnt'];
							if (temp['cnt'] > 0) {
								for (var i = 0; i < temp['cnt']; i++) {
									var CheckList = Number(temp[i]['IsCheckList']);
									var img = "";
									var detail = "<button onclick='event.cancelBubble=true;show_claim_detail(\"" + temp[i]['ItemCode'] + "\");' class='btn btn-info'>เรียกดู</button>";
									var classItemQTY = "itemQTY";
									switch (CheckList) {
										case 0:
											img = "../img/Status_3.png"; // เขียว
											detail = "-";
											break;
										case 1:
											img = "../img/Status_1.png"; // น้ำเงิน
											op_claim++;
											classItemQTY = "";
											break;
										case 2:
											img = "../img/Status_1.png";
											op_claim++;
											classItemQTY = "";
											break;
										case 3:
											img = "../img/Status_2.png"; // เหลือง
											op_claim++;
											classItemQTY = "";
											break;
										default:
											img = "../img/Status_4.png"; // เทา
											detail = "-";
									}

									var num = i + 1;
									var Str = "<tr onclick='show_quantity(\"" + temp[i]['ItemCode'] + "\")'><td><div class='row'>";
									Str += "<div scope='row' class='col-2 d-flex align-items-center justify-content-center'>" + num + "</div>";
									Str += "<div class='col-6'><div class='row'><div class='col-12 text-truncate font-weight-bold p-1'>" + temp[i]['ItemName'] + "</div>";
									Str += "<div class='col-12 text-black-50 p-1 "+classItemQTY+"' id = '" + temp[i]['ItemCode'] + "' data-qty = '" + temp[i]['Qty'] + "'><?php echo $array['numberSize'][$language]; ?> " + temp[i]['Qty'] + " / <?php echo $array['weight'][$language]; ?> " + temp[i]['Weight'] + " </div></div></div>";
									Str += "<div class='col-2 d-flex align-items-center justify-content-center p-0'>" + detail + "</div>";
									Str += "<div class='col-2 d-flex align-items-center justify-content-center'><img src='" + img + "' height='40px'></div></div></td></tr>";

									$("#item").append(Str);
								}

								if (op_claim > 0) {
									$("#claim-btn").show();
									$("#save-btn").hide();
								} else {
									$("#claim-btn").hide();
									$("#save-btn").show();
								}
							} else {
								$("#claim-btn").hide();
								$("#save-btn").hide();

								Title = "<?php echo $array['Empty'][$language]; ?>";
								Text = "<?php echo $array['NoiteminDoc'][$language]; ?> !";
								Type = "info";
								AlertError(Title, Text, Type);
							}

						} else if (temp["form"] == 'show_quantity') {
							$(".item_name").text(temp['ItemName']);
							$("#qc_qty").attr("data-itemcode", temp['ItemCode']);
							$("#qc_qty").val(temp['Qty']);
							var Pass = temp['Pass'];
							var Fail = temp['Fail'];
							var Claim = temp['Claim'];
							var Rewash = temp['Rewash'];
							if (temp['Pass'] == 0) {
								Pass = "";
							}
							if (temp['Fail'] == 0) {
								Fail = "";
							}
							if (temp['Claim'] == 0) {
								Claim = "";
							}
							if (temp['Rewash'] == 0) {
								Rewash = "";
							}
							$("#qc_pass").val(Pass);
							$("#qc_fail").val(Fail);
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
								Str += "ไม่ผ่าน<input onkeydown='make_number()' id='question" + i + "' class='form-control text-center m-2 numonly' type='text' ";
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
							if (temp['cnt'] > 0) {
								$("#detail").empty();
								for (var i = 0; i < temp['cnt']; i++) {
									if (temp[i]['Qty'] != 0) {
										var Str = "<div class='my-btn btn-block alert alert-info py-1 px-3 mb-2'><div class='col-12 text-left font-weight-bold pr-0'>";
										Str += "<div>" + temp[i]['Question'] + "</div></div><div class='col-12 text-truncate p-0'><div class='form-check form-check-inline m-0'>";
										Str += "ไม่ผ่าน<input onkeydown='make_number()' id='question" + i + "' class='form-control text-center m-2 numonly' type='text' ";
										Str += "value='" + temp[i]['Qty'] + "' disabled><?php echo $array['numberSize'][$language]; ?></div></div></div>";

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
							save_Allpass();
							//save_qc();

						} else if (temp["form"] == 'create_rewash') {
							var NewDocNo = temp['NewDocNo'];
							send_rewash(NewDocNo);

						} else if (temp["form"] == 'save_qc') {
							var Menu = "<?php echo $Menu ?>";
							var DocNo = "<?php echo $DocNo ?>";
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
			<button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
			<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
		</div>
	</header>
	<div class="px-3 mb-5">
		<div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
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
								<div class="col-2 text-center p-0"><?php echo $array['no'][$language]; ?></div>
								<div class="col-6 text-left p-0"><?php echo $array['List'][$language]; ?></div>
								<div class="col-2 text-center p-0"><?php echo $array['Cause'][$language]; ?></div>
								<div class="col-2 text-center p-0"><?php echo $array['Status'][$language]; ?></div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="item"></tbody>
			</table>
		</div>
	</div>

	<div id="add_doc" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
		<div class="col-lg-9 col-md-10 col-sm-12">
			<div class="row py-1 px-3">
				<button onclick="create_claim()" id="claim-btn" class="btn btn-danger btn-block" type="button">
					<i class="fas fa-times mr-1"></i><?php echo $array['sendClaim'][$language]; ?>
				</button>
				<button data-toggle="modal" data-target="#exampleModal" id="save-btn" class="btn btn-success btn-block" type="button">
				<!-- <button onclick="save_qc()" id="save-btn" class="btn btn-success btn-block" type="button"> -->
					<i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
				</button>
			</div>
		</div>
	</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="md_checkpass" tabindex="-1" role="dialog" aria-hidden="true">
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
							<label><?php echo $array['numFAll'][$language]; ?></label>
							<input type="text" class="form-control" id="qc_qty" disabled>
						</div>

						<div class="form-group text-left" hidden>
							<label><?php echo $array['numP'][$language]; ?></label>
							<input onkeydown='make_number()' type="text" class="form-control numonly" id="qc_pass" placeholder="0">
						</div>

						<div class="form-group text-left">
							<label><?php echo $array['numNP'][$language]; ?></label>
							<input onkeydown='make_number()' type="text" class="form-control numonly" id="qc_fail" placeholder="0">
						</div>
						<hr>
						<div id="claim_rewash" class="alert alert-secondary m-0">
							<div class="form-row mb-2">
								<div class="col-md-4 col-3 text-right font-weight-bold d-flex align-items-center justify-content-end"><?php echo $array['sendRewash'][$language]; ?></div>
								<div class="col-md-4 col-6">
									<input onkeydown='make_number()' id="rewash_qty" class='form-control text-center numonly' type='text' placeholder='0'>
								</div>
								<div class="col-md-4 col-3 text-left d-flex align-items-center justify-content-start"><?php echo $array['numberSize'][$language]; ?></div>
							</div>
							<div class="form-row">
								<div class="col-md-4 col-3 text-right font-weight-bold d-flex align-items-center justify-content-end"><?php echo $array['sendClaim'][$language]; ?></div>
								<div class="col-md-4 col-6">
									<input onkeydown='make_number()' id="claim_qty" class='form-control text-center numonly' type='text' placeholder='0'>
								</div>
								<div class="col-md-4 col-3 text-left d-flex align-items-center justify-content-start"><?php echo $array['numberSize'][$language]; ?></div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button onclick="save_checkpass()" type="button" class="btn btn-primary mx-3"><?php echo $genarray['yes'][$language]; ?></button>
							<button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_question" tabindex="-1" role="dialog" aria-hidden="true">
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

					<div id="question"></div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button onclick="save_checklist()" type="button" id="save_checklist" class="btn btn-primary mx-3"><?php echo $genarray['yes'][$language]; ?></button>
							<button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
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
							<button onclick="save_Allpass()" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- <div class="modal fade" id="md_claim_rewash" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
								<div class=""><?php echo $array['numberSize'][$language]; ?></div>
							</div>
						</div>

						<div class="row w-100 mx-2">
							<div class="d-flex align-items-center col-md-8 col-7">
								<div class="text-truncate">Damage</div>
							</div>
							<div class="d-flex align-items-center col-md-4 col-5 pl-0">
								<input type="text" class="form-control rounded text-center bg-white my-2 mr-1 numonly">
								<div class=""><?php echo $array['numberSize'][$language]; ?></div>
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
								<div class=""><?php echo $array['numberSize'][$language]; ?></div>
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
		</div> -->
	</div>
</body>

</html>