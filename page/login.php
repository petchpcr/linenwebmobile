<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>

	<?php require 'script_css.php'; ?>
	<link rel="stylesheet" href="../css/bootstrap-material-design.css">
	<link rel="stylesheet" href="../css/docs.min.css">
	<link rel="stylesheet" href="../css/style_login.css">

	<script type="text/javascript">
		// function
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
		// end function

		// display
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
				beforeSend: function() {
					swal({
						title: 'Please wait...',
						text: 'Processing',
						allowOutsideClick: false
					})
					swal.showLoading()
				},
				success: function(result) {
					try {
						var temp = $.parseJSON(result);
						console.log(result);
					} catch (e) {
						console.log('Error#542-decode error');
					}
					if (temp["status"] == 'success') {
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
						}).then(function() {
							if (PmID == 4) {
								window.location.href = 'menu.php';
							} else {
								window.location.href = 'menu.php';
							}
						}, function(dismiss) {
							if (PmID == 4) {
								window.location.href = 'menu.php';
							} else {
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
						}).then(function() {

						}, function(dismiss) {
							// dismiss can be 'cancel', 'overlay',
							// 'close', and 'timer'
							if (dismiss === 'cancel') {

							}
						})
					}

				},
				failure: function(result) {},
				error: function(xhr, status, p3, p4) {}
			});
		}
	</script>
</head>

<body>
	<div class="h-100 d-flex align-items-center p-0 m-0">
		<div>
			<div class="d-flex justify-content-center w-100 mb-0 p-0 px-2">
				<img src="../img/head_login.jpg" class="head_login_img" style="border-radius:30px 30px 0 0;">
			</div>
			<div class="d-flex justify-content-center w-100 pt-0 pb-2 px-2">
				<div class="bg-white card-body" style="border-radius:0 0 30px 30px;max-width:509.42px;">
					<div class="row text-center mb-3 p-0">
						<div class="col-12 mb-4"><img src="../img/logo.png" class="logo_login_img" /></div>
						<div class="col-12 mt-2"><img src="../img/nlinen.png" class="logo_login_img" style="max-height:15px;" /></div>
					</div>

					<div id="row p-0">
						<div class="col-12">
							<div id="icon_user">
								<i class="fas fa-user"></i>
							</div>
							<div class="form-group bmd-form-group">
								<label for="username" id="label_username" class="bmd-label-floating">Username (<?php echo "http://{$_SERVER['HTTP_HOST']}"; ?>)</label>
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
							<button onclick="chklogin()" class="btn btn-primary" style="border-radius:30px;width:200px;">
								<div class="d-flex align-items-center">
									<div class="mr-auto ml-4" style="font-size:27px;">LOGIN</div>
									<i class="fas fa-arrow-right mr-4" style="font-size:20px;"></i>
								</div>
							</button>
						</div>
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