<?php
    session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="shortcut icon" href="../favicon.ico">
	<link rel="stylesheet" href="../css/themes/default/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
	<script src="../js/jquery.js"></script>
	<script src="../js/jquery.mobile-1.4.5.min.js"></script>
    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
	<script>
        function chklogin() {
            var user = $("#username").val();
			var password = $("#password").val();
	
			if (user != "" && password != "") {
			var data = {
				'PAGE': 'login',
				'USERNAME': user,
				'PASSWORD': password
			};
			console.log(JSON.stringify(data));
			senddata(JSON.stringify(data));
			} else {
			swal({
				type: 'warning',
				title: 'Something Wrong',
				text: 'Please recheck your username and password! test'
			})
			}
        }

        function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/login.php';
			$.ajax({
			url: URL,
			dataType: 'text',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: 'post',
			beforeSend: function () {
				swal({
					title: 'Please wait...',
					text: 'Processing',
					allowOutsideClick: false
				})
				swal.showLoading()
			},
			success: function (result) {
				try {
					var temp = $.parseJSON(result);
					console.log(result);
				} catch (e) {
					console.log('Error#542-decode error');
				}
				if (temp["status"] == 'success') {
					swal.hideLoading()
					swal({
						title: '',
						text: temp["msg"],
						type: 'success',
						showCancelButton: false,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						timer: 1000,
						confirmButtonText: 'Ok',
						showConfirmButton: false
					}).then(function () {
						window.location.href = 'menu.php';
					}, function (dismiss) {
						window.location.href = 'menu.php';
						if (dismiss === 'cancel') {
		
						}
					})
	
				} else {
				swal.hideLoading()
				swal({
					title: 'Something Wrong',
					text: temp["msg"],
					type: 'error',
					showCancelButton: false,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: 'Ok'
				}).then(function () {
	
				}, function (dismiss) {
					// dismiss can be 'cancel', 'overlay',
					// 'close', and 'timer'
					if (dismiss === 'cancel') {
	
					}
				})
				//alert(temp["msg"]);
				}
	
			},
			failure: function (result) {
				// alert(result);
			},
			error: function (xhr, status, p3, p4) {
				// var err = "Error " + " " + status + " " + p3 + " " + p4;
				// if (xhr.responseText && xhr.responseText[0] == "{")
				// err = JSON.parse(xhr.responseText).Message;
				// console.log(err);
			}
			});
		}
	</script>
	<style>
		body, html {
			height: 100% !important;
			margin: 0 !important;
		}
		.bg_login {
			/* The image used */
			background-image: url("../img/background01.png") !important;

			/* Full height */
			height: 100% !important; 

			/* Center and scale the image nicely */
			background-position: center !important;
			background-repeat: no-repeat !important;
			background-size: cover !important;
		}
	</style>
</head>

<body>
	<div class="bg_login">
		<div class="row" style="padding-top:10rem;">
			<div class="col-lg-3 col-md-2 col-sm-none"></div>
			<div class="col-lg-6 col-md-8 col-sm-12">
				<table class="center" width="100%" border="0" cellspacing="5" cellpadding="5">
				<tr>
					<td>
						<div class="mb-2" align="center"><img src="../img/logo.png" width="240" height="60" /></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="mx-4" data-demo-html="true">
							<label for="text-basic">User Name :</label>
							<input type="text" id="username" value="">
						</div>
						<div class="mx-4" data-demo-html="true">
							<label for="text-basic">Password :</label>
							<input type="password" id="password" value="">
						</div>
						<div class="m-4">
							<button class="ui-shadow ui-btn ui-corner-all" name="submit-button-1" id="submit-button-1" onclick="chklogin();" >ตกลง</button>	
						</div>
							
					</td>
				</tr>
				</table>
			</div>
			<div class="col-lg-3 col-md-2 col-sm-none"></div>
		</div>
		<div style="padding-bottom:50rem;">
		</div>
	</div>
				
</body>

</html>
