<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$HptCode = $_SESSION['HptCode'];
$PM = $_SESSION['PmID'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/menu_lang.xml');
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

	<title><?php echo $array['title'][$language]; ?></title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>
	<script src="../js/jsQR.js"></script>
	<script>
		// var getQR = "BHQLPNONO010006,12";
		var itemCode;
		var itemQty;
		$(document).ready(function(e) {
			// load_QRcode(getQR);
		});

		// function
		function load_QRcode(getQR) {
			var Arr_QR = getQR.split(",");
			itemCode = Arr_QR[0];
			itemQty = Arr_QR[1];

			var data = {
				'itemCode': itemCode,
				'STATUS': 'load_QRcode'
			};
			senddata(JSON.stringify(data));
		}

		function clear_text() {
			itemCode = "";
			itemQty = "";
			$("#item_code").val("");
			$("#item_cate_main").val("");
			$("#item_cate_sub").val("");
			$("#item_name").val("");
			$("#item_qty").val("");
			$("#item_size").val("");
		}

		function back() {
			window.location.href = 'menu.php';
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/read_QRcode.php';
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
						if (temp["form"] == 'load_QRcode') {
							$("#item_code").val(itemCode);
							$("#item_name").val(temp['ItemName']);
							$("#item_cate_main").val(temp['CateMain']);
							$("#item_cate_sub").val(temp['CateSub']);
							$("#item_qty").val(itemQty);
							$("#item_size").val(temp['SizeName']);

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						swal({
							title: '',
							text: '',
							type: 'warning',
							showCancelButton: false,
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
							showConfirmButton: false,
							timer: 2000,
							confirmButtonText: 'Error!!'
						})
					} else {
						console.log(temp['msg']);
					}
				}
			});
		}
		// end display
	</script>
	<style>
		#githubLink {
			position: absolute;
			right: 0;
			top: 12px;
			color: #2D99FF;
		}

		h1 {
			margin: 10px 0;
			font-size: 40px;
		}

		#loadingMessage {
			text-align: center;
			padding: 40px;
			background-color: #eee;
		}

		#canvas {
			width: 100%;
		}

		#output {
			margin-top: 20px;
			background: #eee;
			padding: 10px;
			padding-bottom: 0;
		}

		#output div {
			padding-bottom: 10px;
			word-wrap: break-word;
		}

		#noQRFound {
			text-align: center;
		}
	</style>
</head>

<body>
	<section data-role="page">

		<header data-role="header">
			<div class="head-bar d-flex justify-content-between">
				<div style="width:139.14px;">
					<button onclick="back()" class="head-btn btn-primary"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
				</div>
				<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
				<div class="text-right" style="width:139.14px;">
					<button onclick="logout(1)" class="head-btn btn-primary" role="button">
						<?php echo $genarray['logout'][$language]; ?>
						<i class="fas fa-power-off ml-1"></i></button>
				</div>
			</div>
		</header>
		<div data-role="content">
			<div class="text-center" style="margin:1rem 0;">
				<div class="mb-3">
					<img src="../img/logo.png" width="156" height="40" />
				</div>
				<div>
					<img src="../img/nlinen.png" width="95" height="14" />
				</div>
			</div>
		</div>

		<div id="show_detail" class="row d-flex justify-content-center pt-3 m-0">
			<div class="col-xl-4 col-lg-5 col-md-6 col-sm-9 col-12 text-center px-3">

				<div id="loadingMessage">🎥 Unable to access video stream (please make sure you have a webcam enabled)</div>
				<canvas id="canvas" hidden></canvas>
				<!-- <div id="output" hidden>
					<div id="outputMessage">No QR code detected.</div>
					<div hidden><b>Data:</b> <span id="outputData"></span></div>
				</div> -->

				<div class="input-group my-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">รหัส</span>
					</div>
					<input type="text" id="item_code" class="form-control bg-white" disabled>
				</div>

				<div class="input-group my-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">หมวดหมู่หลัก</span>
					</div>
					<input type="text" id="item_cate_main" class="form-control bg-white" disabled>
				</div>

				<div class="input-group my-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">หมวดหมู่รอง</span>
					</div>
					<input type="text" id="item_cate_sub" class="form-control bg-white" disabled>
				</div>

				<div class="input-group my-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">รายการ</span>
					</div>
					<input type="text" id="item_name" class="form-control bg-white" disabled>
				</div>

				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">จำนวน</span>
					</div>
					<input type="text" id="item_qty" class="form-control bg-white" disabled>
					<div class="input-group-append">
						<span class="input-group-text">ชิ้น</span>
					</div>
				</div>

				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">ขนาด</span>
					</div>
					<input type="text" id="item_size" class="form-control bg-white" disabled>
				</div>

				<button onclick="clear_text()" class="btn btn-secondary mb-3"><i class="fas fa-undo-alt mr-2"></i>ล้างข้อมูล</button>
			</div>
		</div>

	</section>
	<script>
		var video = document.createElement("video");
		var canvasElement = document.getElementById("canvas");
		var canvas = canvasElement.getContext("2d");
		var loadingMessage = document.getElementById("loadingMessage");
		// var outputContainer = document.getElementById("output");
		// var outputMessage = document.getElementById("outputMessage");
		// var outputData = document.getElementById("outputData");

		function drawLine(begin, end, color) {
			canvas.beginPath();
			canvas.moveTo(begin.x, begin.y);
			canvas.lineTo(end.x, end.y);
			canvas.lineWidth = 4;
			canvas.strokeStyle = color;
			canvas.stroke();
		}

		// Use facingMode: environment to attemt to get the front camera on phones
		navigator.mediaDevices.getUserMedia({
			video: {
				facingMode: "environment"
			}
		}).then(function(stream) {
			video.srcObject = stream;
			video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
			video.play();
			requestAnimationFrame(tick);
		});

		function tick() {
			loadingMessage.innerText = "⌛ Loading video..."
			if (video.readyState === video.HAVE_ENOUGH_DATA) {
				loadingMessage.hidden = true;
				canvasElement.hidden = false;
				// outputContainer.hidden = false;

				canvasElement.height = video.videoHeight;
				canvasElement.width = video.videoWidth;
				canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
				var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
				var code = jsQR(imageData.data, imageData.width, imageData.height, {
					inversionAttempts: "dontInvert",
				});
				if (code) { // แสกนไม่เห็น(ซ่อน)
					drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
					drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
					drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
					drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
					// outputMessage.hidden = true;
					// outputData.parentElement.hidden = false;
					// outputData.innerText = code.data;
					var text = code.data;
					load_QRcode(code.data);
				} else { // แสกนเห็น(แสดง)
					// outputMessage.hidden = false;
					// outputData.parentElement.hidden = true;
				}
			}
			requestAnimationFrame(tick);
		}
	</script>
</body>

</html>