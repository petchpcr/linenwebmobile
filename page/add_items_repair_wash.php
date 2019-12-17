<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
	header("location:../index.html");
}
$Delback = 0;
$Unweight = 0;
$Ref = 0;
$NotDelDetail = 0;

if (isset($_GET['Delback'])) {
	$Delback = $_GET['Delback'];
}
if (isset($_GET['Unweight'])) {
	$Unweight = $_GET['Unweight'];
}
if (isset($_GET['Ref'])) {
	$Ref = $_GET['Ref'];
}
if (isset($_GET['NotDelDetail'])) {
	$NotDelDetail = $_GET['NotDelDetail'];
}

$form_out = $_GET['form_out'];
$siteCode = $_GET['siteCode'];
$Menu = $_GET['Menu'];
$DocNo = $_GET['DocNo'];
$refDoc = $_GET['RefDocNo'];
$DepCode = $_GET['DepCode'];
$Userid = $_GET['user'];
$Per = $_SESSION['Permission'];
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
	<link rel="stylesheet" href="../css/signature-pad2.css">

	<script>
		var siteCode = '<?php echo $siteCode; ?>';
		var DepCode = "<?php echo $DepCode ?>";
		var DocNo = "<?php echo $DocNo ?>";
		var refDoc = "<?php echo $refDoc ?>";
		var Ref = "<?php echo $Ref ?>";
		var Menu = '<?php echo $Menu; ?>';
		var Userid = "<?php echo $Userid ?>";
		var Delback = "<?php echo $Delback ?>";
		var Unweight = "<?php echo $Unweight ?>";
		var NotDelDetail = "<?php echo $NotDelDetail ?>";

		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_items();
			check_signature();

			$('#ModalSign').on('shown.bs.modal', function() {
				resizeCanvas();
			})

			$('#ModalSign').on('hidden.bs.modal', function() {
				signaturePad.clear();
			})
		});

		// function
		function load_items() {
			var data = {
				'DocNo': DocNo,
				'Unweight': Unweight,
				'STATUS': 'load_items'
			};
			senddata(JSON.stringify(data));
		}

		function check_signature() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'check_signature'
			};
			senddata(JSON.stringify(data));
		}

		function choose_items() {
			var Search = $("#search_items").val();
			var data = {
				'siteCode': siteCode,
				'Search': Search,
				'DocNo': DocNo,
				'STATUS': 'choose_items'
			};
			senddata(JSON.stringify(data));
		}

		function new_value(item, name) {
			$("#new_qty").val(1);
			$("#new_weight").val("");
			$("#btn_edit").attr("onclick", "change_value(\"" + item + "\",\"" + name + "\")");

			$("#btn_edit").prop("disabled", false);
			$("#new_qty").prop("disabled", false);
			$("#new_weight").prop("disabled", false);

			$("#md_edit").modal('show');
		}

		function change_value(item, name) {
			var qty = $("#new_qty").val();
			var weight = $("#new_weight").val();

			if (qty > 0) {
				$("#btn_edit").prop("disabled", true);
				$("#new_qty").prop("disabled", true);
				$("#new_weight").prop("disabled", true);

				var data = {
					'DocNo': DocNo,
					'item': item,
					'qty': qty,
					'weight': weight,
					'STATUS': 'change_value'
				};
				senddata(JSON.stringify(data));
			}
			$("#md_edit").modal('hide');

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

		function del_items(item) {
			var data = {
				'DocNo': DocNo,
				'item': item,
				'STATUS': 'del_items'
			};
			senddata(JSON.stringify(data));
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
				cal_weight();
				cal_num();
			});
		}

		function currencyFormat(num) {
			var price = num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
			$("#sum_weight").val(price);
		}

		function add_item() {
			$("#btn_save").prop("disabled", true);
			var ar_item = [];
			var ar_weight = [];
			var ar_qty = [];
			$(".item ").each(function() {
				var num = $(this).attr("data-num");
				var weight = $(this).val();
				var qty_id = "#weight" + num + "qty";
				var qty = $(qty_id).val();
				var item = $(this).attr("data-code");

				ar_item.push(item);
				ar_weight.push(weight);
				ar_qty.push(qty);
			});

			var data = {
				'DocNo': DocNo,
				'Userid': Userid,
				'ar_item': ar_item,
				'ar_weight': ar_weight,
				'ar_qty': ar_qty,
				'STATUS': 'add_item'
			};
			senddata(JSON.stringify(data));
		}

		function sign_fac() {
			$("#ModalSign").modal('show');
			sign_funciton = "sign_fac";
		}

		function sign_nh() {
			$("#ModalSign").modal('show');
			sign_funciton = "sign_nh";
		}

		function save_sign(dataURL) {
			$("#ModalSign").modal('hide');
			swal({
				title: 'Please wait...',
				text: 'Processing',
				allowOutsideClick: false
			})
			swal.showLoading();
			$.ajax({
				url: "../process/signature_repair_wash.php",
				method: "POST",
				data: {
					DocNo: DocNo,
					SignCode: dataURL,
					sign_funciton: sign_funciton
				},
				success: function(data) {
					swal.hideLoading();
					swal({
						title: '',
						text: 'success',
						type: 'success',
						showCancelButton: false,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						timer: 1000,
						confirmButtonText: 'Ok',
						showConfirmButton: false
					}).then(function() {

						},
						function(dismiss) {
							swal.close();
							check_signature();
						})
				}
			});
		}

		function back() {
			// if (Delback == 1) {
			if (false) {
				var data = {
					'DocNo': DocNo,
					'refDoc': refDoc,
					'Menu': Menu,
					'STATUS': 'del_back'
				};
				senddata(JSON.stringify(data));
			} else {
				window.location.href = 'repair_wash.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
			}
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/add_items_repair_wash.php';

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

							for (var i = 0; i < temp['count']; i++) {
								var num = Number(i) + 1;
								var id = "weight" + num;
								var idqty = id + "qty";
								var weight = temp[i]['Weight'];
								if (temp[i]['Weight'] == 0.00) {
									weight = "";
								}
								var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'>";
								Str += "		<div class='col'>";
								Str += "			<div class='row'>";

								Str += "				<div class='d-flex align-items-center col-lg-8 col-md-7 col-sm-6 col-6'>";
								Str += "					<div class='text-truncate font-weight-bold'>" + temp[i]['ItemName'] + "</div>";
								Str += "				</div>";

								Str += "				<div class='d-flex align-items-center col-lg-4 col-md-5 col-sm-6 col-6 input-group p-0'>";
								Str += "					<div class='pr-1'><?php echo $array['numberSize'][$language]; ?></div>";
								Str += "					<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white ";
								Str += "					my-2 mr-1 itemnum numonly' id='" + idqty + "' value='" + temp[i]['Qty'] + "' placeholder='0'>";

								Str += "					<div class='pr-1'><?php echo $array['weight'][$language]; ?></div>";
								Str += "					<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item old numonly' ";
								Str += "					data-code='" + temp[i]['ItemCode'] + "' data-qty='" + temp[i]['Qty'] + "' data-unit='" + temp[i]['UnitCode'] + "' id='" + id + "'";
								Str += "					data-num='" + num + "' data-oldnum=" + temp[i]['Weight'] + " value='" + weight + "' placeholder='0.0'>";

								Str += "					<img onclick='del_items(\"" + temp[i]['ItemCode'] + "\")' src='../img/close.png' style='height:25px;margin-right:5px;margin-bottom:20px;'>";
								Str += "				</div>";

								Str += "			</div>";
								Str += "		</div>";
								Str += "	</div>";
								$("#items").append(Str);

								cal_weight();
								cal_num();
							}
						} else if (temp["form"] == 'check_signature') {
							if (temp['SignFac'] == null && temp['SignNH'] == null) {
								$("#btn_sign_fac").removeAttr('hidden');
								$("#btn_sign_nh").prop('hidden', true);
							} else if (temp['SignFac'] != null && temp['SignNH'] == null) {
								$("#btn_sign_nh").removeAttr('hidden');
								$("#btn_sign_fac").prop('hidden', true);
							} else if (temp['SignFac'] != null && temp['SignNH'] != null) {
								$("#btn_save").removeAttr('hidden');
								$("#btn_sign_fac").prop('hidden', true);
								$("#btn_sign_nh").prop('hidden', true);
							}
						} else if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var num = i + 1;
								var chk = "chk" + num;
								var Str = "<button onclick='new_value(\"" + temp[i]['ItemCode'] + "\",\"" + temp[i]['ItemName'] + "\")' id='chs" + num + "' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'><div>" + temp[i]['ItemName'] + "</div>";
								Str += "</div></button>";

								$("#choose_item").append(Str);
							}
						} else if (temp["form"] == 'change_value') {
							load_items();
							choose_items();
							$("#btn_edit").prop("disabled", false);
							$("#new_qty").prop("disabled", false);
							$("#new_weight").prop("disabled", false);
							$("#md_edit").modal('hide');

						} else if (temp["form"] == 'del_items') {
							load_items();

						} else if (temp["form"] == 'add_item') {
							Delback = 0;
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
						} else if (temp["form"] == 'del_back') {
							Delback = 0;
							back();
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						var message = "";
						if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
						} else if (temp["form"] == 'load_items') {
							$("#items").empty();
							choose_items();
							check_signature();
							$("#md_item").modal('show');

						} else if (temp["form"] == 'add_item') {
							alert("error ADD ITEM");
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
				<img src="../img/logo.png" width="156" height="60" />
			</div>
			<!-- <div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div> -->
		</div>
		<div class="text-center mb-3">
			<div class="text-truncate font-weight-bold" style="font-size:25px;"><?php echo $genarray['docno'][$language]; ?></div>
			<div id="DocNo" class="text-truncate font-weight-bold" style="font-size:25px;"></div>
		</div>
		<div class="row justify-content-center px-3 mb-5">
			<div id="items" class="col-lg-9 col-md-10 col-sm-12 pb-3" style="margin-bottom:100px;">

			</div>
		</div>
	</div>

	<div id="add_doc" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
		<div class="col-lg-9 col-md-10 col-sm-12">
			<?php
				if (true) {
					echo '<div class="form-row my-2">
								<div class="col-12 input-group">
									<div class="input-group-prepend">
											<span class="input-group-text" style="width:100px;">' . $array['numberSum'][$language] . '</span>
									</div>
									<input id="sum_num" type="text" class="form-control text-center bg-white" placeholder="0.0" disabled>
									<div class="input-group-append">
											<span class="input-group-text" style="width:70px;">' . $array['piece'][$language] . '</span>
									</div>
								</div>
							</div>';
				}
			?>
			<div class="form-row my-2">
				<div class="col-12 input-group">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;"><?php echo $array['weightSum'][$language]; ?></span>
					</div>
					<input id="sum_weight" type="text" class="form-control text-center bg-white" placeholder="0.0" disabled>
					<div class="input-group-append">
						<span class="input-group-text" style="width:70px;"><?php echo $array['KG'][$language]; ?></span>
					</div>

				</div>
			</div>
			<div class="row">
				<div class="col-6">
					<button onclick="choose_items()" class="btn btn-create btn-block" type="button" data-toggle="modal" data-target="#md_item">
						<i class="fas fa-plus mr-1"></i><?php echo $array['addList'][$language]; ?>
					</button>
				</div>
				<div class="col-6">
					<button id="btn_save" onclick="add_item()" class="btn btn-success btn-block" type="button" data-toggle="modal" data-target="#" hidden>
						<i class="fas fa-save mr-1"></i><?php echo $genarray['save'][$language]; ?>
					</button>
					<button id="btn_sign_fac" onclick="sign_fac()" class="btn btn-success btn-block mt-0" type="button" hidden>
						<i class="fas fa-signature mr-1"></i><?php echo $array['signfac'][$language]; ?>
					</button>
					<button id="btn_sign_nh" onclick="sign_nh()" class="btn btn-success btn-block mt-0" type="button" hidden>
						<i class="fas fa-signature mr-1"></i><?php echo $array['signnh'][$language]; ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="ModalSign" tabindex="-1" role="dialog" aria-hidden='false'>
		<div class="modal-dialog" role="document">
			<div class="modal-content" style="background-color:#fff;">
				<div class="modal-body p-0">

					<div id="maxxx" onselectstart="return false">
						<div id="signature-pad" class="signature-pad">
							<div class="signature-pad--body">
								<canvas></canvas>
							</div>
							<div class="signature-pad--footer">
								<div class="signature-pad--actions">
									<div>
										<button type="button" class="button clear btn btn-secondary mr-2" data-action="clear"><?php echo $genarray['clear'][$language]; ?></button>
										<button type="button" class="button" data-action="change-color" hidden>Change color</button>
										<button type="button" class="button btn btn-warning" data-action="undo" hidden>ย้อนกลับ</button>

									</div>
									<div>
										<button type="button" class="button save" data-action="save-png" hidden>Save as PNG</button>
										<button type="button" class="button save" data-action="save-jpg" hidden>Save as JPG</button>

										<button type="button" class="button save btn btn-primary" data-action="save-svg"><?php echo $genarray['save'][$language]; ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

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
						<div class="col-12 text-center">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['Getweight'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">

					<div class="row">
						<div class="col-6">
							<div class="bg-primary text-center text-white mb-2 p-0" style="border-radius:0.25rem;"><?php echo $genarray['qty'][$language]; ?></div>
						</div>
						<div class="col-6">
							<div class="bg-primary text-center text-white mb-2 p-0" style="border-radius:0.25rem;"><?php echo $genarray['weight'][$language]; ?></div>
						</div>
					</div>

					<div class="row">
						<div class="col-6">
							<input onkeydown="make_number()" id="new_qty" class="form-control text-center mb-3 numonly" type="text" placeholder="0">
						</div>
						<div class="col-6">
							<input onkeydown="make_number()" id="new_weight" class="form-control text-center mb-3 numonly" type="text" placeholder="0.00">
						</div>
					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="w-100 d-flex justify-content-center m-0">
						<button id="btn_edit" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
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
						<button id="btn_add_items" onclick="change_weight()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
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

	<script>
		/*!
		 * Signature Pad v3.0.0-beta.3 | https://github.com/szimek/signature_pad
		 * (c) 2018 Szymon Nowak | Released under the MIT license
		 */

		(function(global, factory) {
			typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
				typeof define === 'function' && define.amd ? define(factory) :
				(global.SignaturePad = factory());
		}(this, (function() {
			'use strict';

			var Point = (function() {
				function Point(x, y, time) {
					this.x = x;
					this.y = y;
					this.time = time || Date.now();
				}
				Point.prototype.distanceTo = function(start) {
					return Math.sqrt(Math.pow(this.x - start.x, 2) + Math.pow(this.y - start.y, 2));
				};
				Point.prototype.equals = function(other) {
					return this.x === other.x && this.y === other.y && this.time === other.time;
				};
				Point.prototype.velocityFrom = function(start) {
					return this.time !== start.time ?
						this.distanceTo(start) / (this.time - start.time) :
						0;
				};
				return Point;
			}());

			var Bezier = (function() {
				function Bezier(startPoint, control2, control1, endPoint, startWidth, endWidth) {
					this.startPoint = startPoint;
					this.control2 = control2;
					this.control1 = control1;
					this.endPoint = endPoint;
					this.startWidth = startWidth;
					this.endWidth = endWidth;
				}
				Bezier.fromPoints = function(points, widths) {
					var c2 = this.calculateControlPoints(points[0], points[1], points[2]).c2;
					var c3 = this.calculateControlPoints(points[1], points[2], points[3]).c1;
					return new Bezier(points[1], c2, c3, points[2], widths.start, widths.end);
				};
				Bezier.calculateControlPoints = function(s1, s2, s3) {
					var dx1 = s1.x - s2.x;
					var dy1 = s1.y - s2.y;
					var dx2 = s2.x - s3.x;
					var dy2 = s2.y - s3.y;
					var m1 = {
						x: (s1.x + s2.x) / 2.0,
						y: (s1.y + s2.y) / 2.0
					};
					var m2 = {
						x: (s2.x + s3.x) / 2.0,
						y: (s2.y + s3.y) / 2.0
					};
					var l1 = Math.sqrt(dx1 * dx1 + dy1 * dy1);
					var l2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
					var dxm = m1.x - m2.x;
					var dym = m1.y - m2.y;
					var k = l2 / (l1 + l2);
					var cm = {
						x: m2.x + dxm * k,
						y: m2.y + dym * k
					};
					var tx = s2.x - cm.x;
					var ty = s2.y - cm.y;
					return {
						c1: new Point(m1.x + tx, m1.y + ty),
						c2: new Point(m2.x + tx, m2.y + ty)
					};
				};
				Bezier.prototype.length = function() {
					var steps = 10;
					var length = 0;
					var px;
					var py;
					for (var i = 0; i <= steps; i += 1) {
						var t = i / steps;
						var cx = this.point(t, this.startPoint.x, this.control1.x, this.control2.x, this.endPoint.x);
						var cy = this.point(t, this.startPoint.y, this.control1.y, this.control2.y, this.endPoint.y);
						if (i > 0) {
							var xdiff = cx - px;
							var ydiff = cy - py;
							length += Math.sqrt(xdiff * xdiff + ydiff * ydiff);
						}
						px = cx;
						py = cy;
					}
					return length;
				};
				Bezier.prototype.point = function(t, start, c1, c2, end) {
					return (start * (1.0 - t) * (1.0 - t) * (1.0 - t)) +
						(3.0 * c1 * (1.0 - t) * (1.0 - t) * t) +
						(3.0 * c2 * (1.0 - t) * t * t) +
						(end * t * t * t);
				};
				return Bezier;
			}());

			function throttle(fn, wait) {
				if (wait === void 0) {
					wait = 250;
				}
				var previous = 0;
				var timeout = null;
				var result;
				var storedContext;
				var storedArgs;
				var later = function() {
					previous = Date.now();
					timeout = null;
					result = fn.apply(storedContext, storedArgs);
					if (!timeout) {
						storedContext = null;
						storedArgs = [];
					}
				};
				return function wrapper() {
					var args = [];
					for (var _i = 0; _i < arguments.length; _i++) {
						args[_i] = arguments[_i];
					}
					var now = Date.now();
					var remaining = wait - (now - previous);
					storedContext = this;
					storedArgs = args;
					if (remaining <= 0 || remaining > wait) {
						if (timeout) {
							clearTimeout(timeout);
							timeout = null;
						}
						previous = now;
						result = fn.apply(storedContext, storedArgs);
						if (!timeout) {
							storedContext = null;
							storedArgs = [];
						}
					} else if (!timeout) {
						timeout = window.setTimeout(later, remaining);
					}
					return result;
				};
			}

			var SignaturePad = (function() {
				function SignaturePad(canvas, options) {
					if (options === void 0) {
						options = {};
					}
					var _this = this;
					this.canvas = canvas;
					this.options = options;
					this._handleMouseDown = function(event) {
						if (event.which === 1) {
							_this._mouseButtonDown = true;
							_this._strokeBegin(event);
						}
					};
					this._handleMouseMove = function(event) {
						if (_this._mouseButtonDown) {
							_this._strokeMoveUpdate(event);
						}
					};
					this._handleMouseUp = function(event) {
						if (event.which === 1 && _this._mouseButtonDown) {
							_this._mouseButtonDown = false;
							_this._strokeEnd(event);
						}
					};
					this._handleTouchStart = function(event) {
						event.preventDefault();
						if (event.targetTouches.length === 1) {
							var touch = event.changedTouches[0];
							_this._strokeBegin(touch);
						}
					};
					this._handleTouchMove = function(event) {
						event.preventDefault();
						var touch = event.targetTouches[0];
						_this._strokeMoveUpdate(touch);
					};
					this._handleTouchEnd = function(event) {
						var wasCanvasTouched = event.target === _this.canvas;
						if (wasCanvasTouched) {
							event.preventDefault();
							var touch = event.changedTouches[0];
							_this._strokeEnd(touch);
						}
					};
					this.velocityFilterWeight = options.velocityFilterWeight || 0.7;
					this.minWidth = options.minWidth || 0.5;
					this.maxWidth = options.maxWidth || 2.5;
					this.throttle = ('throttle' in options ? options.throttle : 16);
					this.minDistance = ('minDistance' in options ?
						options.minDistance :
						5);
					if (this.throttle) {
						this._strokeMoveUpdate = throttle(SignaturePad.prototype._strokeUpdate, this.throttle);
					} else {
						this._strokeMoveUpdate = SignaturePad.prototype._strokeUpdate;
					}
					this.dotSize =
						options.dotSize ||
						function dotSize() {
							return (this.minWidth + this.maxWidth) / 2;
						};
					this.penColor = options.penColor || 'black';
					this.backgroundColor = options.backgroundColor || 'rgba(0,0,0,0)';
					this.onBegin = options.onBegin;
					this.onEnd = options.onEnd;
					this._ctx = canvas.getContext('2d');
					this.clear();
					this.on();
				}
				SignaturePad.prototype.clear = function() {
					var ctx = this._ctx;
					var canvas = this.canvas;
					ctx.fillStyle = this.backgroundColor;
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					ctx.fillRect(0, 0, canvas.width, canvas.height);
					this._data = [];
					this._reset();
					this._isEmpty = true;
				};
				SignaturePad.prototype.fromDataURL = function(dataUrl, options, callback) {
					var _this = this;
					if (options === void 0) {
						options = {};
					}
					var image = new Image();
					var ratio = options.ratio || window.devicePixelRatio || 1;
					var width = options.width || this.canvas.width / ratio;
					var height = options.height || this.canvas.height / ratio;
					this._reset();
					image.onload = function() {
						_this._ctx.drawImage(image, 0, 0, width, height);
						if (callback) {
							callback();
						}
					};
					image.onerror = function(error) {
						if (callback) {
							callback(error);
						}
					};
					image.src = dataUrl;
					this._isEmpty = false;
				};
				SignaturePad.prototype.toDataURL = function(type, encoderOptions) {
					if (type === void 0) {
						type = 'image/png';
					}
					switch (type) {
						case 'image/svg+xml':
							return this._toSVG();
						default:
							return this.canvas.toDataURL(type, encoderOptions);
					}
				};
				SignaturePad.prototype.on = function() {
					this.canvas.style.touchAction = 'none';
					this.canvas.style.msTouchAction = 'none';
					if (window.PointerEvent) {
						this._handlePointerEvents();
					} else {
						this._handleMouseEvents();
						if ('ontouchstart' in window) {
							this._handleTouchEvents();
						}
					}
				};
				SignaturePad.prototype.off = function() {
					this.canvas.style.touchAction = 'auto';
					this.canvas.style.msTouchAction = 'auto';
					this.canvas.removeEventListener('pointerdown', this._handleMouseDown);
					this.canvas.removeEventListener('pointermove', this._handleMouseMove);
					document.removeEventListener('pointerup', this._handleMouseUp);
					this.canvas.removeEventListener('mousedown', this._handleMouseDown);
					this.canvas.removeEventListener('mousemove', this._handleMouseMove);
					document.removeEventListener('mouseup', this._handleMouseUp);
					this.canvas.removeEventListener('touchstart', this._handleTouchStart);
					this.canvas.removeEventListener('touchmove', this._handleTouchMove);
					this.canvas.removeEventListener('touchend', this._handleTouchEnd);
				};
				SignaturePad.prototype.isEmpty = function() {
					return this._isEmpty;
				};
				SignaturePad.prototype.fromData = function(pointGroups) {
					var _this = this;
					this.clear();
					this._fromData(pointGroups, function(_a) {
						var color = _a.color,
							curve = _a.curve;
						return _this._drawCurve({
							color: color,
							curve: curve
						});
					}, function(_a) {
						var color = _a.color,
							point = _a.point;
						return _this._drawDot({
							color: color,
							point: point
						});
					});
					this._data = pointGroups;
				};
				SignaturePad.prototype.toData = function() {
					return this._data;
				};
				SignaturePad.prototype._strokeBegin = function(event) {
					var newPointGroup = {
						color: this.penColor,
						points: []
					};
					if (typeof this.onBegin === 'function') {
						this.onBegin(event);
					}
					this._data.push(newPointGroup);
					this._reset();
					this._strokeUpdate(event);
				};
				SignaturePad.prototype._strokeUpdate = function(event) {
					var x = event.clientX;
					var y = event.clientY;
					var point = this._createPoint(x, y);
					var lastPointGroup = this._data[this._data.length - 1];
					var lastPoints = lastPointGroup.points;
					var lastPoint = lastPoints.length > 0 && lastPoints[lastPoints.length - 1];
					var isLastPointTooClose = lastPoint ?
						point.distanceTo(lastPoint) <= this.minDistance :
						false;
					var color = lastPointGroup.color;
					if (!lastPoint || !(lastPoint && isLastPointTooClose)) {
						var curve = this._addPoint(point);
						if (!lastPoint) {
							this._drawDot({
								color: color,
								point: point
							});
						} else if (curve) {
							this._drawCurve({
								color: color,
								curve: curve
							});
						}
						lastPoints.push({
							time: point.time,
							x: point.x,
							y: point.y
						});
					}
				};
				SignaturePad.prototype._strokeEnd = function(event) {
					this._strokeUpdate(event);
					if (typeof this.onEnd === 'function') {
						this.onEnd(event);
					}
				};
				SignaturePad.prototype._handlePointerEvents = function() {
					this._mouseButtonDown = false;
					this.canvas.addEventListener('pointerdown', this._handleMouseDown);
					this.canvas.addEventListener('pointermove', this._handleMouseMove);
					document.addEventListener('pointerup', this._handleMouseUp);
				};
				SignaturePad.prototype._handleMouseEvents = function() {
					this._mouseButtonDown = false;
					this.canvas.addEventListener('mousedown', this._handleMouseDown);
					this.canvas.addEventListener('mousemove', this._handleMouseMove);
					document.addEventListener('mouseup', this._handleMouseUp);
				};
				SignaturePad.prototype._handleTouchEvents = function() {
					this.canvas.addEventListener('touchstart', this._handleTouchStart);
					this.canvas.addEventListener('touchmove', this._handleTouchMove);
					this.canvas.addEventListener('touchend', this._handleTouchEnd);
				};
				SignaturePad.prototype._reset = function() {
					this._lastPoints = [];
					this._lastVelocity = 0;
					this._lastWidth = (this.minWidth + this.maxWidth) / 2;
					this._ctx.fillStyle = this.penColor;
				};
				SignaturePad.prototype._createPoint = function(x, y) {
					var rect = this.canvas.getBoundingClientRect();
					return new Point(x - rect.left, y - rect.top, new Date().getTime());
				};
				SignaturePad.prototype._addPoint = function(point) {
					var _lastPoints = this._lastPoints;
					_lastPoints.push(point);
					if (_lastPoints.length > 2) {
						if (_lastPoints.length === 3) {
							_lastPoints.unshift(_lastPoints[0]);
						}
						var widths = this._calculateCurveWidths(_lastPoints[1], _lastPoints[2]);
						var curve = Bezier.fromPoints(_lastPoints, widths);
						_lastPoints.shift();
						return curve;
					}
					return null;
				};
				SignaturePad.prototype._calculateCurveWidths = function(startPoint, endPoint) {
					var velocity = this.velocityFilterWeight * endPoint.velocityFrom(startPoint) +
						(1 - this.velocityFilterWeight) * this._lastVelocity;
					var newWidth = this._strokeWidth(velocity);
					var widths = {
						end: newWidth,
						start: this._lastWidth
					};
					this._lastVelocity = velocity;
					this._lastWidth = newWidth;
					return widths;
				};
				SignaturePad.prototype._strokeWidth = function(velocity) {
					return Math.max(this.maxWidth / (velocity + 1), this.minWidth);
				};
				SignaturePad.prototype._drawCurveSegment = function(x, y, width) {
					var ctx = this._ctx;
					ctx.moveTo(x, y);
					ctx.arc(x, y, width, 0, 2 * Math.PI, false);
					this._isEmpty = false;
				};
				SignaturePad.prototype._drawCurve = function(_a) {
					var color = _a.color,
						curve = _a.curve;
					var ctx = this._ctx;
					var widthDelta = curve.endWidth - curve.startWidth;
					var drawSteps = Math.floor(curve.length()) * 2;
					ctx.beginPath();
					ctx.fillStyle = color;
					for (var i = 0; i < drawSteps; i += 1) {
						var t = i / drawSteps;
						var tt = t * t;
						var ttt = tt * t;
						var u = 1 - t;
						var uu = u * u;
						var uuu = uu * u;
						var x = uuu * curve.startPoint.x;
						x += 3 * uu * t * curve.control1.x;
						x += 3 * u * tt * curve.control2.x;
						x += ttt * curve.endPoint.x;
						var y = uuu * curve.startPoint.y;
						y += 3 * uu * t * curve.control1.y;
						y += 3 * u * tt * curve.control2.y;
						y += ttt * curve.endPoint.y;
						var width = curve.startWidth + ttt * widthDelta;
						this._drawCurveSegment(x, y, width);
					}
					ctx.closePath();
					ctx.fill();
				};
				SignaturePad.prototype._drawDot = function(_a) {
					var color = _a.color,
						point = _a.point;
					var ctx = this._ctx;
					var width = typeof this.dotSize === 'function' ? this.dotSize() : this.dotSize;
					ctx.beginPath();
					this._drawCurveSegment(point.x, point.y, width);
					ctx.closePath();
					ctx.fillStyle = color;
					ctx.fill();
				};
				SignaturePad.prototype._fromData = function(pointGroups, drawCurve, drawDot) {
					for (var _i = 0, pointGroups_1 = pointGroups; _i < pointGroups_1.length; _i++) {
						var group = pointGroups_1[_i];
						var color = group.color,
							points = group.points;
						if (points.length > 1) {
							for (var j = 0; j < points.length; j += 1) {
								var basicPoint = points[j];
								var point = new Point(basicPoint.x, basicPoint.y, basicPoint.time);
								this.penColor = color;
								if (j === 0) {
									this._reset();
								}
								var curve = this._addPoint(point);
								if (curve) {
									drawCurve({
										color: color,
										curve: curve
									});
								}
							}
						} else {
							this._reset();
							drawDot({
								color: color,
								point: points[0]
							});
						}
					}
				};
				SignaturePad.prototype._toSVG = function() {
					var _this = this;
					var pointGroups = this._data;
					var ratio = Math.max(window.devicePixelRatio || 1, 1);
					var minX = 0;
					var minY = 0;
					var maxX = this.canvas.width / ratio;
					var maxY = this.canvas.height / ratio;
					var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
					svg.setAttribute('width', this.canvas.width.toString());
					svg.setAttribute('height', this.canvas.height.toString());
					this._fromData(pointGroups, function(_a) {
						var color = _a.color,
							curve = _a.curve;
						var path = document.createElement('path');
						if (!isNaN(curve.control1.x) &&
							!isNaN(curve.control1.y) &&
							!isNaN(curve.control2.x) &&
							!isNaN(curve.control2.y)) {
							var attr = "M " + curve.startPoint.x.toFixed(3) + "," + curve.startPoint.y.toFixed(3) + " " +
								("C " + curve.control1.x.toFixed(3) + "," + curve.control1.y.toFixed(3) + " ") +
								(curve.control2.x.toFixed(3) + "," + curve.control2.y.toFixed(3) + " ") +
								(curve.endPoint.x.toFixed(3) + "," + curve.endPoint.y.toFixed(3));
							path.setAttribute('d', attr);
							path.setAttribute('stroke-width', (curve.endWidth * 2.25).toFixed(3));
							path.setAttribute('stroke', color);
							path.setAttribute('fill', 'none');
							path.setAttribute('stroke-linecap', 'round');
							svg.appendChild(path);
						}
					}, function(_a) {
						var color = _a.color,
							point = _a.point;
						var circle = document.createElement('circle');
						var dotSize = typeof _this.dotSize === 'function' ? _this.dotSize() : _this.dotSize;
						circle.setAttribute('r', dotSize.toString());
						circle.setAttribute('cx', point.x.toString());
						circle.setAttribute('cy', point.y.toString());
						circle.setAttribute('fill', color);
						svg.appendChild(circle);
					});
					var prefix = 'data:image/svg+xml;base64,';
					var header = '<svg' +
						' xmlns="http://www.w3.org/2000/svg"' +
						' xmlns:xlink="http://www.w3.org/1999/xlink"' +
						(" viewBox=\"" + minX + " " + minY + " " + maxX + " " + maxY + "\"") +
						(" width=\"" + maxX + "\"") +
						(" height=\"" + maxY + "\"") +
						'>';
					var body = svg.innerHTML;
					if (body === undefined) {
						var dummy = document.createElement('dummy');
						var nodes = svg.childNodes;
						dummy.innerHTML = '';
						for (var i = 0; i < nodes.length; i += 1) {
							dummy.appendChild(nodes[i].cloneNode(true));
						}
						body = dummy.innerHTML;
					}
					var footer = '</svg>';
					var data = header + body + footer;
					// alert("SVG : "+data);
					save_sign(data);
					return prefix + btoa(data);
				};
				return SignaturePad;
			}());

			return SignaturePad;

		})));
	</script>
	<script>
		var wrapper = document.getElementById("signature-pad");
		var clearButton = wrapper.querySelector("[data-action=clear]");
		var changeColorButton = wrapper.querySelector("[data-action=change-color]");
		var undoButton = wrapper.querySelector("[data-action=undo]");
		var savePNGButton = wrapper.querySelector("[data-action=save-png]");
		var saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
		var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
		var canvas = wrapper.querySelector("canvas");
		var signaturePad = new SignaturePad(canvas, {
			// It's Necessary to use an opaque color when saving image as JPEG;
			// this option can be omitted if only saving as PNG or SVG
			backgroundColor: 'rgb(255, 255, 255)'
		});

		// Adjust canvas coordinate space taking into account pixel ratio,
		// to make it look crisp on mobile devices.
		// This also causes canvas to be cleared.
		function resizeCanvas() {
			// When zoomed out to less than 100%, for some very strange reason,
			// some browsers report devicePixelRatio as less than 1
			// and only part of the canvas is cleared then.
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			// This part causes the canvas to be cleared
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);

			// This library does not listen for canvas changes, so after the canvas is automatically
			// cleared by the browser, SignaturePad#isEmpty might still return false, even though the
			// canvas looks empty, because the internal data of this library wasn't cleared. To make sure
			// that the state of this library is consistent with visual state of the canvas, you
			// have to clear it manually.
			signaturePad.clear();
		}

		// On mobile devices it might make more sense to listen to orientation change,
		// rather than window resize events.
		window.onresize = resizeCanvas;
		resizeCanvas();

		function download(dataURL, filename) {
			if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
				window.open(dataURL);
			} else {
				var blob = dataURLToBlob(dataURL);
				var url = window.URL.createObjectURL(blob);

				var a = document.createElement("a");
				a.style = "display: none";
				a.href = url;
				a.download = filename;
				// document.body.appendChild(a);
				// a.click();

				// window.URL.revokeObjectURL(url);
			}
		}

		// One could simply use Canvas#toBlob method instead, but it's just to show
		// that it can be done using result of SignaturePad#toDataURL.
		function dataURLToBlob(dataURL) {
			// Code taken from https://github.com/ebidel/filer.js
			var parts = dataURL.split(';base64,');
			var contentType = parts[0].split(":")[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			var Str = "";
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
				Str += uInt8Array[i];
			}

			var bbb = new Blob([uInt8Array], {
				type: contentType
			});
			return new Blob([uInt8Array], {
				type: contentType
			});
		}

		clearButton.addEventListener("click", function(event) {
			signaturePad.clear();
		});

		undoButton.addEventListener("click", function(event) {
			var data = signaturePad.toData();

			if (data) {
				data.pop(); // remove the last dot or line
				signaturePad.fromData(data);
			}
		});

		changeColorButton.addEventListener("click", function(event) {
			var r = Math.round(Math.random() * 255);
			var g = Math.round(Math.random() * 255);
			var b = Math.round(Math.random() * 255);
			var color = "rgb(" + r + "," + g + "," + b + ")";

			signaturePad.penColor = color;
		});

		savePNGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL();
				download(dataURL, "signature.png");
			}
		});

		saveJPGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL("image/jpeg");
				download(dataURL, "signature.jpg");
			}
		});

		saveSVGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL('image/svg+xml');
				download(dataURL, "signature.svg");
			}
		});
	</script>
</body>

</html>