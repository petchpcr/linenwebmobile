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
$From = $_GET['From'];
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

	<script>
		var siteCode = "<?php echo $siteCode ?>";
		var Menu = '<?php echo $Menu; ?>';
		var From = "<?php echo $From ?>";
		var DocNo = "<?php echo $DocNo ?>";

		$(document).ready(function(e) {
			load_process();
		});

		// function
		function load_process() {
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'From': From,
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

		function start_wash() {
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'start_wash'
			};
			senddata(JSON.stringify(data));
		}

		function question_end_wash() {
			$("#save_question").attr("onclick","end_wash()");
			$("#md_question").modal("show");
		}

		function end_wash() {
			var question = $("textarea#ipt_question").val();
			var data = {
				'DocNo': DocNo,
				'From': From,
				'question': question,
				'STATUS': 'end_wash'
			};
			senddata(JSON.stringify(data));
		}

		function start_pack() {
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'start_pack'
			};
			senddata(JSON.stringify(data));
		}

		function question_end_pack() {
			$("#save_question").attr("onclick","end_pack()");
			$("#md_question").modal("show");
		}
		
		function end_pack() {
			var question = $("textarea#ipt_question").val();
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'end_pack'
			};
			senddata(JSON.stringify(data));
		}

		function start_send() {
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'start_send'
			};
			senddata(JSON.stringify(data));
		}

		function question_end_send() {
			$("#save_question").attr("onclick","end_send()");
			$("#md_question").modal("show");
		}

		function end_send() {
			var question = $("textarea#ipt_question").val();
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'end_send'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			window.location.href = 'dirty.php?Menu=' + Menu + '&siteCode=' + siteCode + '&From=' + From;
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/process.php';
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
							$("#send_time").text("( <?php echo $array['useTimeS'][$language]; ?> " + temp['LimitTime'] + " <?php echo $genarray['minute'][$language]; ?> )");
							$("#head-back").remove();
							var Back = "<div id='head-back' style='width:139.14px;'><button onclick='back(\"" + temp['HptCode'] + "\")' class='head-btn btn-light'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button></div>";
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
							} else if (temp['IsStatus'] == 1) { //-----กำลังซัก
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

							} else if (temp['IsStatus'] == 2) { //-----กำลังแพคของ
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

								var W_Start = new Date(temp['WashStartTime']);
								var W_End = new Date(temp['WashEndTime']);

								$("#W_Use").text(temp['WashUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());

								if (temp['PackStartTime'] == null) { // ถ้ากดเริ่มครั้งแรก
									$("#P_Start_btn").show();
									$("#P_End_btn").hide();
									$("#P_Start").text("--:--:--");
									$("#P_End").text("--:--:--");
								} else if (temp['PackStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
									$("#P_Start_btn").hide();
									$("#P_End_btn").show();
									var P_Start = new Date(temp['PackStartTime']);
									$("#P_Start").text(P_Start.toLocaleTimeString());
									$("#P_End").text("--:--:--");
								}
							} else if (temp['IsStatus'] == 3) { //-----กำลังขนส่ง
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

								var W_Start = new Date(temp['WashStartTime']);
								var W_End = new Date(temp['WashEndTime']);
								var P_Start = new Date(temp['PackStartTime']);
								var P_End = new Date(temp['PackEndTime']);

								$("#W_Use").text(temp['WashUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#P_Use").text(temp['PackUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());
								$("#P_Start").text(P_Start.toLocaleTimeString());
								$("#P_End").text(P_End.toLocaleTimeString());


								if (temp['SendStartTime'] == null) { // ถ้ากดเริ่มครั้งแรก
									$("#S_Start_btn").show();
									$("#S_End_btn").hide();
									$("#S_Start").text("--:--:--");
									$("#S_End").text("--:--:--");
								} else if (temp['SendStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
									$("#S_Start_btn").hide();
									$("#S_End_btn").show();
									var S_Start = new Date(temp['SendStartTime']);
									$("#S_Start").text(S_Start.toLocaleTimeString());
									$("#S_End").text("--:--:--");
								}
							} else if (temp['IsStatus'] == 4) { //-----เสร็จสิ้น

								if (temp['Signature'] == null || temp['Signature'] == "") {
									swal({
										title: "<?php echo $genarray['confirm'][$language]; ?>",
										text: "<?php echo $array['ConfFinShipping'][$language]; ?>",
										type: "warning",
										showCancelButton: false,
										confirmButtonClass: "btn-success",
										cancelButtonClass: "btn-danger",
										confirmButtonText: "<?php echo $genarray['yes2'][$language]; ?>",
										cancelButtonText: "<?php echo $genarray['cancel'][$language]; ?>",
										closeOnConfirm: true,
										closeOnCancel: true,
									}).then(result => {
										var siteCode = "<?php echo $siteCode ?>";
										var Menu = "<?php echo $Menu ?>";
										var From = "<?php echo $From ?>";
										window.location.href = 'signature.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + temp['DocNo'] + '&From=' + From;
									})
								} else {
									var ck = temp['Signature'];
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

								var W_Start = new Date(temp['WashStartTime']);
								var W_End = new Date(temp['WashEndTime']);
								var P_Start = new Date(temp['PackStartTime']);
								var P_End = new Date(temp['PackEndTime']);
								var S_Start = new Date(temp['SendStartTime']);
								var S_End = new Date(temp['SendEndTime']);
								var S_Over = temp['SendOverTime'].substring(0, 1);

								if (S_Over == '-') {
									$("#S_Head_use").text("<?php echo $array['overTime'][$language]; ?>");
									$("#S_Head_use").css("color", "red");
									$("#S_Use").css("color", "red");
									$("#S_Use").text(temp['SendOverTime'].substring(1) + " <?php echo $genarray['minute'][$language]; ?>");

								} else {
									$("#S_Head_use").text("<?php echo $array['useTime'][$language]; ?>");
									$("#S_Use").text(temp['SendUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								}

								$("#W_Use").text(temp['WashUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#P_Use").text(temp['PackUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
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
							$("#md_question").modal("hide");
							$("textarea#ipt_question").val("");
							if (temp['question'] == null || temp['question'] == "") {
								load_process();
							}
							else {
								swal({
									title: 'Please wait...',
									text: 'Processing',
									allowOutsideClick: false
								})
								swal.showLoading();

								$.ajax({
									url: "../process/sendmail_wash.php",
									method: "POST",
									data: {
										'DocNo': DocNo,
										'siteCode': siteCode
									},
									success: function(data) {
										console.log(99999);
										swal.close();
										load_process();
									}
								});
							}
							
						} else if (temp["form"] == 'start_pack') {
							load_process();
						} else if (temp["form"] == 'end_pack') {
							$("#md_question").modal("hide");
							$("textarea#ipt_question").val("");
							load_process();
						} else if (temp["form"] == 'start_send') {
							load_process();
						} else if (temp["form"] == 'end_send') {
							$("#md_question").modal("hide");
							$("textarea#ipt_question").val("");
							load_process();
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
			<div id="user" class="head-text font-weight-bold text-truncate align-self-center"><?php echo $UserFName ?> <?php echo "[ ".$Per." ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3">

		<div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="156" height="40" /></div>
		<div class="text-center text-truncate font-weight-bold mt-4" style="font-size:25px;"><?php echo $DocNo; ?></div>
		<div id="send_time" class="text-center text-truncate font-weight-bold mb-4" style="font-size:20px;"></div>

		<div id="process">
			<div class="card alert alert-info mx-3 mt-3" style="padding:1rem;">
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
					<div class="col-md-8 col-sm-12" id="W_Start_btn"><button id="W_Start_btn_sub" onclick="start_wash()" type="button" class="btn btn-lg btn-scondary btn-block"><?php echo $array['StartWash'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="W_End_btn"><button id="W_End_btn_sub" onclick="question_end_wash()" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
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
					<div class="col-md-8 col-sm-12" id="P_Start_btn"><button onclick="start_pack()" type="button" class="btn btn-lg btn-secondary btn-block"><?php echo $array['Startpack'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="P_End_btn"><button onclick="end_pack()" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
					<div class="col-md-2 col-sm-none"></div>

				</div>
			</div>

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
					<div class="col-md-8 col-sm-12" id="S_Start_btn"><button onclick="start_send()" type="button" class="btn btn-lg btn-secondary btn-block"><?php echo $array['Startshipping'][$language]; ?></button></div>
					<div class="col-md-8 col-sm-12" id="S_End_btn"><button onclick="end_send()" type="button" class="btn btn-lg btn-primary btn-block"><?php echo $array['Finish'][$language]; ?></button></div>
					<div class="col-md-2 col-sm-none"></div>

				</div>
			</div>

			<div id="sign_zone" class="mx-3" hidden>
				<div class="text-center">
					<div class="row justify-content-center">
						<div class="card my-2 p-2">
							<div id="show_sign"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

  <!-- Modal -->
	<div class="modal fade" id="md_question" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<div class="font-weight-bold">คำถาม</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<div class="mb-2">โปรดกรอกรายละเอียดหากมีปัญหาเกิดขึ้น</div>
					<textarea id="ipt_question" cols="20" rows="10" class="form-control"></textarea>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button id="save_question" type="button" class="btn btn-primary m-2"><?php echo $genarray['confirm'][$language]; ?></button>
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