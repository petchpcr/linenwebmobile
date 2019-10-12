<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$From = $_GET['From'];
if (!isset($_GET['From'])) {
	$From = $_GET['TypeDoc'];
}
$TypeDoc = $_GET['TypeDoc'];
$Menu = $_GET['Menu'];
$siteCode = $_GET['siteCode'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/dirty_lang.xml');
$json = json_encode($xml);
$array = json_decode($json, TRUE);
$genxml = simplexml_load_file('../xml/Language/general_lang.xml');
$json = json_encode($genxml);
$genarray = json_decode($json, TRUE);
require '../getTimeZone.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	$Menu = $_GET['Menu'];
	if ($Menu == 'dirty') {
		echo "<title>" . $genarray['titledirty'][$language] . $genarray['titleDocument'][$language] . "</title>";
	} else {
		echo "<title>" . $genarray['titlefactory'][$language] . $genarray['titleDocument'][$language] . "</title>";
	}
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var Userid = "<?php echo $Userid ?>";
		var siteCode = "<?php echo $siteCode ?>";
		var Menu = "<?php echo $Menu ?>";
		var From = "<?php echo $From ?>";
		var TypeDoc = "<?php echo $TypeDoc ?>";
		var rcv_code = [];
		var rcv_qty = [];
		var Arr_ItemCode = [];
		var Arr_ItemName = [];
		var Arr_Qty = [];
		var sendmail = 0;

		$(document).ready(function(e) {
			if (Menu == 'factory') {
				$("#add_doc").remove();
			}
			load_dep();
			load_site();
			load_doc();
			load_Fac();
		});

		// function
		function load_dep() {
			var data = {
				'siteCode': siteCode,
				'STATUS': 'load_dep'
			};
			senddata(JSON.stringify(data));
		}

		function load_Fac() {
			var data = {
				'STATUS': 'load_Fac'
			};
			senddata(JSON.stringify(data));
		}


		function load_site() {
			$('#datepicker').val("<?php echo date("d-m-Y"); ?>");
			var data = {
				'siteCode': siteCode,
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function load_doc() {
			var search = $('#datepicker').val();
			// var searchDate = new Date(search);
			if (Menu == 'factory') {
				var status = 'load_doc_procees';
			} else {
				var status = 'load_doc';
			}
			var data = {
				'search': search,
				'siteCode': siteCode,
				'Menu': Menu,
				'From': From,
				'STATUS': status
			};
			senddata(JSON.stringify(data));
		}

		function show_process(DocNo, From) {
			if (Menu == 'dirty') {
				window.location.href = 'dirty_view.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + DocNo;
			} else if (Menu == 'factory') {
				window.location.href = 'process.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + DocNo + '&From=' + From + '&TypeDoc=' + TypeDoc;
			}
		}

		function receive_zero(DocNo, From) {
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'receive_zero'
			};
			senddata(JSON.stringify(data));
		}

		function confirm_yes(DocNo, From) {
			sendmail = 0;
			Arr_ItemCode = [];
			Arr_ItemName = [];
			Arr_Qty = [];
			$('.receive_item').each(function(index) {
				var ItemCode = $(this).attr("data-itemcode");
				var ItemName = $(this).attr("data-itemname");
				var Qty = Number($(this).val());
				var code_i = rcv_code.indexOf(ItemCode);
				if (code_i >= 0) {
					if (Qty != rcv_qty[code_i]) {
						sendmail++;
					}
				}
				Arr_ItemCode.push(ItemCode);
				Arr_ItemName.push(ItemName);
				Arr_Qty.push(Qty);
			});
			var Str_ItemCode = Arr_ItemCode.join(',');
			var Str_Qty = Arr_Qty.join(',');

			var data = {
				'DocNo': DocNo,
				'From': From,
				'Str_ItemCode': Str_ItemCode,
				'Str_Qty': Str_Qty,
				'STATUS': 'confirm_yes'
			};
			senddata(JSON.stringify(data));
		}

		function change_dep() {
			var slt = $("#DepName").val();
			var sltFac = $("#FacName").val();
			if (sltFac == 0) {
				$("#btn_add_dirty").prop('disabled', true);
			} else {
				$("#btn_add_dirty").prop('disabled', false);
			}
		}

		function add_dirty() {
			var FacCode = $("#FacName").val();
			var data = {
				'Userid': Userid,
				'siteCode': siteCode,
				'FacCode': FacCode,
				'STATUS': 'add_dirty'
			};
			senddata(JSON.stringify(data));
		}

		function make_number() {
			$('.numonly').on('input', function() {
				this.value = this.value.replace(/[^0-9]/g, ''); //<-- replace all other than given set of values\
				this.value = Number(this.value);
			});
		}

		function back() {
			var Menu = '<?php echo $Menu; ?>';
			if (Menu == "factory") {
				window.location.href = "hospital.php?Menu=factory";
			} else {
				window.location.href = "menu.php";
			}
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/dirty.php';
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
						if (temp["form"] == 'load_dep') {
							for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
								var Str = "<option value=" + temp[i]['DepCode'] + ">" + temp[i]['DepName'] + "</option>";
								$("#DepName").append(Str);
							}

						} else if (temp["form"] == 'load_Fac') {
							for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
								var Str = "<option value=" + temp[i]['FacCode'] + ">" + temp[i]['FacName'] + "</option>";
								$("#FacName").append(Str);
							}

						} else if (temp["form"] == 'load_site') {
							$("#HptName").text(temp['HptName']);
						} else if (temp["form"] == 'load_doc') {

							$(".btn.btn-mylight.btn-block").remove();
							for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
								var status_class = "";
								var status_text = "";
								var status_line = "";
								var Menu = '<?php echo $Menu; ?>';

								if (Menu == 'dirty') {
									if (temp[i]['IsStatus'] == 0) {
										status_class = "status4";
										status_text = "<?php echo $genarray['statusOnCreate'][$language]; ?>";
										status_line = "StatusLine_4";
									} else if (temp[i]['IsStatus'] == 1) {
										status_class = "status1";
										status_text = "<?php echo $genarray['statusCretFin'][$language]; ?>";
										status_line = "StatusLine_1";
									} else if (temp[i]['IsStatus'] == 2) {
										status_class = "status2";
										status_text = "<?php echo $genarray['statusOnWork'][$language]; ?>";
										status_line = "StatusLine_2";
									} else if (temp[i]['IsStatus'] >= 3) {
										status_class = "status3";
										status_text = "<?php echo $genarray['statusfin'][$language]; ?>";
										status_line = "StatusLine_3";
									}
									var dep = "<div class='my-col-7 text-left'>";
									dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div>";
									dep += "<div class='font-weight-light align-self-center'>" + temp[i]['DepName'] + "</div></div></div></button>";
									if (temp[i]['DepName'] == null) {
										dep = "<div class='my-col-7 text-left d-flex'>";
										dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div></div></div></button>";
									}
									var Str = "<button onclick='show_process(\"" + temp[i]['DocNo'] + "\",0)' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
									Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
									Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div>" + dep;

									$("#document").append(Str);

								} else if (Menu == 'factory') {
									var IsPrcess = temp[i]['IsProcess'];
									if (IsPrcess == 0 || IsPrcess == null) {
										status_class = "status4";
										status_text = "<?php echo $genarray['statusNotWork'][$language]; ?>";
										status_line = "StatusLine_4";
									} else if (IsPrcess == 7) {
										status_class = "status3";
										status_text = "<?php echo $genarray['statusfin'][$language]; ?>";
										status_line = "StatusLine_3";
									} else {
										status_class = "status2";
										status_text = "<?php echo $genarray['statusOnWork'][$language]; ?>";
										status_line = "StatusLine_2";
									}

									var onclick = "show_process(\"" + temp[i]['DocNo'] + "\",\"" + temp[i]['From'] + "\")";
									if (temp[i]['IsReceive'] == 0) {
										onclick = "receive_zero(\"" + temp[i]['DocNo'] + "\",\"" + temp[i]['From'] + "\")";
									}

									if (temp[i]['IsStatus'] > 0) {
										var dep = "<div class='my-col-7 text-left'>";
										dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div>";
										dep += "<div class='font-weight-light align-self-center'>" + temp[i]['DepName'] + "</div></div></div></button>";
										if (temp[i]['DepName'] == null) {
											dep = "<div class='my-col-7 text-left d-flex'>";
											dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div></div></div></button>";
										}
										var Str = "<button onclick='" + onclick + "' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
										Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
										Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div>" + dep;

										$("#document").append(Str);
									}
								}
							}
						} else if (temp["form"] == 'receive_zero') {
							$("#show_receive").empty();
							rcv_code = [];
							rcv_qty = [];
							for (var i = 0; i < temp['count']; i++) {
								rcv_code.push(temp[i]['ItemCode']);
								rcv_qty.push(temp[i]['Qty']);
								var Str = "<div class='alert alert-info my-2 p-2'>";
								Str += "<div class='text-center font-weight-bold mb-2'>";
								Str += temp[i]['ItemName'];
								Str += "</div>";
								Str += "<div class='row'>";
								Str += "<div class='col-6 p-0 text-right'>ทั้งหมด <b>" + temp[i]['Qty'] + "</b> ได้รับ</div>";
								Str += "<div class='col-6 p-0 text-left'>";
								Str += "<div class='ml-2' style='width:80px;'>";
								Str += "<input onkeydown='make_number()' type='text' class='form-control text-center receive_item numonly' data-itemname='" + temp[i]['ItemName'] + "' data-itemcode='" + temp[i]['ItemCode'] + "' value='" + temp[i]['Qty'] + "'>";
								Str += "</div>";
								Str += "</div>";
								Str += "</div>";
								Str += "</div>";
								$("#show_receive").append(Str);
							}
							$("#btn_receive").attr("onclick", "confirm_yes(\"" + temp['DocNo'] + "\",\"" + temp['From'] + "\")");
							$("#md_receive").modal("show");
						} else if (temp["form"] == 'confirm_yes') {
							if (sendmail == 0) {
								show_process(temp['DocNo'], temp['From']);
							} else {
								swal({
									title: 'Please wait...',
									text: 'Processing',
									allowOutsideClick: false
								})
								swal.showLoading()
								$.ajax({
									url: '../process/sendmail_receive.php',
									method: "POST",
									data: {
										DocNo: temp['DocNo'],
										Arr_ItemName: Arr_ItemName,
										Receive_Qty: Arr_Qty,
										Total_Qty: rcv_qty,
										siteCode: '<?php echo $siteCode; ?>'
									},
									success: function(data) {
										swal.close();
										show_process(temp['DocNo'], temp['From']);
									}
								});
								
							}
						} else if (temp["form"] == 'add_dirty') {
							var Userid = temp['user']
							var siteCode = temp['siteCode']
							var DocNo = temp['DocNo']
							var Menu = '<?php echo $Menu; ?>';
							window.location.href = 'add_items_dirty.php?siteCode=' + siteCode + '&DocNo=' + DocNo + '&Menu=' + Menu + '&user=' + Userid + '&Delback=1';
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_doc') {
							$(".btn.btn-mylight.btn-block").remove();
							swal({
								title: '',
								text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?>' + $('#datepicker').val(),
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Data found'
							})

						} else {
							swal({
								title: '',
								text: temp['msg'],
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Error!!'
							})
						}
					} else {
						console.log(temp['msg']);
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
				<button onclick='back()' class='head-btn btn-primary'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-primary" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3 pb-4 mb-5">

		<div align="center" style="margin:1rem 0;">
			<div class="mb-3">
				<img src="../img/logo.png" width="156" height="40" />
			</div>
			<div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div>
		</div>
		<div id="HptName" class="text-center text-truncate font-weight-bold my-4" style="font-size:25px;"></div>
		<div id="document">

			<div class="d-flex justify-content-center mb-3">
				<div width="50"><input type="text" id="datepicker" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly></div>
				<button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
			</div>

			<div id="add_doc" class="fixed-bottom d-flex justify-content-center py-2 bg-white">
				<button class="btn btn-create btn-block" type="button" data-toggle="modal" style="max-width:250px;" data-target="#exampleModal">
					<i class="fas fa-plus mr-1"></i><?php echo $genarray['createdocno'][$language]; ?>
				</button>
			</div>

		</div>

	</div>

	<!-- Modal -->
	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['confirmCreatedocno'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<!-- <?php echo $genarray['chooseDepartment'][$language] . $array['CreateDirtyLinenDoc'][$language]; ?> -->
					<div class="input-group my-3" hidden>
						<div class="input-group-prepend">
							<label class="input-group-text" for="inputGroupSelect01"><?php echo $genarray['chooseDep'][$language]; ?></label>
						</div>
						<select onchange="change_dep()" id="DepName" class="custom-select">
							<option value="0" selected><?php echo $genarray['chooseDepartmentPl'][$language]; ?></option>
						</select>
					</div>
					<div class="input-group my-3">
						<div class="input-group-prepend">
							<label class="input-group-text" for="inputGroupSelect01"><?php echo $array['chooseFactory'][$language]; ?></label>
						</div>
						<select onchange="change_dep()" id="FacName" class="custom-select">
							<option value="0" selected><?php echo $array['chooseFactoryPl'][$language]; ?></option>
						</select>
					</div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button id="btn_add_dirty" onclick="add_dirty()" type="button" class="btn btn-primary m-2" style="font-size: 20px;" disabled><?php echo $genarray['confirm'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal" style="font-size: 20px;"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_receive" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<div class="font-weight-bold">ยืนยันการรับเอกสาร</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					โปรดระบุจำนวนที่ได้รับ
					<div id="show_receive">

						<!-- <div class="alert alert-info my-2 p-2">
							<div class="text-center font-weight-bold mb-2">
								กางเกงผู้ป่วย (ทหารผ่านศึก) แบบกางเกงจีนเอวยางยืดขาจั๊ม FREE SIZE
							</div>
							<div class="row">
								<div class="col-6 p-0 text-right">ทั้งหมด <b>200000</b> ได้รับ</div>
								<div class="col-6 p-0 text-left">
									<div class="ml-2" style="width:80px;">
										<input type="text" class="form-control text-center">
									</div>
								</div>
							</div>
						</div> -->

					</div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button id="btn_receive" type="button" class="btn btn-primary m-2" style="font-size: 20px;"><?php echo $genarray['confirm'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal" style="font-size: 20px;"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

</html>