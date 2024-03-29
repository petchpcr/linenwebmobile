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
$form_out = $_GET['form_out'];
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
		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			load_process();
		});

		var log_out = 0;

		// function
		function load_process() {
			if (log_out == 0) {
				var DocNo = "<?php echo $DocNo ?>";
				var From = "<?php echo $From ?>";
				var data = {
					'DocNo': DocNo,
					'From': From,
					'STATUS': 'load_process'
				};
				senddata(JSON.stringify(data));
			}
		}

		var x = setInterval(function() {
			load_process();
		}, 1000);

		function back() {
			window.location.href = "dirty_to_track.php?siteCode=<?php echo $siteCode ?>&Menu=track" + txt_form_out;
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
							$("#head-back").remove();
							var Back = "<div id='head-back' style='width:139.14px;'><button onclick='back(\"" + temp['HptCode'] + "\")' class='head-btn btn-primary'><i class='fas fa-arrow-circle-left mr-1'></i><?php echo $genarray['back'][$language]; ?></button></div>";
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

								$("#W_Start").text("--:--:--");
								$("#W_End").text("--:--:--");
								$("#P_Start").text("--:--:--");
								$("#P_End").text("--:--:--");
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");
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
								$("#W_End").text("--:--:--");
								$("#P_Start").text("--:--:--");
								$("#P_End").text("--:--:--");
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");

								var W_Start = new Date(temp['WashStartTime']);
								$("#W_Start").text(W_Start.toLocaleTimeString());

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
								$("#W_Use_text").show();
								$("#S_Start").text("--:--:--");
								$("#S_End").text("--:--:--");

								var W_Start = new Date(temp['WashStartTime']);
								var W_End = new Date(temp['WashEndTime']);

								$("#W_Use").text(temp['WashUseTime'] + " <?php echo $genarray['minute'][$language]; ?>");
								$("#W_Start").text(W_Start.toLocaleTimeString());
								$("#W_End").text(W_End.toLocaleTimeString());

								if (temp['PackStartTime'] == null) { // ถ้ากดเริ่มครั้งแรก
									$("#P_Start").text("--:--:--");
									$("#P_End").text("--:--:--");
								} else if (temp['PackStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
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
									$("#S_Start").text("--:--:--");
									$("#S_End").text("--:--:--");
								} else if (temp['SendStartTime'] != null) { // ถ้าเคยกดเริ่มแล้ว
									var S_Start = new Date(temp['SendStartTime']);
									$("#S_Start").text(S_Start.toLocaleTimeString());
									$("#S_End").text("--:--:--");
								}
							} else if (temp['IsStatus'] == 4) { //-----เสร็จสิ้น

								var ck = temp['Signature'];
								$("#show_sign").html(ck);
								$("#sign_zone").removeAttr("hidden");

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
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {}
				}
			});
		}
		// end display
	</script>
</head>

<body>

	<header data-role="header">
		<div class="head-bar d-flex justify-content-between">
			<div id="user" class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
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
		<div class="text-center my-4">
			<h4 class="text-truncate"><?php echo $DocNo; ?></h4>
		</div>

		<div id="process">
			<div class="card alert alert-info mt-3" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_1.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['Wash'][$language]; ?></div>
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
							<div id="W_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="card alert alert-info mt-4" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_2.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['pack'][$language]; ?></div>
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
							<div id="P_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="card alert alert-info mt-4" style="padding:1rem;">
				<div class="row">
					<div class="col-4 align-self-center">
						<div class="row">
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center"><img src="../img/icon_3.png" height="90px" /></div>
							<div class="col-md-6 col-sm-none"></div>
							<div class="col-md-6 col-sm-12 text-center font-weight-light"><?php echo $array['shipping'][$language]; ?></div>
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
							<div id="S_Status_text" class="col-md-6 col-sm-12 text-center font-weight-light"></div>
							<div class="col-md-6 col-sm-none"></div>
						</div>
					</div>
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

</body>

</html>