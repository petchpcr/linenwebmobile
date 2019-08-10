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
	
	<?php require 'script_css.php'; ?>
	<link rel="stylesheet" href="../css/bootstrap-material-design.css">
	<link rel="stylesheet" href="../css/docs.min.css">
	<link rel="stylesheet" href="../css/style_login.css">

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

		function make_char() {
            $('.nonspa').on('input', function() {
                this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');; //<-- replace all other than given set of values
            });
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
					
					swal.hideLoading();
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
						if (PmID == 4) {
							//window.location.href = 'hospital.php?Menu=factory';
							window.location.href = 'menu.php';
						}
						else {
							window.location.href = 'menu.php';
						}
					}, function (dismiss) {
						if (PmID == 4) {
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
</head>

<body>
	<div class="row p-0 m-0 d-flex justify-content-center">
		<div class="col-lg-7 col-md-9 col-sm-11 col-11 p-0">
			<img src="../img/head_login.png" class="head_login_img" style="border-radius:30px 30px 0 0;">
		</div>
		<div class="col-lg-7 col-md-9 col-sm-11 col-11 p-0">
			<div class="bg-white card-body" style="border-radius:0 0 30px 30px;">
			<div class="row text-center mb-3 p-0">
				<div class="col-12 mb-4"><img src="../img/logo.png" class="logo_login_img"/></div>
				<div class="col-12 mt-2"><img src="../img/nlinen.png" class="logo_login_img" style="max-height:15px;"/></div>
			</div>

			<div id="row p-0">
				<div class="col-12">
					<div id="icon_user">
						<i class="fas fa-user"></i>
					</div>
					<div class="form-group bmd-form-group">
						<label for="username" id="label_username" class="bmd-label-floating">Username (<?php echo "http://{$_SERVER['HTTP_HOST']}";?>)</label>
						<input onkeyup='make_char()' type="text" autocomplete="off" class="form-control" id="username">
					</div>
				</div>
			</div>
			
			<div id="row">
				<div class="col-12">
					<div id="icon_password">
						<i class="fas fa-lock"></i>
					</div>
					<div class="form-group bmd-form-group">
						<label for="password" id="label_password" class="bmd-label-floating">Password</label>
						<input type="password" autocomplete="off" class="form-control" id="password">
					</div>
				</div>
			</div>
			
			<div class="text-center mt-2">
				<div id="btn_login">
					<button onclick="chklogin()" class="btn btn_custom">
					<div class="row align-items-center px-4">
						<div class="ml-3 mr-auto">LOGIN</div>	
						<i class="fas fa-arrow-right mr-3"></i>
					</div>
					
					</button>
				</div>
			</div>
			</div>
		</div>
	</div>
	<script src="../js/popper.min.js"></script>
	<script src="../js/bootstrap-material-design.js"></script>
	
	<script src="../js/anchor.min.js"></script>
    <script src="../js/clipboard.min.js"></script>
    <script src="../js/holder.min.js"></script>
    <script src="../js/application.js"></script>
</body>

</html>
