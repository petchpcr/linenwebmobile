<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
	header("location:../index.html");
}
$Menu = $_GET['Menu'];
$siteCode = $_GET['siteCode'];
$DepCode = $_GET['DepCode'];
$From = $_GET['From'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/dirty_lang.xml');
$json = json_encode($xml);
$array = json_decode($json, TRUE);
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

	<title><?php echo $genarray['titleclean'][$language] . $genarray['titleRefDocument'][$language]; ?></title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script src="../dist/js/sweetalert2.min.js"></script>
	<link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
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
			var From = "<?php echo $From ?>";
			var data = {
				'search': search,
				'siteCode': siteCode,
				'Menu': Menu,
				'From': From,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function add_dirty(refDocNo) {
			var Userid = "<?php echo $Userid ?>";
			var siteCode = "<?php echo $siteCode ?>";
			var DepCode = "<?php echo $DepCode ?>";
			var data = {
				'Userid': Userid,
				'siteCode': siteCode,
				'DepCode': DepCode,
				'refDocNo': refDocNo,
				'STATUS': 'add_dirty'
			};
			senddata(JSON.stringify(data));
			console.log(data);
		}

		function back() {
			var siteCode = "<?php echo $siteCode ?>";
			var Menu = '<?php echo $Menu; ?>';
			var From = '<?php echo $From; ?>';
			window.location.href = "clean.php?siteCode=" + siteCode + "&Menu=" + Menu + "&From=" + From;
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var From = '<?php echo $From; ?>';

			var URL = '../process/ref_dirty.php';

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

								status_class = "status1";
								status_text = "<?php echo $genarray['statusfin'][$language]; ?>";
								status_line = "StatusLine_1";

								if (temp[i]['IsStatus'] == 3) {

									var Str = "<button onclick='add_dirty(\"" + temp[i]['DocNo'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
									Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
									Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div><div class='my-col-7 text-left'>";
									Str += "<div class='text-truncate font-weight-bold'>" + temp[i]['DocNo'] + "</div><div class='font-weight-light'>" + temp[i]['DepName'] + "</div></div></div></button>";

									$("#document").append(Str);
								}

							}
						} else if (temp["form"] == 'add_dirty') {
							var Userid = temp['user']
							var siteCode = temp['siteCode']
							var DepCode = temp['DepCode']
							var DocNo = temp['DocNo']
							var RefDocNo = temp['RefDocNo']
							var Menu = '<?php echo $Menu; ?>';
							window.location.href = 'add_items.php?siteCode=' + siteCode + '&DepCode=' + DepCode + '&DocNo=' + DocNo + '&RefDocNo=' + RefDocNo + '&Menu=' + Menu + '&user=' + Userid;
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
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
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3 pb-4 mb-5">

		<div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="156" height="40" /></div>
		<div class="text-center my-4">
			<h4 id="HptName" class="text-truncate"></h4>
		</div>
		<div id="document">
			<div class="d-flex justify-content-center mb-3">
				<div width="50"><input type="text" id="datepicker" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly></div>
				<button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
			</div>
		</div>
	</div>

</body>

</html>