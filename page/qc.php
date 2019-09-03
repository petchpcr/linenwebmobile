<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$Menu = $_GET['Menu'];
$siteCode = $_GET['siteCode'];
$language = $_SESSION['lang'];
$genxml = simplexml_load_file('../xml/Language/general_lang.xml');
$json = json_encode($genxml);
$genarray = json_decode($json, TRUE);
require '../getTimeZone.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		$(document).ready(function(e) {
			load_site();
			load_doc();
		});

		// function
		function load_site() {
			$('#datepicker').val("<?php echo date("d-m-Y"); ?>");
			var siteCode = "<?php echo $siteCode ?>";
			var data = {
				'siteCode': siteCode,
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function load_doc() {
			var search = $('#datepicker').val();
			var siteCode = "<?php echo $siteCode ?>";
			var Menu = "<?php echo $Menu ?>";
			var data = {
				'search': search,
				'siteCode': siteCode,
				'Menu': Menu,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function show_qc(DocNo) {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'get_doc_type'
			};
			senddata(JSON.stringify(data));
		}

		function change_dep() {
			var slt = $("#DepName").val();
			if (slt == 0) {
				$("#btn_add_dirty").prop('disabled', true);
			} else {
				$("#btn_add_dirty").prop('disabled', false);
			}
		}

		function add_dirty() {
			var Userid = "<?php echo $Userid ?>";
			var siteCode = "<?php echo $siteCode ?>";
			var DepCode = $("#DepName").val();
			var data = {
				'Userid': Userid,
				'siteCode': siteCode,
				'DepCode': DepCode,
				'STATUS': 'add_dirty'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			var Menu = '<?php echo $Menu; ?>';
			window.location.href = "menu.php";
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/qc.php';
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
						if (temp["form"] == 'load_site') {
							$("#HptName").text(temp['HptName']);
						} else if (temp["form"] == 'load_doc') {

							$(".btn.btn-mylight.btn-block").remove();
							for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
								var status_class = "";
								var status_text = "";
								var status_line = "";
								var CheckList = temp[i]['IsCheckList'];

								if (CheckList == 0 || CheckList == null) {
									status_class = "status4";
									status_text = "<?php echo $genarray['statusNotQC'][$language]; ?>";
									status_line = "StatusLine_4";
								} else if (CheckList == 1) {
									status_class = "status3";
									status_text = "<?php echo $genarray['statusPass'][$language]; ?>";
									status_line = "StatusLine_3";
								} else if (CheckList == 2) {
									status_class = "status5";
									status_text = "<?php echo $genarray['statusClaim'][$language]; ?>";
									status_line = "StatusLine_5";
								}

								var Str = "<button onclick='show_qc(\"" + temp[i]['DocNo'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
								Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
								Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div><div class='my-col-7 text-left'>";
								Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['DocNo'] + "</div><div class='font-weight-light'>" + temp[i]['DepName'] + "</div></div></div></button>";

								$("#document").append(Str);
							}
						} else if (temp["form"] == 'add_dirty') {
							var Userid = temp['user']
							var siteCode = temp['siteCode']
							var DepCode = temp['DepCode']
							var DocNo = temp['DocNo']
							var Menu = '<?php echo $Menu; ?>';
							window.location.href = 'add_items.php?siteCode=' + siteCode + '&DepCode=' + DepCode + '&DocNo=' + DocNo + '&Menu=' + Menu + '&user=' + Userid;
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						} else if (temp["form"] == 'get_doc_type') {
							var siteCode = "<?php echo $siteCode ?>";
							var Menu = '<?php echo $Menu; ?>';
							var DocNo = temp["DocNo"];
							window.location.href = 'qc_view.php?siteCode=' + siteCode + '&Menu=' + Menu + '&DocNo=' + DocNo + '&from=' + temp["table"];
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_doc') {
							$(".btn.btn-mylight.btn-block").remove();
							swal({
								title: '',
								text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?>' + $('#datepicker').val(),
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Data found'
							})

						} else {
							swal({
								title: '',
								text: temp['msg'],
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Error!!'
							})
						}

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
	<header data-role="header">
		<div class="head-bar d-flex justify-content-between">
			<div style="width:139.14px;">
				<button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ ".$Per." ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3 pb-4 mb-5">

		<div align="center" style="margin:1rem 0;"><img src="../img/Linen4.0.png" width="230" height="40" /></div>
		<div id="HptName" class="text-center text-truncate font-weight-bold my-4" style="font-size:25px;"></div>
		<div id="document">
			<div class="d-flex justify-content-center mb-3">
				<div width="50"><input type="text" id="datepicker" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly></div>
				<button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
			</div>
		</div>
	</div>

	<script>
		$('#datepicker').datepicker({
			// uiLibrary: 'bootstrap4',
			size: 'large',
			format: 'dd-mm-yyyy'
		});
	</script>

</body>

</html>