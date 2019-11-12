<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$ShowSign = "";
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$Menu = $_GET['Menu'];
$siteCode = $_GET['siteCode'];
$DocNo = $_GET['DocNo'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/fac_process_lang.xml');
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
	<title><?php echo $genarray['titlefactory'][$language] . $array['title'][$language]; ?></title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>
	<link rel="stylesheet" href="../css/signature-pad2.css">
	<script>
		var Menu = '<?php echo $Menu; ?>';
		var siteCode = '<?php echo $siteCode; ?>';
		var DocNo = "<?php echo $DocNo ?>";
		var From = "<?php echo $From ?>";
		var SignFnc = "";
		
		$(document).ready(function(e) {
			load_process();
			$('#ModalSign').on('shown.bs.modal', function () {
				resizeCanvas();
				signaturePad.clear();
			});
		});

		// function
		function load_process() {
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'STATUS': 'load_process'
			};
			senddata(JSON.stringify(data));
		}

		function insert_process() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'insert_process'
			};
			senddata(JSON.stringify(data));
		}

		function start_wash(DocNo) {
			var From = "<?php echo $From ?>";
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'start_wash'
			};
			senddata(JSON.stringify(data));
		}

		function end_wash(DocNo) {
			var From = "<?php echo $From ?>";
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'end_wash'
			};
			senddata(JSON.stringify(data));
		}

		function start_pack(DocNo) {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'start_pack'
			};
			senddata(JSON.stringify(data));
		}

		function end_pack(DocNo) {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'end_pack'
			};
			senddata(JSON.stringify(data));
		}

		function md_send(fnc) {
			SignFnc = fnc;
			$("#ModalSign").modal('show');
		}

		function save_sign(dataURL) {
			$("#ModalSign").modal('hide');
			$.ajax({
				url: "../process/signature_sc.php",
				method: "POST",
				data: {
					DocNo: DocNo,
					SignCode: dataURL,
					SignFnc: SignFnc
				},
				success: function(data) {
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
						load_process();
					})
				}
			});
		}

		function start_send() {
			window.location.href = 'signature.php?Menu=' + Menu + '&DocNo=' + DocNo + '&siteCode=' + siteCode + '&fnc=start_send';
			// var data = {
			// 	'DocNo': DocNo,
			// 	'From': From,
			// 	'STATUS': 'start_send'
			// };
			// senddata(JSON.stringify(data));
		}

		function end_send() {
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'end_send'
			};
			senddata(JSON.stringify(data));
		}

		function view_detail() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'view_detail'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			var Menu = '<?php echo $Menu; ?>';
			var site = '<?php echo $siteCode; ?>';
			window.location.href = 'shelfcount.php?siteCode=' + site + '&Menu=' + Menu;
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/shelf_process.php';
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
						if (temp["form"] == 'load_process') {
							$("#back_div").remove();
							var Back = "<div id='back_div' style='width:139.14px;'><button onclick='back(\"" + temp['HptCode'] + "\")' class='head-btn btn-primary'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button></div>";
							$("#user").before(Back);
							if (temp['IsStatus'] == 0 || temp['IsStatus'] == null) { //-----ยังไม่ได้ทำอะไร
								$("#W_Status").attr("src", "../img/Status_4.png");
								$("#P_Status").attr("src", "../img/Status_4.png");
								$("#S_Status").attr("src", "../img/Status_4.png");
								$("#W_Status_text").text("No Process");
								$("#P_Status_text").text("No Process");
								$("#S_Status_text").text("No Process");
								$("#W_Use_text").hide();
								$("#P_Use_text").hide();
								$("#S_Use_text").hide();
								$("#W_Sum_btn").show();
								$("#P_Sum_btn").hide();
								$("#S_Sum_btn").hide();
								$("#W_Start").text("--:--:--");
								$("#W_End").text("--:--:--");
								$("#P_Start").text("--:--:--");
								$("#P_End").text("--:--:--");
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");

								$("#W_Start_btn").show();
								$("#W_End_btn").hide();
							} else if (temp['IsStatus'] == 99) { //-----กำลังซัก
								$("#W_Status").attr("src", "../img/Status_1.png");
								$("#P_Status").attr("src", "../img/Status_4.png");
								$("#S_Status").attr("src", "../img/Status_4.png");
								$("#W_Status_text").text("Wait Process");
								$("#P_Status_text").text("No Process");
								$("#S_Status_text").text("No Process");
								$("#W_Use_text").hide();
								$("#P_Use_text").hide();
								$("#S_Use_text").hide();
								$("#W_Sum_btn").show();
								$("#P_Sum_btn").hide();
								$("#S_Sum_btn").hide();
								$("#W_End").text("--:--:--");
								$("#P_Start").text("--:--:--");
								$("#P_End").text("--:--:--");
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");

								var W_Start = new Date(temp['WashStartTime']);
								$("#W_Start").text(W_Start.toLocaleTimeString());

								$("#W_Start_btn").remove();
								$("#W_End_btn").show();

							} else if (temp['IsStatus'] == 1 || temp['IsStatus'] == 2) { //-----กำลังแพคของ
								$("#W_Status").attr("src", "../img/Status_3.png");
								$("#P_Status").attr("src", "../img/Status_1.png");
								$("#S_Status").attr("src", "../img/Status_4.png");
								$("#W_Status_text").text("Success Process");
								$("#P_Status_text").text("Wait Process");
								$("#S_Status_text").text("No Process");
								$("#W_Start_text").removeClass("col-lg-6");
								$("#W_End_text").removeClass("col-lg-6");
								$("#W_Start_text").addClass("col-lg-4");
								$("#W_End_text").addClass("col-lg-4");
								$("#P_Use_text").hide();
								$("#S_Use_text").hide();
								$("#W_Sum_btn").remove();
								$("#P_Sum_btn").show();
								$("#S_Sum_btn").hide();
								$("#W_Use_text").show();
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");

								var W_Start = new Date(temp['ScStartTime']);
								var W_End = new Date(temp['ScEndTime']);

								$("#W_Use").text(temp['ScUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());

								if (temp['PkStartTime'] == null) { // ถ้ากดเริ่มครั้งแรก
									$("#P_Status").attr("src", "../img/Status_4.png");
									$("#P_Start_btn").show();
									$("#P_End_btn").hide();
									$("#P_Start").text("--:--:--");
									$("#P_End").text("--:--:--");
								} else if (temp['PkStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
									$("#P_Start_btn").hide();
									$("#P_End_btn").show();
									var P_Start = new Date(temp['PkStartTime']);
									$("#P_Start").text(P_Start.toLocaleTimeString());
									$("#P_End").text("--:--:--");
								}
							} else if (temp['IsStatus'] == 3 && temp['DvEndTime'] == null) { //-----กำลังขนส่ง
								$("#W_Status").attr("src", "../img/Status_3.png");
								$("#P_Status").attr("src", "../img/Status_3.png");
								$("#S_Status").attr("src", "../img/Status_1.png");
								$("#W_Status_text").text("Success Process");
								$("#P_Status_text").text("Success Process");
								$("#S_Status_text").text("Wait Process");
								$("#W_Start_text").removeClass("col-lg-6");
								$("#W_End_text").removeClass("col-lg-6");
								$("#W_Start_text").addClass("col-lg-4");
								$("#W_End_text").addClass("col-lg-4");
								$("#P_Start_text").removeClass("col-lg-6");
								$("#P_End_text").removeClass("col-lg-6");
								$("#P_Start_text").addClass("col-lg-4");
								$("#P_End_text").addClass("col-lg-4");
								$("#P_Use_text").show();
								$("#S_Use_text").hide();
								$("#W_Sum_btn").remove();
								$("#P_Sum_btn").remove();
								$("#S_Sum_btn").show();
								$("#W_Use_text").show();

								var W_Start = new Date(temp['ScStartTime']);
								var W_End = new Date(temp['ScEndTime']);
								var P_Start = new Date(temp['PkStartTime']);
								var P_End = new Date(temp['PkEndTime']);

								$("#W_Use").text(temp['ScUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#P_Use").text(temp['PkUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());
								$("#P_Start").text(P_Start.toLocaleTimeString());
								$("#P_End").text(P_End.toLocaleTimeString());


								if (temp['DvStartTime'] == null) { // ถ้ากดเริ่มครั้งแรก
									$("#S_Status").attr("src", "../img/Status_4.png");
									$("#S_Start_btn").show();
									$("#S_End_btn").hide();
									$("#S_Start").text("--:--:--");
									$("#S_End").text("--:--:--");
								} else if (temp['DvStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
									$("#S_Start_btn").hide();
									$("#S_End_btn").show();
									var S_Start = new Date(temp['DvStartTime']);
									$("#S_Start").text(S_Start.toLocaleTimeString());
									$("#S_End").text("--:--:--");

									var ck_start = temp['signStart'];
									$("#show_sign_start").html(ck_start);
									$("#sign_zone_start").removeAttr("hidden");
								}
							} else if ((temp['IsStatus'] == 3 && temp['DvEndTime'] != null) || temp['IsStatus'] == 4) { //-----เสร็จสิ้น

								if (temp['Signature'] == null || temp['Signature'] == "") {
									// swal({
									// 	title: "<?php echo $genarray['confirm'][$language]; ?>",
									// 	text: "<?php echo $array['ConfFinShipping'][$language]; ?>",
									// 	type: "warning",
									// 	showCancelButton: false,
									// 	confirmButtonClass: "btn-success",
									// 	cancelButtonClass: "btn-danger",
									// 	confirmButtonText: "<?php echo $genarray['yes2'][$language]; ?>",
									// 	cancelButtonText: "<?php echo $genarray['isno'][$language]; ?>",
									// 	closeOnConfirm: true,
									// 	closeOnCancel: true,
									// }).then(result => {
									// 	window.location.href = 'signature.php?Menu=' + Menu + '&DocNo=' + temp['DocNo'] + '&siteCode=' + temp['HptCode'];
									// })
									var ck_start = temp['signStart'];

									$("#show_sign_start").html(ck_start);
									$("#sign_zone_start").removeAttr("hidden");
									md_send('end_send');
									
								} else {
									var ck = temp['Signature'];
									var ck_start = temp['signStart'];

									$("#show_sign_start").html(ck_start);
									$("#sign_zone_start").removeAttr("hidden");

									$("#show_sign").html(ck);
									$("#sign_zone").removeAttr("hidden");
								}

								$("#W_Sum_btn").remove();
								$("#P_Sum_btn").remove();
								$("#S_Sum_btn").remove();
								$("#W_Status").attr("src", "../img/Status_3.png");
								$("#P_Status").attr("src", "../img/Status_3.png");
								$("#S_Status").attr("src", "../img/Status_3.png");
								$("#W_Status_text").text("Success Process");
								$("#P_Status_text").text("Success Process");
								$("#S_Status_text").text("Success Process");
								$("#W_Start_text").removeClass("col-lg-6");
								$("#W_End_text").removeClass("col-lg-6");
								$("#W_Start_text").addClass("col-lg-4");
								$("#W_End_text").addClass("col-lg-4");
								$("#P_Start_text").removeClass("col-lg-6");
								$("#P_End_text").removeClass("col-lg-6");
								$("#P_Start_text").addClass("col-lg-4");
								$("#P_End_text").addClass("col-lg-4");
								$("#S_Start_text").removeClass("col-lg-6");
								$("#S_End_text").removeClass("col-lg-6");
								$("#S_Start_text").addClass("col-lg-4");
								$("#S_End_text").addClass("col-lg-4");
								$("#W_Use_text").show();
								$("#P_Use_text").show();
								$("#S_Use_text").show();

								var W_Start = new Date(temp['ScStartTime']);
								var W_End = new Date(temp['ScEndTime']);
								var P_Start = new Date(temp['PkStartTime']);
								var P_End = new Date(temp['PkEndTime']);
								var S_Start = new Date(temp['DvStartTime']);
								var S_End = new Date(temp['DvEndTime']);
								var S_Over = temp['DvUseTime'].substring(0, 1);

								if (S_Over == '-') {
									$("#S_Head_use").text("<?php echo $genarray['overTime'][$language]; ?>");
									$("#S_Head_use").css("color", "red");
									$("#S_Use").css("color", "red");
									$("#S_Use").text(temp['DvUseTime'].substring(1) + " <?php echo $genarray['minute'][$language]; ?>");

								} else {
									$("#S_Head_use").text("<?php echo $array['useTime'][$language]; ?>");
									$("#S_Use").text(temp['DvUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								}

								$("#W_Use").text(temp['ScUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#P_Use").text(temp['PkUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());
								$("#P_Start").text(P_Start.toLocaleTimeString());
								$("#P_End").text(P_End.toLocaleTimeString());
								$("#S_Start").text(S_Start.toLocaleTimeString());
								$("#S_End").text(S_End.toLocaleTimeString());

							}
						} else if (temp["form"] == 'insert_process') {
							load_process();
						} else if (temp["form"] == 'start_wash') {
							load_process();
						} else if (temp["form"] == 'stop_wash') {
							load_process();
						} else if (temp["form"] == 'end_wash') {
							load_process();
						} else if (temp["form"] == 'start_pack') {
							load_process();
						} else if (temp["form"] == 'end_pack') {
							load_process();
						} else if (temp["form"] == 'start_send') {
							load_process();
						} else if (temp["form"] == 'end_send') {
							load_process();
						} else if (temp["form"] == 'view_detail') {
							$("#lg_body").empty();

							var Str = "<table class='table table-bordered table-sm'>";
							Str += "			<thead>";
							Str += "				<tr>";
							Str += "					<th><?php echo $genarray['no'][$language]; ?></th>";
							Str += "					<th><?php echo $genarray['item'][$language]; ?></th>";
							Str += "					<th><?php echo $genarray['issue'][$language]; ?></th>";
							Str += "				</tr>";
							Str += "			</thead>";
							Str += "			<tbody>";

							for (var i = 0; i < temp['cnt']; i++) {
								Str += "					<tr>";
								Str += "						<th>" + Number(i+1) + "</th>";
								Str += "						<td class='text-left pl-3'>" + temp['ItemName'][i] + "</td>";
								Str += "						<td>" + temp['TotalQty'][i] + "</td>";
								Str += "					</tr>";
							}

							Str += "				</tbody>";
							Str += "			</table>";

							$("#lg_body").append(Str);
							$("#md_lg").modal('show');

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_process') {
							insert_process();
						} else if (temp["form"] == 'insert_process') {
							swal({
								title: '',
								text: '<?php echo $genarray['errorToAddData'][$language]; ?>',
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Error!!'
							})
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
			<div id="user" class="head-text font-weight-bold text-truncate align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-primary" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3">

		<div align="center" style="margin:1rem 0;">
			<div class="mb-3">
				<img src="../img/logo.png" width="156" height="60" />
			</div>
			<!-- <div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div> -->
		</div>
		<div class="text-center text-truncate font-weight-bold my-4" style="font-size:25px;"><?php echo $DocNo; ?></div>

		<div id="process">
			<!-- <div class="card alert alert-info mx-3 mt-3" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_1.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><?php echo $array['Wash'][$language]; ?></div>
						</div>
					</div>

					<div class="col-4 text-left align-self-center text-center">
						<div class="row">
							<div id="W_Start_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
								<label id="W_Start" class='font-weight-light'></label>
							</div>
							<div id="W_End_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
								<label id="W_End" class='font-weight-light'></label>
							</div>
							<div id="W_Use_text" class="col-lg-4 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Processtime'][$language]; ?></div>
								<label id="W_Use" class='font-weight-light'></label>
							</div>
						</div>
					</div>

					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-12 text-center"><img id="W_Status" height="40px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div id="W_Status_text" class="col-md-6 col-sm-12 text-center"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
				</div>
				<div id="W_Sum_btn" class="row mt-4">
					<div class="col-md-2 col-sm-none"></div>
					<div class="col-md-8 col-sm-12" id="W_Start_btn"><button id="W_Start_btn_sub" onclick="start_wash('<?php echo $DocNo; ?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['StartWash'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="W_End_btn"><button id="W_End_btn_sub" onclick="end_wash('<?php echo $DocNo; ?>')" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
					<div class="col-md-2 col-sm-none"></div>
				</div>
			</div>

			<div class="card alert alert-info mx-3 mt-4" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_2.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><?php echo $array['pack'][$language]; ?></div>
						</div>
					</div>

					<div class="col-4 text-left align-self-center text-center">
						<div class="row">
							<div id="P_Start_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
								<label id="P_Start" class='font-weight-light'></label>
							</div>
							<div id="P_End_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
								<label id="P_End" class='font-weight-light'></label>
							</div>
							<div id="P_Use_text" class="col-lg-4 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Processtime'][$language]; ?></div>
								<label id="P_Use" class='font-weight-light'></label>
							</div>
						</div>
					</div>

					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-12 text-center"><img id="P_Status" height="40px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div id="P_Status_text" class="col-md-6 col-sm-12 text-center"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
				</div>
				<div id="P_Sum_btn" class="row mt-4">
					<div class="col-md-2 col-sm-none"></div>
					<div class="col-md-8 col-sm-12" id="P_Start_btn"><button onclick="start_pack('<?php echo $DocNo; ?>')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Startpack'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="P_End_btn"><button onclick="end_pack('<?php echo $DocNo; ?>')" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
					<div class="col-md-2 col-sm-none"></div>

				</div>
			</div> -->

			<div class="card alert alert-info mx-3 mt-4" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_3.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><?php echo $array['shipping'][$language]; ?></div>
						</div>
					</div>

					<div class="col-4 text-left align-self-center text-center">
						<div class="row">
							<div id="S_Start_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Starttime'][$language]; ?></div>
								<label id="S_Start" class='font-weight-light'></label>
							</div>
							<div id="S_End_text" class="col-lg-6 col-md-12 col-sm-12">
								<div class="head_text"><?php echo $array['Finishtime'][$language]; ?></div>
								<label id="S_End" class='font-weight-light'></label>
							</div>
							<div id="S_Use_text" class="col-lg-4 col-md-12 col-sm-12">
								<div id="S_Head_use" class="head_text"></div>
								<label id="S_Use" class='font-weight-light'></label>
							</div>
						</div>
					</div>

					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-12 text-center"><img id="S_Status" height="40px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div id="S_Status_text" class="col-md-6 col-sm-12 text-center"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
				</div>
				<div id="S_Sum_btn" class="row mt-4">
					<div class="col-md-2 col-sm-none"></div>
					<div class="col-md-8 col-sm-12" id="S_Start_btn"><button onclick="md_send('start_send')" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Startshipping'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="S_End_btn"><button onclick="end_send()" type="button" class="btn btn-lg btn-success btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
					<div class="col-md-2 col-sm-none"></div>

				</div>
			</div>

			<div id="sign_zone_start" class="mx-3 mb-3 text-center" hidden>
				<div class="col-md-8 col-sm-12 mx-auto my-4">
					<button onclick="view_detail()" class="btn btn-block btn-info"><?php echo $genarray['detail'][$language]; ?></button>
				</div>
				
				<div class="text-center">
					<div><b>ลายเซนต์ผู้ส่ง</b></div>
					<div class="row justify-content-center">
						<div class="card mb-2 p-2">
							<div id="show_sign_start"></div>
						</div>
					</div>
				</div>
			</div>

			<div id="sign_zone" class="mx-3 mb-3" hidden>
				<div class="text-center">
					<div><b>ลายเซนต์ผู้รับ</b></div>
					<div class="row justify-content-center">
						<div class="card mb-2 p-2">
							<div id="show_sign"></div>
						</div>
					</div>
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

	<div class="modal fade" id="md_lg" tabindex="-1" role="dialog" aria-hidden='false'>
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div id="lg_body" class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">

				

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