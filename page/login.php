<?php
    session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>

	<!-- <link rel="shortcut icon" href="../favicon.ico">
	<link rel="stylesheet" href="../css/themes/default/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
	<script src="../js/jquery.js"></script>
	<script src="../js/jquery.mobile-1.4.5.min.js"></script>
    <script src="../dist/js/sweetalert2.min.js"></script>
	<link rel="stylesheet" href="../dist/css/sweetalert2.min.css"> -->
	
	<script src="../js/jquery-3.3.1.min.js"></script>

    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <script src="../bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.css">

    <script src="../js/gijgo.min.js" type="text/javascript"></script>
    <link href="../css/gijgo.min.css" rel="stylesheet" type="text/css" />

    <script src="../dist/js/sweetalert2.min.js"></script>
	<link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
	
	<script type="text/javascript">
		function back() {
			alert("Go to back");
		}

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
				text: 'Please recheck your username and password!'
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
					// var FacCode = temp['FacCode'];
					var PmID = temp['PmID'];
					
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
						if (PmID == 2) {
							window.location.href = 'hospital.php?Menu=factory';
						}
						else {
							window.location.href = 'menu.php';
						}
					}, function (dismiss) {
						if (PmID == 2) {
							window.location.href = 'hospital.php?Menu=factory';
						}
						else {
							window.location.href = 'menu.php';
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
			background-image: url("../img/background01.png") !important;
		}
	</style>
</head>

<body>
	<div class="d-flex justify-content-center" style="height:100%;padding-top:10rem;">
		<div class="col-lg-9 col-md-10 col-sm-12">

			<div class="mb-3" align="center"><img src="../img/logo.png" width="240" height="60" /></div>

			<div class="form-group">
				<label>User Name :</label>
				<input type="text" class="form-control" id="username">
			</div>

			<div class="form-group mb-4">
				<label>Password :</label>
				<input type="password" class="form-control" id="password">
			</div>

			<div class="form-group">
				<button class="btn btn-info btn-block font-weight-bold" onclick="chklogin();" >Login</button>	
			</div>
		</div>
		
	</div>	
</body>

</html>
