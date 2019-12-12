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
$form_out = $_GET['form_out'];
$siteCode = $_GET['siteCode'];
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
	<script src="../js/vue.min.js"></script>
	<script src="../js/vue-qrcode-reader.browser.js"></script>
	<link rel="stylesheet" href="../css/vue-qrcode-reader.css">
	<script>
		var siteCode = '<?php echo $siteCode ?>';
		var itemCode;
		var itemQty;

		var form_out = '<?php echo $form_out ?>';
		var txt_form_out = "";
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		}

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
			if (form_out == 1) {
				window.location.href = "menu.php?siteCode=" + siteCode + txt_form_out;
			} else {
				window.location.href = "menu.php";
			}
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
					} else if (temp['status'] == "failed_QRcode") {
						clear_text();
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
					<img src="../img/logo.png" width="156" height="60" />
				</div>
				<!-- <div>
					<img src="../img/nlinen.png" width="95" height="14" />
				</div> -->
			</div>
		</div>

		<div id="show_detail" class="row d-flex justify-content-center pt-3 m-0">
			<div class="col-xl-4 col-lg-5 col-md-6 col-sm-9 col-12 text-center px-3">

				<div id="app">
					<!-- <p>
						Last result: <b>{{ decodedContent }}</b>
					</p> -->

					<p class="error">
						{{ errorMessage }}
					</p>

					<qrcode-stream @decode="onDecode" @init="onInit"></qrcode-stream>
				</div>

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


</body>
<script>
	new Vue({
		el: '#app',

		data() {
			return {
				decodedContent: '',
				errorMessage: ''
			}
		},

		methods: {
			onDecode(content) {
				this.decodedContent = content;
				load_QRcode(content);
			},

			onInit(promise) {
				promise.then(() => {
						console.log('Successfully initilized! Ready for scanning now!')
					})
					.catch(error => {
						if (error.name === 'NotAllowedError') {
							this.errorMessage = 'Hey! I need access to your camera'
						} else if (error.name === 'NotFoundError') {
							this.errorMessage = 'Do you even have a camera on your device?'
						} else if (error.name === 'NotSupportedError') {
							this.errorMessage = 'Seems like this page is served in non-secure context (HTTPS, localhost or file://)'
						} else if (error.name === 'NotReadableError') {
							this.errorMessage = 'Couldn\'t access your camera. Is it already in use?'
						} else if (error.name === 'OverconstrainedError') {
							this.errorMessage = 'Constraints don\'t match any installed camera. Did you asked for the front camera although there is none?'
						} else {
							this.errorMessage = 'UNKNOWN ERROR: ' + error.message
						}
					})
			}
		}
	})
</script>

</html>