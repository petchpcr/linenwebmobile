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
		var arr_old_items = [];
		var arr_new_items = [];
		var all_i_code = [];
		var all_i_name = [];
		var all_dep_code = [];
		var all_dep_name = [];
		var arr_del_items = [];
		var now_id = "";
		var now_item = "";
		var obj = {};
		var mul_qty = {};
		var mul_weight = {};

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_dep();
			choose_items();
			load_items();
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
		function load_items() {
			arr_old_items = [];
			arr_new_items = [];
			arr_del_items = [];
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

		function choose_items() {
			var Search = $("#search_items").val();
			var data = {
				'Search': Search,
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

		function chk_dep(dep) {
			var id = "#" + dep;
			if ($(id).is(':checked')) {
				$(id).prop("checked", false);
			} else {
				$(id).prop("checked", true);
			}
		}

		function select_item(chk) {
			now_id = "#" + chk;
			now_item = $(now_id).val();
			console.log(now_item);

			if (typeof mul_qty[now_item] !== 'undefined') {
				all_dep_code.forEach(function(dep) {
					var qty = mul_qty[now_item][dep];

					console.log("Dep = " + dep + " | Qty = " + qty);
					var id, qtyid, weightid;
					var have = 0;
					$(".chk-dep").each(function() {

						if ($(this).val() == dep) {
							qtyid = "#depqty" + $(this).attr("data-num");
							weightid = "#depweight" + $(this).attr("data-num");
							id = "#" + $(this).attr('id');
							if (qty > 0) {
								have++;
							}
						};
						console.log("Dep = " + $(this).val() + " VS Dep = " + dep + " | Have = " + have);
					});

					if (have > 0) {
						console.log("Check");
						$(id).prop("checked", true);
						$(qtyid).val(qty);
						$(weightid).val(mul_weight[now_item][dep]);
					} else {
						console.log("Un Check");
						$(id).prop("checked", false);
						$(qtyid).val(1);
						$(weightid).val("");
					}

				});
			} else {
				$(".chk-dep").each(function() {
					var qtyid = "#depqty" + $(this).attr("data-num");
					var weightid = "#depweight" + $(this).attr("data-num");
					$(qtyid).val(1);
					$(weightid).val("");
					$(this).prop("checked", false);
				});
			}



			// $.each(obj, function(item, item_val) {
			// 	// console.log(item+" | "+item_val);
			// 	if (now_item == item_val) {
			// 		$.each(item_val, function(key, qty) {
			// 			console.log(key+" | "+qty);
			// 			if (qty > 0) {
			// 				$(".chk-item").each(function() {
			// 					if ($(this).val() == key) {
			// 						$(this).prop("checked", true);
			// 					};
			// 				});
			// 			} else {
			// 				$(".chk-item").each(function() {
			// 					if ($(this).val() == key) {
			// 						$(this).prop("checked", false);
			// 					};
			// 				});
			// 			}
			// 		});
			// 	}
			// });

			// arr_new_items.forEach(function(value) {
			// 	console.log(value);
			// });
			// var have = 0;
			// $(".chk-item").each(function() {
			// 	if ($(this).is(':checked')) {
			// 		var code = $(this).val();
			// 		arr_new_items.push(code);
			// 		// have++;
			// 	}
			// });
			// arr_new_items.forEach(function(value) {
			// 	console.log(value);
			// });
			// if (have == 0) {
			// 	Title = "คุณไม่ได้เลือกรายการ";
			// 	Text = "โปรดเลือกรายการก่อนทำขั้นตอนถัดไป !";
			// 	Type = "warning";
			// 	AlertError(Title, Text, Type);
			// } else {
			$("#md_dep").modal('show');
			// }

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
					if (false) {
						var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'>";
						Str += "<div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
						Str += "<div class='text-truncate font-weight-bold'>" + name + "</div>";
						Str += "</div>";
						Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
						Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item new numonly' ";
						Str += "id='" + id + "' data-code='" + code + "' data-qty='" + qty + "' data-unit='" + unit + "' data-num='" + num + "'data-oldnum=0 placeholder='0.0'>";
						Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'><img onclick='del_items(" + num + ")' src='../img/close.png' style='height:25px;margin-right:5px;margin-bottom:20px;'>";
						Str += "</div>";
						Str += "</div>";
					} else {
						var idqty = id + "qty";
						var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'>";
						Str += "<div class='col'><div class='row'>";
						Str += "<div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
						Str += "<div class='text-truncate font-weight-bold'>" + name + "</div>";
						Str += "</div>";
						Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
						Str += "<label class='mr-1'><?php echo $array['numberSize'][$language]; ?></label>";
						Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 itemnum numonly' id='" + idqty + "' placeholder='0' value='1'>";
						Str += "<img onclick='del_items(" + num + ")' src='../img/close.png' style='height:25px;margin-right:5px;margin-bottom:20px;'>";
						Str += "</div>";
						Str += "</div>";
						Str += "<div class='row'>";
						Str += "<div class='d-flex align-items-center col-xl-10 col-lg-9 col-md-9 col-sm-8 col-7'>";
						// Str +=    "<div><button class='btn btn-info py-0'>เลือกแผนก</button></div>";
						Str += "</div>";
						Str += "<div class='d-flex align-items-center col-xl-2 col-lg-3 col-md-3 col-sm-4 col-5 input-group p-0'>";
						Str += "<label class='mr-1'><?php echo $array['weight'][$language]; ?></label>";
						Str += "<input onkeydown='make_number()' type='text' class='form-control rounded text-center bg-white my-2 mr-1 item new numonly' ";
						Str += "data-code='" + code + "' data-qty='" + qty + "' data-unit='" + unit + "' id='" + id + "' data-num='" + num + "' data-oldnum=0 placeholder='0.0'>";
						Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'>";
						Str += "</div>";
						Str += "</div>";
						Str += "</div>";
					}

					$("#items").append(Str);
					arr_new_items.push(code);
					num++;
					cal_weight();
					cal_num();
				};
			});
		}

		function list_to_arr() {
			arr_new_items.push(now_item);
			arr_new_items = Array.from(new Set(arr_new_items));
			if (typeof mul_qty[now_item] == 'undefined') {
				mul_qty[now_item] = {};
			}
			if (typeof mul_weight[now_item] == 'undefined') {
				mul_weight[now_item] = {};
			}
			var have = 0;
			$(".chk-dep").each(function() {
				var dep = $(this).val();
				var num = $(this).attr("data-num");
				if ($(this).is(':checked')) {
					mul_qty[now_item][dep] = Number($("#depqty" + num).val());
					mul_weight[now_item][dep] = Number($("#depweight" + num).val());
					have++;
				} else {
					mul_qty[now_item][dep] = 0;
					mul_weight[now_item][dep] = 0;
				}
			});

			if (have == 0) {
				$(now_id).prop("checked", false);
			} else {
				$(now_id).prop("checked", true);
			}
			Notsave = 1;
			// console.log(mul_qty);

			// mul_qty['BHQLPPPUP010414'].forEach(function(value,key) {
			// });
			// arr_new_items.forEach(function(item) {
			// 	console.log(key+" | "+value);

			// });
			gen_to_site();
		}

		function gen_to_site() {
			$("#items").empty();
			var doc_qty = 0;
			var doc_weight = 0;
			arr_new_items.forEach(function(item) {
				var have = 0;
				var sum_qty = 0;
				var sum_weight = 0;
				$.each(mul_qty[item], function(dep, qty) {
					// console.log(qty + " | " + dep);
					if (qty > 0) {
						have++;
						sum_qty = Number(sum_qty) + Number(qty);
						sum_weight = Number(sum_weight) + Number(mul_weight[item][dep]);
					}
				});
				if (have > 0) {
					var name, num;
					$(".chk-item").each(function() {
						if ($(this).val() == item) {
							num = $(this).attr("data-num");
							name = $(this).attr("data-name");
						};
					});
					if (!(sum_weight > 0)) {
						sum_weight = "";
					}
					var click = "chk" + num;
					var Str = "<div id='item" + num + "' class='row alert alert-info mb-3 p-0'>";
					Str += "<div class='col'><div class='row'>";
					Str += "<div class='d-flex align-items-center col-12 pr-0'>";
					Str += "<div class='text-truncate font-weight-bold mr-auto'>" + name + "</div>";
					Str += "<button class='btn btn-info mt-2 mr-3 py-0 px-5' onclick='select_item(\"" + click + "\")'>แผนก</button>";
					Str += "<img onclick='del_items(" + num + ")' src='../img/close.png' style='height:25px;margin:5px 5px 5px 5px;'>";
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
					Str += "<img src='../img/kg.png' onclick='show_weight(" + num + ")' height='40'>";
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
				$("#sum_weight").val(doc_weight);
			} else {
				$("#sum_weight").val("");
			}

			$("#md_dep").modal('hide');
			// console.log("sum_qty | " + doc_qty);
			// console.log("sum_weight | " + doc_weight);
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

		function add_item() {
			// var Item = { 
			// 	BHT0122215: { Dep2 : ['10','2.52'],
			// 								Dep3 : ['40','10.3']
			// 							},
			// 	OUT0122215: { Dep19 : '11'}};

			// Item['BHT0122215']['Dep2'][0] = 11;

			var data = {
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
							for (var i = 0; i < temp['count']; i++) {
								arr_new_items.push(temp[i]['ItemCode']);
							}
							arr_new_items = Array.from(new Set(arr_new_items));

							arr_new_items.forEach(function(item) {
								mul_qty[item] = {};
								mul_weight[item] = {};
								for (var i = 0; i < temp['count']; i++) {
									if (item == temp[i]['ItemCode']) {
										mul_qty[item][temp[i]['DepCode']] = temp[i]['Qty'];
										mul_weight[item][temp[i]['DepCode']] = temp[i]['Weight'];
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
									all_dep_code.push(temp[i]['DepCode']);
									all_dep_name.push(temp[i]['DepName']);
								}
								var id = "depchk" + i;
								var Str = "<div onclick='chk_dep(\"" + id + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>";
								Str += "<div class='mr-auto'>" + temp[i]['DepName'] + "</div>";
								Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number()' class='form-control text-center ml-2 numonly' type='text' id='depqty" + i + "' value='1' style='max-width:80px;'>";
								Str += "<input onclick='event.cancelBubble=true;' onkeydown='make_number_weight(" + i + ")' class='form-control text-center mx-2 weightonly' data-num='" + i + "' type='text' id='depweight" + i + "' placeholder='0.00' style='max-width:80px;'>";
								Str += "<input class='m-0 chk-dep' type='checkbox' id='" + id + "' data-num='" + i + "' value='" + temp[i]['DepCode'] + "'>";
								Str += "</div>";
								Str += "</div>";
								$("#choose_dep").append(Str);
							}
							console.log(all_dep_code);
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

								// var have_old = 0;
								// var have_new = 0;

								// if (Menu != 'dirty') {
								// 	if (arr_old_items.length > 0) {
								// 		for (var ii = 0; ii < arr_old_items.length; ii++) {
								// 			if (arr_old_items[ii] == temp[i]['ItemCode']) {
								// 				have_old = 1;
								// 			}
								// 		}
								// 	}

								// 	if (arr_new_items.length > 0) {
								// 		for (var iii = 0; iii < arr_new_items.length; iii++) {
								// 			if (arr_new_items[iii] == temp[i]['ItemCode']) {
								// 				have_new = 1;
								// 			}
								// 		}
								// 	}
								// }

								// if (have_old == 0 && have_new == 0) {
								var checked = "";
								if (typeof mul_qty[temp[i]['ItemCode']] !== 'undefined') {
									$.each(mul_qty[temp[i]['ItemCode']], function(dep, qty) {
										// mul_qty[temp[i]['ItemCode']].forEach(function(qty,dep) {
										if (qty > 0) {
											checked = "checked";
										}
									});
								}
								var num = i + 1;
								var chk = "chk" + num;
								// var Str =  "<div onclick='chk_items(\"" + chk + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								var Str = "<div onclick='select_item(\"" + chk + "\")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold py-2 pr-0'>";
								Str += "<div>" + temp[i]['ItemName'] + "</div>";
								Str += "<input class='m-0 chk-item' type='checkbox' id='" + chk + "' data-name='" + temp[i]['ItemName'] + "' value='" + temp[i]['ItemCode'] + "' data-num='" + num + "' " + checked + "></div>";
								// Str += "<hr class='m-0'><div class='col-12 text-truncate text-left'>" + HptName + " / " + DepName;
								Str += "</div>";
								Str += "</div>";

								$("#choose_item").append(Str);
								// }
							}
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
							choose_items();
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
				<img src="../img/logo.png" width="156" height="40" />
			</div>
			<div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div>
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
						<!-- <div class="col-6 text-right">
							<button id="btn_add_items" onclick="select_chk()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
						</div> -->
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
						<div class="text-center w-100 p-0"><?php echo $genarray['item'][$language]; ?></div>
						<div class="text-left p-0" style="width:150px;"><?php echo $genarray['qty'][$language]; ?></div>
						<div class="text-left p-0" style="width:165px;"><?php echo $genarray['weight'][$language]; ?></div>
					</div>
					<div id="choose_dep"></div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="list_to_arr()" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
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