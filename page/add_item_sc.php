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
$Create = $_GET['Create'];
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
		var siteCode = '<?php echo $siteCode; ?>';
		var Menu = '<?php echo $Menu; ?>';
		var DocNo = "<?php echo $DocNo ?>";
		var DepCode = "<?php echo $DepCode ?>";
		var Create = "<?php echo isset($_GET['Create'])?$Create:0; ?>";
		var Notsave = 0;
		var old_i_code = [];
		var old_i_name = [];
		var old_i_qty = [];
		var old_i_par = [];
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
			$(".chk-item").each(function() {
				var code, name, qty;
				if ($(this).is(':checked')) {
					Notsave = 1;
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
			$("#item").empty();
			var num = 0;
			old_i_code.forEach(function(val, i) {
				var order = Number(old_i_par[i]-old_i_qty[i]);
				var Str = "<tr onclick='view_item(\""+val+"\","+num+")' id='list"+num+"'>";
						Str +=  "<td>";
						Str +=  "	<div class='row'>";
						Str +=  "		<div class='col-3 d-flex align-items-center'>"+old_i_name[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+old_i_par[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+old_i_qty[i]+"</div>";
						Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+order+"</div>";
						Str +=  "		</div>";
						Str +=  "</td>";
						Str += "</tr>";

				$("#item").append(Str);
				num++;
			});

			new_i_code.forEach(function(val, i) {
				var order = Number(new_i_par[i]-new_i_qty[i]);
				var Str = "<tr onclick='view_item(\""+val+"\","+num+")' id='list"+num+"'>";
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
				num++;
			});
		}

		function view_item(code,i) {
			var iold = old_i_code.indexOf(code);
			var inew = new_i_code.indexOf(code);
			if (iold != -1) {
				$("#newqty").val(old_i_qty[iold]);
				$("#viewname").text(old_i_name[iold]);
				$("#viewname").attr("data-code",code);
				$("#viewname").attr("data-ar","old");
				$("#md_editqty").modal('show');
			}
			else if (inew != -1) {
				$("#newqty").val(new_i_qty[inew]);
				$("#viewname").text(new_i_name[inew]);
				$("#viewname").attr("data-code",code);
				$("#viewname").attr("data-ar","new");
				$("#md_editqty").modal('show');
			}
		}

		function edit_qty() {
			Notsave = 1;
			var ar = $("#viewname").attr("data-ar");
			var Title = "จำนวนผิดพลาด";
			var Type = "warning";
			if (ar == 'old') {
				var index = old_i_code.indexOf($("#viewname").attr("data-code"));
				if (Number($("#newqty").val()) > Number(old_i_par[index])) {
					var Text = "จำนวนสูงสุดคือ "+old_i_par[index];
					AlertError(Title, Text, Type);
				} else {
					old_i_qty[index] = $("#newqty").val();
					ar_to_site();
					$("#md_editqty").modal('hide');
				}
			}
			else if (ar == 'new') {
				var index = new_i_code.indexOf($("#viewname").attr("data-code"));
				if (Number($("#newqty").val()) > Number(new_i_par[index])) {
					var Text = "จำนวนสูงสุดคือ "+new_i_par[index];
					AlertError(Title, Text, Type);
				} else {
					new_i_qty[index] = $("#newqty").val();
					ar_to_site();
					$("#md_editqty").modal('hide');
				}
			}
		}

		function del_item() {
			Notsave = 1;
			var code = $("#viewname").attr("data-code");
			// หา Index ของคำนั้น
			var iold = old_i_code.indexOf(code); 
			var inew = new_i_code.indexOf(code);

			// ลบ Index ที่หาเจอ
			if (iold != -1) {
				old_i_code.splice(iold, 1); 
				old_i_name.splice(iold, 1);
				old_i_qty.splice(iold, 1);
				old_i_par.splice(iold, 1);
			}
			else if (inew != -1) {
				new_i_code.splice(inew, 1);
				new_i_name.splice(inew, 1);
				new_i_qty.splice(inew, 1);
				new_i_par.splice(inew, 1);
			}
			ar_to_site();
		}

		function add_item() {
			var old_size = old_i_code.length;
			var new_size = new_i_code.length;
			if (old_size == 0 && new_size == 0) {
				var Title = "ไม่สามารถบันทึกข้อมูลได้";
				var Text = "ต้องมีข้อมูลในเอกสาร";
				var Type = "warning";
				AlertError(Title, Text, Type);
			} else {
				var old_code = old_i_code.join(',');
				var old_qty = old_i_qty.join(',');
				var old_par = old_i_par.join(',');
				var new_code = new_i_code.join(',');
				var new_qty = new_i_qty.join(',');
				var new_par = new_i_par.join(',');

				var data = {
					'DocNo': DocNo,
					'old_code': old_code,
					'old_qty': old_qty,
					'old_par': old_par,
					'new_code': new_code,
					'new_qty': new_qty,
					'new_par': new_par,
					'STATUS': 'add_item'
				};
				senddata(JSON.stringify(data));
			}
		}

		function del_doc() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'del_doc'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			if (Notsave == 1 || Create == 1) {
				swal({
					title: "ข้อมูลยังไม่ถูกบันทึก",
					text: "ต้องการละทิ้งหรือไม่ ?",
					type: "question",
					showConfirmButton: true,
					showCancelButton: true,
					confirmButtonColor: '#d33',
					confirmButtonText: '<?php echo $genarray['yes2'][$language]; ?>',
					cancelButtonText: '<?php echo $genarray['cancel'][$language]; ?>'
				}).then((result) => {
					if (Create == 1) {
						del_doc();
					} else {
						window.location.href = 'shelf_count.php?siteCode=' + siteCode + '&Menu=' + Menu;
					}
				})
			} else {
				window.location.href = 'shelf_count.php?siteCode=' + siteCode + '&Menu=' + Menu;
			}
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
								for (var i = 0; i < temp['cnt']; i++) {
									old_i_code.push(temp[i]['ItemCode']);
									old_i_name.push(temp[i]['ItemName']);
									old_i_qty.push(temp[i]['CcQty']);
									old_i_par.push(temp[i]['ParQty']);
									var Str = "<tr onclick='view_item(\""+temp[i]['ItemCode']+"\","+i+")' id='list"+i+"'>";
											Str +=  "<td>";
											Str +=  "	<div class='row'>";
											Str +=  "		<div class='col-3 d-flex align-items-center'>"+temp[i]['ItemName']+"</div>";
											Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+temp[i]['ParQty']+"</div>";
											Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+temp[i]['CcQty']+"</div>";
											Str +=  "		<div class='col-3 d-flex align-items-center justify-content-center'>"+temp[i]['TotalQty']+"</div>";
											Str +=  "		</div>";
											Str +=  "</td>";
											Str += "</tr>";

									$("#item").append(Str);
								}
							}

						} else if (temp["form"] == 'choose_items') {
							$("#choose_item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var have = 0;
								old_i_code.forEach(function(val) {
									if (val == temp[i]['ItemCode']) {
										have++;
									}
								});
								new_i_code.forEach(function(val) {
									if (val == temp[i]['ItemCode']) {
										have++;
									}
								});
								if (have == 0) {
									var chk_id = "chchk" + i;
									var qty_id = "chqty" + i;
									var Str = "<div onclick='select_item(" + i + ")' class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
											Str += "	<div class='d-flex justify-content-between align-items-center col-12 text-truncate text-left font-weight-bold pr-0'>";
											Str += "		<div class='mr-auto text-truncate'>" + temp[i]['ItemName'] + "</div>";
											Str += "		<input onclick='event.cancelBubble=true;' onkeydown='make_number()' id='" + qty_id + "' value='1' type='text' class='form-control text-center numonly mx-2' style='max-width:100px;'>";
											Str += "		<input class='m-0 chk-item' type='checkbox' id='" + chk_id + "' data-code='" + temp[i]['ItemCode'] + "' data-name='" + temp[i]['ItemName'] + "' data-i='" + i + "'>";
											Str += "	</div>";
											Str += "</div>";

									$("#choose_item").append(Str);
								}
							}
							$("#md_chooseitem").modal('show');

						} else if (temp["form"] == 'get_par') {
							new_i_par = [];
							new_i_par = temp['ar_par'].split(',');
							ar_to_site();
							$("#md_chooseitem").modal('hide');

						} else if (temp["form"] == 'add_item') {
							window.location.href = 'shelf_count.php?siteCode=' + siteCode + '&Menu=' + Menu;

						} else if (temp["form"] == 'del_doc') {
							window.location.href = 'shelf_count.php?siteCode=' + siteCode + '&Menu=' + Menu;

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
				<button onclick="choose_items()" class="btn btn-create btn-block mr-3" style="max-width:250px;" type="button">
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
					<h5 class="modal-title" id="exampleModalLabel">แก้ไขรายการ</h5>
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
							</div>
						</div>

					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button onclick="edit_qty()" type="button" class="btn btn-primary m-2"><i class="fas fa-check mr-2"></i>ยืนยัน</button>
						</div>
						<div class="col-6 text-left">
							<button onclick="del_item()" id="btn_del" type="button" class="btn btn-danger m-2" data-dismiss="modal"><i class="fas fa-trash-alt mr-2"></i>ลบออก</button>
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