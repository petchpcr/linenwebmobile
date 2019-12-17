<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
	header("location:../index.html");
}
if (isset($_GET['Delback'])) {
	$Delback = $_GET['Delback'];
} else {
	$Delback = 0;
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
		var DocNo = "<?php echo $DocNo ?>";
		var refDoc = "<?php echo $refDoc ?>";
		var Menu = '<?php echo $Menu; ?>';
		var Userid = "<?php echo $Userid ?>";
		var Delback = "<?php echo $Delback ?>";
		var Notsave = 0;
		var arr_new_items = [];
		var all_i_code = [];
		var all_i_name = [];
		var all_dep_code = [];
		var all_dep_name = [];
		var item_num = {};
		var now_id = "";
		var now_item = "";
		var mul_qty = {};
		var mul_weight = {};

		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_dep();
			choose_items(1);
			// load_items();
		});

		// function
		function load_items() {
			arr_old_items = [];
			arr_new_items = [];
			var data = {
				'DocNo': DocNo,
				'refDoc': refDoc,
				'STATUS': 'load_items'
			};
			senddata(JSON.stringify(data));
		}

		function load_dep() {
			var data = {
				'siteCode': siteCode,
				'STATUS': 'load_dep'
			};
			senddata(JSON.stringify(data));
		}

		function choose_items(first) {
			var Search = $("#search_items").val();
			var data = {
				'Search': Search,
				'first': first,
				'siteCode': siteCode,
				'DocNo': DocNo,
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

		function chk_dep(dep) {
			var id = "#" + dep;
			if ($(id).is(':checked')) {
				$(id).prop("checked", false);
			} else {
				$(id).prop("checked", true);
			}
		}

		function clear_all_dep() {
			$(".chk-dep").each(function() {
				var num = $(this).attr("data-num");
				$(".chk-dep").each(function() {
					var weight_id = "#depweight" + num;
					$(weight_id).val("");
				});
				$(this).prop("checked", false);
			});
		}

		function select_item(item) {
			now_item = item;

			var data = {
				'DocNo': DocNo,
				'item': item,
				'STATUS': 'select_item'
			};
			senddata(JSON.stringify(data));
			clear_all_dep();
		}

		function list_to_arr() {
			var have = 0;
			arr_new_items.push(now_item);
			arr_new_items = Array.from(new Set(arr_new_items));

			if (typeof mul_qty[now_item] == 'undefined') {
				mul_qty[now_item] = {};
			}
			if (typeof mul_weight[now_item] == 'undefined') {
				mul_weight[now_item] = {};
			}
			if (typeof item_num[now_item] == 'undefined') {
				item_num[now_item] = {};
			}

			// ยัด qty และ weight เข้าไปใน object
			$(".chk-dep").each(function() {
				var dep = $(this).val();
				var num = $(this).attr("data-num");
				if ($(this).is(':checked')) {
					mul_qty[now_item][dep] = Number($("#depqty" + num).val());
					mul_weight[now_item][dep] = Number($("#depweight" + num).val());
					item_num[now_item][dep] = num;
					have++;
				} else {
					mul_qty[now_item][dep] = 0;
					mul_weight[now_item][dep] = 0;
				}
			});

			// check ไอเทมที่มีอยู่ ถ้าไม่มี uncheck
			$(".chk-item").each(function() {
				var this_item = $(this).val();
				if (this_item == now_item) {
					if (have == 0) {
						$(this).prop("checked", false);
					} else {
						$(this).prop("checked", true);
					}
				}
			});

			Notsave = 1;
			gen_to_site();
		}

		function edit_value() {
			$("#btn_edit_value").prop("disabled", true);
			var have = 0;
			var ar_qty = [];
			var ar_weight = [];
			var ar_depcode = [];
			$(".chk-dep").each(function() {
				if ($(this).is(':checked')) {
					var DepCode = $(this).val();
					var qtyid = "#depqty" + $(this).attr("data-num");
					var weightid = "#depweight" + $(this).attr("data-num");
					ar_qty.push($(qtyid).val());
					ar_weight.push($(weightid).val());
					ar_depcode.push(DepCode);
					have++;
				}
			});

			var data = {
				'DocNo': DocNo,
				'now_item': now_item,
				'ar_qty': ar_qty,
				'ar_weight': ar_weight,
				'ar_depcode': ar_depcode,
				'STATUS': 'edit_value'
			};
			senddata(JSON.stringify(data));
		}

		function gen_to_site() {
			$("#items").empty();
			var doc_qty = 0;
			var doc_weight = 0;

			$.each(mul_qty, function(item, value) {
				var have = 0;
				var sum_qty = 0;
				var sum_weight = 0;
				var num = "";
				$.each(value, function(dep, qty) {
					if (qty > 0) {
						sum_qty = Number(sum_qty) + Number(qty);
						sum_weight = Number(sum_weight) + Number(mul_weight[item][dep]);
						// num = item_num[item][dep];
					}
				});

				// ถ้ามีจำนวนให้ Append
				if (sum_qty > 0) {

					// หาชื่อไอเทม
					var code_i = all_i_code.indexOf(item);
					var name = all_i_name[code_i];

					var click = "chk" + num;
					var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'>";
					Str += "<div class='col'><div class='row'>";
					Str += "<div class='d-flex align-items-center col-12 pr-0'>";
					Str += "<div class='text-truncate font-weight-bold mr-auto'>" + name + "</div>";
					Str += "<button class='btn btn-info mt-2 mr-3 py-0 px-5' onclick='select_item(\"" + item + "\")'>แผนก</button>";
					Str += "<img onclick='del_items(\"" + item + "\")' src='../img/close.png' style='height:25px;margin:5px 5px 5px 5px;'>";
					Str += "</div>";
					Str += "<div class='d-flex justify-content-end align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
					Str += "</div>";
					Str += "</div>";
					Str += "<div class='row'>";
					Str += "<div class='d-flex align-items-center col-12 input-group p-0'>";
					Str += "<label class='ml-auto mr-1'>จำนวน</label>";
					Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-2' ";
					Str += "style='max-width:90px;' value='" + sum_qty + "' placeholder='0' disabled>";
					Str += "<label class='mr-1'><?php echo $array['weight'][$language]; ?></label>";
					Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1' ";
					Str += "style='max-width:90px;' value='" + sum_weight + "' placeholder='0.0' disabled>";
					Str += "<img src='../img/kg.png' onclick='show_weight(\"" + item + "\")' height='40'>";
					Str += "</div>";
					Str += "</div>";
					Str += "</div>";

					$("#items").append(Str);
				}

				doc_qty = Number(doc_qty + sum_qty);
				doc_weight = Number(doc_weight + sum_weight);
			});

			if (doc_qty > 0) {
				$("#sum_num").val(doc_qty);
			} else {
				$("#sum_num").val("");
			}

			if (doc_weight > 0) {
				$("#sum_weight").val(doc_weight.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,'));
			} else {
				$("#sum_weight").val("");
			}

			$("#md_dep").modal('hide');
		}

		function del_items(item) {
			var data = {
				'DocNo': DocNo,
				'item': item,
				'STATUS': 'del_items'
			};
			senddata(JSON.stringify(data));
		}

		function show_weight(item) {
			// $("#val_weight").val("");
			// $("#btn_change_weight").attr("onclick","change_weight(\"" + item + "\",\"" + dep + "\")");
			// $("#md_weight").modal('show');
		}

		function change_weight(item, dep) {
			var val = $("#val_weight").val();
			mul_weight[item][dep] = val;

			Notsave = 1;
			gen_to_site();

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
			});
		}

		function make_number_weight(num) {
			$('.weightonly').on('input', function() {
				this.value = this.value.replace(/[^0-9.]/g, ''); //<-- replace all other than given set of values
				var num1 = this.value.length;
				var num2 = (this.value.replace(/[^0-9]/g, '')).length;
				if ((num1 - num2) >= 2) {
					this.value = $("#" + this.id).data("oldnum");
				} else if ((num1 - num2) == 0) {}
				$("#" + this.id).data("oldnum", $("#" + this.id).val());

				if ($("#" + this.id).attr("data-num") == num) {
					var chk_id = "#depchk" + num;
					if (this.value > 0) {
						$(chk_id).prop("checked", true);
					} else {
						$(chk_id).prop("checked", false);
					}
				}
			});
		}

		function currencyFormat(num) {
			var price = num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
			$("#sum_weight").val(price);
		}

		function add_item() {
			$("#btn_save").prop("disabled", true);
			var data = {
				'DocNo': DocNo,
				'STATUS': 'add_item'
			};
			senddata(JSON.stringify(data));
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

		function back() {
			// if (Notsave == 1 || Delback == 1) {
			if (false) {
				swal({
					title: '<?php echo $genarray['confirm'][$language]; ?>',
					text: '<?php echo $genarray['YNwantToExit'][$language]; ?>',
					type: 'question',
					showConfirmButton: true,
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					confirmButtonText: '<?php echo $genarray['yes2'][$language]; ?>'
				}).then((result) => {
					if (Delback == 1) {
						var data = {
							'DocNo': DocNo,
							'Menu': Menu,
							'STATUS': 'del_back'
						};
						senddata(JSON.stringify(data));
					} else {
						if (Menu == 'dirty') {
							window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
						} else if (Menu == 'clean') {
							window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
						} else if (Menu == 'newlinentable') {
							window.location.href = 'new_linen_item.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
						}
					}
				});
			} else {
				if (Menu == 'dirty') {
					window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
				} else if (Menu == 'clean') {
					window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
				} else if (Menu == 'newlinentable') {
					window.location.href = 'new_linen_item.php?siteCode=' + siteCode + '&Menu=' + Menu + txt_form_out;
				}
			}
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);

			$.ajax({
				url: '../process/add_items_newlinen.php',
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
							var total_qty = 0;
							var total_weight = 0;
							for (var i = 0; i < temp['cnt']; i++) {
								var Str = "<div class='row alert alert-info mb-3 p-0'>";
								Str += "		<div class='col'>";
								Str += "			<div class='row'>";

								Str += "				<div class='d-flex align-items-center col-lg-8 col-md-7 col-sm-6 col-6'>";
								Str += "					<div class='text-truncate font-weight-bold'>" + temp['ItemName'][i] + "</div>";
								Str += "				</div>";

								Str += "				<div class='d-flex align-items-center col-lg-4 col-md-5 col-sm-6 col-6 input-group p-0'>";
								Str += "					<div class='pr-1'><?php echo $array['numberSize'][$language]; ?></div>";
								Str += "					<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white ";
								Str += "					my-2 mr-1 itemnum numonly' value='" + temp['Qty'][i] + "' disabled>";

								Str += "					<div class='pr-1'><?php echo $array['weight'][$language]; ?></div>";
								Str += "					<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item old numonly' ";
								Str += "					data-qty='" + temp['Qty'][i] + "' value='" + temp['Weight'][i] + "' placeholder='0.0' disabled>";

								Str += "					<img onclick='del_items(\"" + temp['ItemCode'][i] + "\")' src='../img/close.png' style='height:25px;margin-right:5px;margin-bottom:20px;'>";
								Str += "				</div>";

								Str += "			</div>";
								Str += "		</div>";
								Str += "	</div>";

								$("#items").append(Str);
							}

							$("#sum_num").val(temp['Total_Qty']);
							$("#sum_weight").val(temp['Total_Weight']);
						} else if (temp["form"] == 'load_dep') {
							$("#choose_dep").empty();
							for (var i = 0; i < temp['count']; i++) {
								var code_i = all_dep_code.indexOf(temp[i]['DepCode']);
								var name_i = all_dep_name.indexOf(temp[i]['DepName']);
								if (!(code_i >= 0) && !(name_i >= 0)) {
									all_dep_code.push(temp[i]['DepCode']);
									all_dep_name.push(temp[i]['DepName']);
								}
								var id = "depchk" + i;
								var Str = "<div onclick='chk_dep(\"" + id + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex align-items-center col-12 text-truncate text-left font-weight-bold px-0'>";
								Str += "<div class='mr-auto text-truncate'>" + temp[i]['DepName'] + "</div>";
								Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number()' class='form-control text-center ml-2 numonly' type='text' id='depqty" + i + "' value='1' style='max-width:80px;'>";
								Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number_weight(" + i + ")' class='form-control text-center mx-2 weightonly' data-num='" + i + "' type='text' id='depweight" + i + "' placeholder='0.00' style='max-width:80px;'>";
								Str += "<input class='m-0 chk-dep' type='checkbox' id='" + id + "' data-num='" + i + "' value='" + temp[i]['DepCode'] + "'>";
								Str += "</div>";
								Str += "</div>";
								$("#choose_dep").append(Str);
							}
						} else if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var checked = "";
								if (temp[i]['check'] == 1) {
									checked = "checked";
								}
								var Str = "<div onclick='select_item(\"" + temp[i]['ItemCode'] + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold py-2 pr-0'>";
								Str += "<div>" + temp[i]['ItemName'] + "</div>";
								Str += "<input class='m-0 chk-item' type='checkbox' value='" + temp[i]['ItemCode'] + "' " + checked + "></div>";
								Str += "</div>";
								Str += "</div>";

								$("#choose_item").append(Str);
							}

							if (temp['first'] == 1) { // ถ้าโหลดครั้งแรก
								load_items();
							}

						} else if (temp["form"] == 'select_item') {
							for (var i = 0; i < temp['cnt']; i++) {
								var Qty = temp[i]['Qty'];
								var Weight = temp[i]['Weight'];
								var DepCode = temp[i]['DepCode'];
								$(".chk-dep").each(function() {
									var chk_DepCode = $(this).val();
									if (chk_DepCode == DepCode) {
										$(this).prop("checked", true);
										var qtyid = "#depqty" + $(this).attr("data-num");
										var weightid = "#depweight" + $(this).attr("data-num");
										$(qtyid).val(Qty);
										$(weightid).val(Weight);
									}
								});
							}

							$("#md_dep").modal('show');
						} else if (temp["form"] == 'edit_value') {
							choose_items(1);
							$("#md_dep").modal('hide');
							$("#btn_edit_value").prop("disabled", false);

						} else if (temp["form"] == 'del_items') {
							load_items();

						} else if (temp["form"] == 'add_item') {
							Notsave = 0;
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
							Notsave = 0;
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
							$("#md_item").modal('show');
						} else if (temp["form"] == 'select_item') {
							$("#md_dep").modal('show');
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
									<input id="sum_num" type="text" class="form-control text-center bg-white" placeholder="0" disabled>
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

					<div id="choose_item">
					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_dep" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">เลือกแผนก</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div class="bg-primary text-white d-flex mb-2 mx-0" style="border-radius:0.25rem;">
						<div class="text-left w-100 py-0 pr-0 pl-4"><?php echo $genarray['department'][$language]; ?></div>
						<div class="text-left p-0" style="width:150px;"><?php echo $genarray['qty'][$language]; ?></div>
						<div class="text-left p-0" style="width:165px;"><?php echo $genarray['weight'][$language]; ?></div>
					</div>
					<div id="choose_dep"></div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button id="btn_edit_value" onclick="edit_value()" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
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
					<input onkeydown="make_number()" id="val_weight" class="form-control text-center mb-3 numonly" type="text">
				</div>

				<div class="modal-footer text-center">
					<div class="w-100 d-flex justify-content-center m-0">
						<button id="btn_change_weight" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
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