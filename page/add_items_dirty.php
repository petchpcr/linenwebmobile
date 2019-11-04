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
		var all_i_code = [];
		var all_i_name = [];
		var all_dep_code = [];
		var all_dep_name = [];
		var now_id = "";
		var now_item = "";
		var now_dep;
		var mul_qty = {};
		var mul_weight = {};
		var isRound = {};
		var dep_search = 0;;

		var round = {};
		round['dep'] = {};
		round['dep']['item'] = {};

		round['dep']['item'][0] = [159, 40];
		round['dep']['item'][1] = ['a', 'b'];
		round['dep']['item'][1] = undefined;

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_dep(0);
			choose_items();
			load_items();
			// test();
			if (typeof round['dep']['item'][1] === 'undefined') {
				// console.log("Not have");
			}
			$.each(round['dep']['item'], function(key, val) {
				// console.log(key + " | " + val);

			});
			// console.log($.type(round['dep']['item'][0][0]));

			// $.each(arr_test, function(item, item_val) {
			// 		console.log(item);
			// 		$.each(item_val, function(dep, dep_val) {
			// 				console.log(dep+" | "+dep_val);
			// 		});
			// });
			// console.log(items);
			// arr_test['item'].forEach(function(value,index) {
			// 	console.log(index+" = "+value);
			// });
		});

		// function
		function test() {
			var data = {
				'round': round,
				'STATUS': 'test'
			};
			senddata(JSON.stringify(data));
		}

		function load_items(FromDelRound) {
			mul_qty = {};
			mul_weight = {};
			var data = {
				'DocNo': DocNo,
				'refDoc': refDoc,
				'FromDelRound': FromDelRound,
				'STATUS': 'load_items'
			};
			senddata(JSON.stringify(data));
		}

		function load_dep(num) {
			dep_search = num;
			if (num != 1) {
				all_dep_code = [];
				all_dep_name = [];
			}

			var Search = $("#search_dep").val();
			var data = {
				'siteCode': siteCode,
				'Search': Search,
				'dep_search': dep_search,
				'STATUS': 'load_dep'
			};
			senddata(JSON.stringify(data));
		}

		function choose_items() {
			var Search = $("#search_items").val();
			var data = {
				'Search': Search,
				'siteCode': siteCode,
				'refDoc': refDoc,
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

		function chk_dep(DepCode) {
			now_dep = DepCode;
			$("#search_items").val("");
			choose_items();
			$("#md_dep").modal('hide');
			$("#md_item").modal('show');
		}

		function before_select_item(chk) {
			$("#search_dep").val("");
			load_dep(chk);
		}

		function select_item(ItemCode) {
			now_item = ItemCode;

			$("#md_item").modal('hide');
			edit_round(now_dep, now_item);
			// $("#md_round").modal('show');
		}

		function item_handler() {
			var RequestName = $("#search_items").val();
			var data = {
				'DocNo': DocNo,
				'now_dep': now_dep,
				'RequestName': RequestName,
				'STATUS': 'item_handler'
			};
			senddata(JSON.stringify(data));
		}

		function list_to_arr() {
			var have = 0;
			$(".chk-dep").each(function() {
				var dep = $(this).val();
				var num = $(this).attr("data-num");
				if (dep_search == 1) { // ถ้ากด search
					if ($(this).is(':checked')) {
						if (mul_qty[dep][now_item] == null) {
							if (typeof mul_qty[dep][now_item] === 'undefined') {
								if (typeof mul_qty[dep] === 'undefined') {
									mul_qty[dep] = {};
									mul_qty[dep][now_item] = {};
								} else {
									mul_qty[dep][now_item] = {};
								}
							}
							if (typeof mul_weight[dep][now_item] === 'undefined') {
								if (typeof mul_weight[dep] === 'undefined') {
									mul_weight[dep] = {};
									mul_weight[dep][now_item] = {};
								} else {
									mul_weight[dep][now_item] = {};
								}
							}

							mul_qty[dep][now_item] = 0;
							mul_weight[dep][now_item] = 0;
						}
						have++;
					}

				} else { // ถ้าไม่ search

					if ($(this).is(':checked')) {
						if (mul_qty[dep][now_item] == null) {
							if (typeof mul_qty[dep][now_item] === 'undefined') {
								if (typeof mul_qty[dep] === 'undefined') {
									mul_qty[dep] = {};
									mul_qty[dep][now_item] = {};
								} else {
									mul_qty[dep][now_item] = {};
								}
							}
							if (typeof mul_weight[dep][now_item] === 'undefined') {
								if (typeof mul_weight[dep] === 'undefined') {
									mul_weight[dep] = {};
									mul_weight[dep][now_item] = {};
								} else {
									mul_weight[dep][now_item] = {};
								}
							}

							mul_qty[dep][now_item] = 0;
							mul_weight[dep][now_item] = 0;
						}
						have++;
					} else {

						if (typeof mul_qty[dep] === 'undefined') {
							mul_qty[dep] = {};
							mul_qty[dep][now_item] = {};
						}
						if (typeof mul_weight[dep] === 'undefined') {
							mul_weight[dep] = {};
							mul_weight[dep][now_item] = {};
						}
						mul_qty[dep][now_item] = null;
						mul_weight[dep][now_item] = null;
					}
				}
			});

			// if (typeof mul_qty[now_item] == 'undefined') {
			// 	mul_qty[now_item] = {};
			// }
			// if (typeof mul_weight[now_item] == 'undefined') {
			// 	mul_weight[now_item] = {};
			// }
			// var have = 0;
			// $(".chk-dep").each(function() {
			// 	var dep = $(this).val();
			// 	var num = $(this).attr("data-num");
			// 	if ($(this).is(':checked')) {
			// 		mul_qty[now_item][dep] = Number($("#depqty" + num).val());
			// 		mul_weight[now_item][dep] = Number($("#depweight" + num).val());
			// 		have++;
			// 	} else {
			// 		mul_qty[now_item][dep] = 0;
			// 		mul_weight[now_item][dep] = 0;
			// 	}
			// });

			if (have == 0) {
				$(now_id).prop("checked", false);
			} else {
				$(now_id).prop("checked", true);
			}
			// Notsave = 1;


			// console.log(mul_qty);
			add_item(1);
			gen_to_site();
		}

		function gen_to_site() {
			$("#items").empty();
			// var doc_qty = 0;
			var doc_weight = Number(0);
			$.each(mul_qty, function(dep, qty) {
				// หาชื่อ Department
				var dep_name;
				$.each(all_dep_code, function(dn_key, dn_val) {
					if (dn_val == dep) {
						dep_name = all_dep_name[dn_key];
					}
				});

				// หาชื่อ Item
				var item_name;
				$.each(qty, function(item, qty_val) {
					if (qty_val != null) {
						var DHL = 0;
						$.each(all_i_code, function(i_key, i_val) {
							if (i_val == item) {
								item_name = all_i_name[i_key];
								DHL++;
							}
						});
						if (DHL == 0) {
							item_name = item;
						}
						// ยัดใส่ใน div#items
						var Str = "<tr>";
						Str += "		<td>";
						Str += "		<div class='row'>";
						Str += "			<div class='col-4 text-truncate'>" + dep_name + "</div>";
						Str += "			<div class='col-4 text-truncate'>" + item_name + "</div>";
						Str += "			<div class='col-4 d-flex'>";
						Str += "				<div class='mx-auto justify-content-center d-flex align-items-center'>";
						Str += "					<input type='text' disabled class='form-control text-center bg-white mr-2' value='" + mul_qty[dep][item] + "' style='max-width:70px;'>";
						Str += "					<input type='text' disabled class='form-control text-center bg-white' value='" + mul_weight[dep][item] + "' style='max-width:70px;'>";
						Str += "				</div>";
						Str += "				<button onclick='edit_round(\"" + dep + "\",\"" + item + "\")' class='btn btn-info btn-block p-0 ml-2' style='width:40px;border-radius:225px;'>";
						Str += "					<i class='fas fa-plus'></i>";
						Str += "				</button>";
						Str += "			</div>";
						Str += "		</div>";
						Str += "	</td>";
						Str += "</tr>";

						$("#items").append(Str);
						doc_weight = Number(doc_weight) + Number(mul_weight[dep][item]);
					}
				});
			});

			// if (doc_qty > 0) {
			// 	$("#sum_num").val(doc_qty);
			// } else {
			// 	$("#sum_num").val("");
			// }

			if (doc_weight > 0) {
				$("#sum_weight").val(doc_weight);
			} else {
				$("#sum_weight").val("");
			}

			$("#md_dep").modal('hide');
			// console.log("sum_weight | " + doc_weight);
		}

		function edit_round(dep, item) {
			var Not_Handler = 0;
			// หาชื่อ Department
			var dep_name;
			$.each(all_dep_code, function(dn_key, dn_val) {
				if (dn_val == dep) {
					$("#round_depname").text(all_dep_name[dn_key]);
				}
			});
			// หาชื่อ Item
			$.each(all_i_code, function(i_key, i_val) {
				if (i_val == item) {
					$("#round_itemname").text(all_i_name[i_key]);
					Not_Handler++;
				}
			});

			$("#val_qty").val(1);
			$("#val_weight").val("");
			$("#show_round").empty();

			if (Not_Handler == 0) {
				$("#round_itemname").text(item);
				var click = "add_round('" + dep + "','HDL')";
			} else {
				var click = "add_round('" + dep + "','" + item + "')";
			}
			console.log(Not_Handler);
			$("#btn_add_round").attr("onclick", click);
			var data = {
				'DocNo': DocNo,
				'dep': dep,
				'item': item,
				'STATUS': 'edit_round'
			};
			senddata(JSON.stringify(data));

		}

		function add_round(dep, item) {
			var qty = $("#val_qty").val();
			var weight = $("#val_weight").val();

			if (qty != "" && weight != "") {
				var data = {
					'DocNo': DocNo,
					'dep': dep,
					'item': item,
					'qty': qty,
					'weight': weight,
					'STATUS': 'add_round'
				};
				senddata(JSON.stringify(data));
			}

		}

		function del_round(id, RowID, dep, item) {
			var data = {
				'id': id,
				'RowID': RowID,
				'dep': dep,
				'item': item,
				'STATUS': 'del_round'
			};
			senddata(JSON.stringify(data));
		}

		function del_items(num) {
			var chkid = "#chk" + num;
			var code = $(chkid).val();
			console.log("CODE : " + code);

			$.each(mul_qty[code], function(dep, qty) {
				mul_qty[code][dep] = 0;
				mul_weight[code][dep] = 0;
			});

			console.log("After Delete");
			Notsave = 1;
			gen_to_site();
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
				// cal_weight();
				// cal_num();
			});
		}

		function make_number_weight(num) {
			$('.weightonly').on('input', function() {
				this.value = this.value.replace(/[^0-9.]/g, ''); //<-- replace all other than given set of values
				var num1 = this.value.length;
				var num2 = (this.value.replace(/[^0-9]/g, '')).length;
				if ((num1 - num2) >= 2) {
					this.value = $("#" + this.id).data("oldnum");
				} else if ((num1 - num2) == 0) {
					// this.value = Number(this.value);
				}
				$("#" + this.id).data("oldnum", $("#" + this.id).val());

				if ($("#" + this.id).attr("data-num") == num) {
					var chk_id = "#depchk" + num;
					if (this.value > 0) {
						$(chk_id).prop("checked", true);
					} else {
						$(chk_id).prop("checked", false);
					}
				}

				// cal_weight();
			});
		}

		function currencyFormat(num) {
			var price = num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
			$("#sum_weight").val(price);
		}

		function close_open_modal(close, open) {
			$(close).modal('hide');
			$(open).modal('show');
		}

		function sign_fac() {
			window.location.href = 'signature.php?Menu=' + Menu + '&DocNo=' + DocNo + '&siteCode=' + siteCode + '&fnc=sign_fac';
		}

		function sign_nh() {
			window.location.href = 'signature.php?Menu=' + Menu + '&DocNo=' + DocNo + '&siteCode=' + siteCode + '&fnc=sign_nh';
		}

		function add_item(NotBack) {
			var data = {
				'NotBack': NotBack,
				'DocNo': DocNo,
				'mul_qty': mul_qty,
				'mul_weight': mul_weight,
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
			if (Notsave == 1 || Delback == 1) {
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
							window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu;
						} else if (Menu == 'clean') {
							window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu;
						} else if (Menu == 'newlinentable') {
							window.location.href = 'new_linen_item.php?siteCode=' + siteCode + '&Menu=' + Menu;
						}
					}
				});
			} else {
				if (Menu == 'dirty') {
					window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu;
				} else if (Menu == 'clean') {
					window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu;
				} else if (Menu == 'newlinentable') {
					window.location.href = 'new_linen_item.php?siteCode=' + siteCode + '&Menu=' + Menu;
				}
			}
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			if (Menu == 'dirty') {
				var URL = '../process/add_items_dirty.php';
			} else if (Menu == 'clean') {
				var URL = '../process/add_items_clean.php';
			} else {
				var URL = '../process/add_items_new_linen.php';
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

							if (temp['SignFac'] == null && temp['SignNH'] == null) {
								$("#btn_sign_fac").removeAttr('hidden');
							}
							else if (temp['SignFac'] != null && temp['SignNH'] == null) {
								$("#btn_sign_nh").removeAttr('hidden');
							}
							else if (temp['SignFac'] != null && temp['SignNH'] != null) {
								$("#btn_save").removeAttr('hidden');
							}

							all_dep_code.forEach(function(dep) {
								// สร้าง Obj เปล่าๆขึ้นมา
								if (typeof mul_qty[dep] === 'undefined') {
									mul_qty[dep] = {};
								}
								if (typeof mul_weight[dep] === 'undefined') {
									mul_weight[dep] = {};
								}

								for (var i = 0; i < temp['count']; i++) {
									if (dep == temp[i]['DepCode']) {
										// ยัดค่าที่ Select ได้ เข้าไปใน Obj
										mul_qty[dep][temp[i]['ItemCode']] = temp[i]['Qty'];
										mul_weight[dep][temp[i]['ItemCode']] = temp[i]['Weight'];
									}
								}
							});

							gen_to_site();

						} else if (temp["form"] == 'load_dep') {
							$("#choose_dep").empty();
							for (var i = 0; i < temp['count']; i++) {
								var code_i = all_dep_code.indexOf(temp[i]['DepCode']);
								var name_i = all_dep_name.indexOf(temp[i]['DepName']);
								if (!(code_i >= 0) && !(name_i >= 0)) {
									if (temp['dep_search'] != 1) {

									}
									all_dep_code.push(temp[i]['DepCode']);
									all_dep_name.push(temp[i]['DepName']);
								}
								var id = "depchk" + i;
								var Str = "<div onclick='chk_dep(\"" + temp[i]['DepCode'] + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex align-items-center col-12 text-truncate text-left font-weight-bold px-0'>";
								Str += "<div class='mr-auto text-truncate'>" + temp[i]['DepName'] + "</div>";
								// Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number()' class='form-control text-center ml-2 numonly' type='text' id='depqty" + i + "' value='1' style='max-width:80px;'>";
								// Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number_weight(" + i + ")' class='form-control text-center mx-2 weightonly' data-num='" + i + "' type='text' id='depweight" + i + "' placeholder='0.00' style='max-width:80px;'>";
								// Str += "<input class='m-0 chk-dep' type='checkbox' id='" + id + "' data-num='" + i + "' value='" + temp[i]['DepCode'] + "'>";
								Str += "</div>";
								Str += "</div>";
								$("#choose_dep").append(Str);
							}
							// if (temp['dep_search'] != 1 && temp['dep_search'] != 0) {
							// 	select_item(temp['dep_search']);
							// }
							if (temp['dep_search'] != 0) {
								$("#md_dep").modal('show');
							}
							// console.log(all_dep_code);
						} else if (temp["form"] == 'choose_items') {
							var HptName = temp['HptName'];
							var DepName = temp['DepName'];
							$("#choose_item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var code_i = all_i_code.indexOf(temp[i]['ItemCode']);
								var name_i = all_i_name.indexOf(temp[i]['ItemName']);
								if (!(code_i >= 0) && !(name_i >= 0)) {
									all_i_code.push(temp[i]['ItemCode']);
									all_i_name.push(temp[i]['ItemName']);
								}

								var checked = "";
								$.each(mul_qty, function(dep, qty) {
									$.each(qty, function(item, qty_val) {
										if (temp[i]['ItemCode'] == item && qty_val >= 0) {
											checked = "checked";
										}
									});
								});

								var num = i + 1;
								var chk = "chk" + num;
								var Str = "<div onclick='select_item(\"" + temp[i]['ItemCode'] + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold py-2 pr-0'>";
								Str += "<div>" + temp[i]['ItemName'] + "</div>";
								// Str += "<input class='m-0 chk-item' type='checkbox' id='" + chk + "' data-name='" + temp[i]['ItemName'] + "' value='" + temp[i]['ItemCode'] + "' data-num='" + num + "' " + checked + "></div>";
								Str += "</div>";
								Str += "</div>";

								$("#choose_item").append(Str);
							}
						} else if (temp["form"] == 'add_item') {
							Notsave = 0;
							Delback = 0;
							if (temp['NotBack'] == 0) {
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
							}

						} else if (temp["form"] == 'del_back') {
							Notsave = 0;
							Delback = 0;
							back();
						} else if (temp["form"] == 'item_handler') {
							var handler = temp['RequestName'];
							var DepCode = temp['now_dep'];
							edit_round(DepCode,handler);

						} else if (temp["form"] == 'edit_round') {
							for (var i = 0; i < temp['cnt']; i++) {
								var Str = "<div class='col-12 d-flex mb-2 px-0'>";
								Str += "<div style='width:50px;'>";
								Str += "รอบ " + Number(i + 1);
								Str += "</div>";
								Str += "<div class='d-flex'>";
								Str += "<div style='width:110px;'>จำนวน :</div> ";
								Str += "<input class='form-control text-center' disabled value='" + temp[i]['Qty'] + "' type='text'>";
								Str += "</div>";
								Str += "<div class='d-flex'>";
								Str += "<div style='width:110px;'>น้ำหนัก :</div> ";
								Str += "<input class='form-control text-center' disabled value='" + temp[i]['Weight'] + "' type='text'>";
								Str += "</div>";
								Str += "<button onclick='del_round(\"" + temp[i]['id'] + "\",\"" + temp[i]['RowID'] + "\",\"" + temp['dep'] + "\",\"" + temp['item'] + "\")' class='btn btn-danger p-0 ml-2' style='min-width:40px;'><i class='fas fa-times'></i></button>";
								Str += "</div>";

								$("#show_round").append(Str);
							}
							$("#md_round").modal('show');

						} else if (temp["form"] == 'add_round') {
							load_items();
							var dep = temp['dep'];
							var item = temp['item'];
							edit_round(dep, item);

						} else if (temp["form"] == 'del_round') {
							load_items(1);
							var dep = temp['dep'];
							var item = temp['item'];
							edit_round(dep, item);

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						var message = "";
						if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
						} else if (temp["form"] == 'load_items') {
							// choose_items();
							// $("#md_item").modal('show');
							$("#items").empty();
							if (temp['SignFac'] == null && temp['SignNH'] == null) {
								$("#btn_sign_fac").removeAttr('hidden');
							}
							else if (temp['SignFac'] != null && temp['SignNH'] == null) {
								$("#btn_sign_nh").removeAttr('hidden');
							}
							else if (temp['SignFac'] != null && temp['SignNH'] != null) {
								$("#btn_save").removeAttr('hidden');
							}
							load_dep();
							$("#md_round").modal('hide');
							// $("#md_dep").modal('show');

						} else if (temp["form"] == 'add_item') {
							alert("error ADD ITEM");
						} else if (temp["form"] == 'edit_round') {
							$("#md_round").modal('show');
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
			<!-- <div id="items" class="col-lg-9 col-md-10 col-sm-12 pb-3 border" style="margin-bottom:100px;"> -->
			<table class="table table-hover col-lg-9 col-md-10 col-sm-12" style="margin-bottom:100px;">
				<thead>
					<tr class="bg-primary text-white">
						<th scope="col">
							<div class="row">
								<div class="col-4 text-center"><?php echo $genarray['department'][$language]; ?></div>
								<div class="col-4 text-center"><?php echo $array['list'][$language]; ?></div>
								<div class="col-4 d-flex">
									<div class="mx-auto">
										<?php echo $array['numberSize'][$language] . " / " . $genarray['weight'][$language]; ?>
									</div>
									<div style="width:40px;"></div>
								</div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="items"></tbody>
			</table>

			<!-- </div> -->
		</div>
	</div>

	<div id="add_doc" class="fixed-bottom d-flex justify-content-center pb-4 bg-white">
		<div class="col-lg-9 col-md-10 col-sm-12">
			<?php
			if (false) {
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
					<button onclick="load_dep()" class="btn btn-create btn-block" type="button">
						<i class="fas fa-plus mr-1"></i><?php echo $array['addList'][$language]; ?>
					</button>
				</div>
				<div class="col-6">
					<button id="btn_save" onclick="add_item(0)" class="btn btn-success btn-block mt-0" type="button" data-toggle="modal" data-target="#" hidden>
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
	<div class="modal fade" id="md_item" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo $array['addList'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div class="d-flex mb-3">
						<input onkeyup="choose_items()" id="search_items" class="form-control" type="text" placeholder="<?php echo $array['searchitem'][$language]; ?>">
						<button onclick="item_handler()" class="btn btn-success p-0 ml-2" style='min-width:40px;border-radius:225px;'><i class="fas fa-plus"></i></button>
					</div>
					<div id="choose_item">
					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="close_open_modal('#md_item','#md_dep')" type="button" class="btn btn-primary m-2"><?php echo $genarray['back'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
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
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['chooseDepartment'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<input onkeyup="load_dep(1)" id="search_dep" class="form-control mb-3" type="text" placeholder="<?php echo $array['searchdep'][$language]; ?>">
					<div id="choose_dep"></div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="w-100 d-flex justify-content-center m-0">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_round" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['Getweight'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">

					<div class="font-weight-bold d-flex justify-content-center"><?php echo $genarray['department'][$language]; ?> : <div id="round_depname"></div>
					</div>
					<div class="font-weight-bold d-flex justify-content-center mb-3"><?php echo $array['list'][$language]; ?> : <div id="round_itemname"></div>
					</div>

					<div class="d-flex">
						<div class="d-flex">
							<div style="width:110px;"><?php echo $array['numberSize'][$language]; ?> :</div>
							<input onkeydown="make_number()" id="val_qty" class="form-control text-center numonly" type="text">
						</div>
						<div class="d-flex">
							<div style="width:110px;"><?php echo $array['weight'][$language]; ?> :</div>
							<input onkeydown="make_number_weight()" id="val_weight" class="form-control text-center weightonly" placeholder="0.0" type="text">
						</div>
						<button id="btn_add_round" class='btn btn-success p-0 ml-2' style='min-width:40px;border-radius:225px;'>
							<i class='fas fa-plus'></i>
						</button>
					</div>

					<hr>

					<div id="show_round"></div>

				</div>

				<div class="modal-footer text-center">
					<div class="col-6 text-right">
						<button onclick="close_open_modal('#md_round','#md_item')" type="button" class="btn btn-primary m-2"><?php echo $genarray['back'][$language]; ?></button>
					</div>
					<div class="col-6 text-left">
						<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
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