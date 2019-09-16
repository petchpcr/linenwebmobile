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

	<script>
		var getQR = "BHQLPNONO010006,12";
		var itemCode;
		var itemQty;
		$(document).ready(function(e) {
			load_QRcode(getQR);
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
							$("#item_name").val(temp['ItemName']);
							$("#item_qty").val(itemQty);
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
			<div class="col-lg-4 col-md-6 col-sm-8 col-12 text-center px-3">

				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">ชื่อไอเทม</span>
					</div>
					<input type="text" id="item_name" class="form-control bg-white" value="เสื้อขาว size XL" disabled>
				</div>

				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" style="width:100px;">จำนวน</span>
					</div>
					<input type="text" id="item_qty" class="form-control bg-white" value="24" disabled>
				</div>

				<button class="btn btn-create mt-2"><i class="fas fa-camera mr-2"></i>แสกนอีกครั้ง</button>
			</div>
		</div>

	</section>
</body>

</html>