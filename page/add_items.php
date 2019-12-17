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

		function choose_items() {
			var Search = $("#search_items").val();
			var data = {
				'DepCode': DepCode,
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

			$("#btn_edit").prop("disabled",false);
			$("#new_qty").prop("disabled",false);
			$("#new_weight").prop("disabled",false);

			$("#md_edit").modal('show');
		}

		function change_value(item, name) {
			var qty = $("#new_qty").val();
			var weight = $("#new_weight").val();

			if (qty > 0) {
				$("#btn_edit").prop("disabled",true);
				$("#new_qty").prop("disabled",true);
				$("#new_weight").prop("disabled",true);
				
				var data = {
					'DocNo': DocNo,
					'item': item,
					'qty': qty,
					'weight': weight,
					'STATUS': 'change_value'
				};
				senddata(JSON.stringify(data));
			}
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
			var last_item = $('.item:last').data("num");
			if (last_item == null || last_item == '') {
				last_item = 0;
			}
			var num = Number(last_item) + 1;
			var total = $(".chk-item").length;
			var count = 0;

			$(".chk-item").each(function() {
				if ($(this).is(':checked')) {
					var id = "weight" + num;
					var name = $(this).data('name');
					var code = $(this).val();
					var qty = 0;
					var unit = 1;
					var idqty = id + "qty";
					var div = "#chs" + num;

					var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'><div class='col'><div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
					Str += "<div class='text-truncate font-weight-bold'>" + name + "</div></div><div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><label class='mr-1'><?php echo $array['numberSize'][$language]; ?></label>";
					Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 itemnum numonly'";
					Str += "id='" + idqty + "' placeholder='0' value='1'>";
					Str += "<img onclick='del_items(" + num + ")' src='../img/close.png' style='height:25px;margin-right:5px;margin-bottom:20px;'></div></div>";
					Str += "<div class='row'><div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'></div>";
					Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'><label class='mr-1'><?php echo $array['weight'][$language]; ?></label><input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item new numonly' ";
					Str += "data-code='" + code + "' data-qty='" + qty + "' data-unit='" + unit + "' id='" + id + "' data-num='" + num + "' data-oldnum=0 placeholder='0.0'>";
					Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'></div></div></div>";


					$("#items").append(Str);
					arr_new_items.push(code);
					$(div).remove();
					count++;
					num++;
					cal_weight();
					cal_num();
				};
			});
			if (total == count) {
				$("#md_item").modal('hide');
			} else {
				choose_items();
			}
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
				window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
			}
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/add_items_clean.php';

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
							$("#btn_edit").prop("disabled",false);
							$("#new_qty").prop("disabled",false);
							$("#new_weight").prop("disabled",false);
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
							choose_items();
							$("#md_item").modal('show');

						}else if (temp["form"] == 'add_item') {
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
					<button id="btn_save" onclick="add_item()" class="btn btn-success btn-block" type="button" data-toggle="modal" data-target="#">
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

					<div id="choose_item"></div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 m-0">
						<button type="button" class="btn btn-secondary my-2 mx-auto" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo $genarray['Getweight'][$language]; ?></h5>
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
</body>

</html>