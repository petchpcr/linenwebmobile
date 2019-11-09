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
$xml = simplexml_load_file('../xml/Language/clean_lang.xml');
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
	<title>KPI</title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var siteCode = "<?php echo $siteCode ?>";
		var Menu = "<?php echo $Menu ?>";

		$(document).ready(function(e) {
			$('#datepicker').val("<?php echo date("d-m-Y"); ?>");
			load_doc();
		});

		// function
		function load_doc() {
			var search = $('#datepicker').val();
			var data = {
				'search': search,
				'siteCode': siteCode,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}
		
		function view_kp(DocNo) {
			window.location.href = 'kpi_clean1_checklist.php?siteCode=' + siteCode + '&DocNo=' + DocNo;
		}

		function add_kpi() {
			var search = $('#datepicker').val();
			var data = {
				'search': search,
				'siteCode': siteCode,
				'STATUS': 'add_kpi'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			window.location.href = "menu.php";
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/kpi_clean1.php';
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
						if (temp["form"] == 'load_doc') {
							$(".btn.btn-mylight.btn-block").remove();
							$("#btn_add").hide();
							var status_class = "";
							var status_text = "";
							var status_line = "";

							if (temp['IsStatus'] == 0) {
								status_class = "status1";
								status_text = "<?php echo $genarray['statusCretFin'][$language]; ?>";
								status_line = "StatusLine_1";
							} else if (temp['IsStatus'] == 1) {
								status_class = "status3";
								status_text = "<?php echo $genarray['statusfin'][$language]; ?>";
								status_line = "StatusLine_3";
							}

							var dep = "<div class='my-col-7 text-left'>";
							dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp['DocNo'] + "</div>";
							dep += "<div class='font-weight-light align-self-center'>" + temp['DepName'] + "</div></div></div></button>";
							if (temp['DepName'] == null) {
								dep = "<div class='my-col-7 text-left d-flex'>";
								dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp['DocNo'] + "</div></div></div></button>";
							}

							var Str = "<button onclick='view_kp(\"" + temp['DocNo'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
							Str += "<div class='row'><div class='card " + status_class + "'>" + status_text + "</div>";
							Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div>" + dep;

							$("#document").append(Str);

						} else if (temp["form"] == 'add_kpi') {
							load_doc();

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_doc') {
							$(".btn.btn-mylight.btn-block").remove();
							$("#btn_add").show();
							var search = $('#datepicker').val();
							swal({
								title: '',
								text: '<?php echo $genarray['notfoundDocInDate'][$language]; ?> ' + search,
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
				<button onclick="back()" class="head-btn btn-primary"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
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
			<div id="HptName" class="text-truncate font-weight-bold" style="font-size:25px;"></div>
		</div>
		<div id="document" style="margin-bottom:70px;">
			<div class="d-flex justify-content-center mb-3">
				<div width="50"><input type="text" id="datepicker" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly></div>
				<button onclick="load_doc()" class="btn btn-info ml-2 p-1" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
			</div>
			<div id="add_doc" class="fixed-bottom py-2 px-3 bg-white d-flex justify-content-center">
				<button id="btn_add" onclick="add_kpi()" class="btn btn-create btn-block" type="button" style="max-width:250px;">
					<i class="fas fa-plus mr-1"></i><?php echo $genarray['createdocno'][$language]; ?>
				</button>
			</div>
		</div>
	</div>

</body>

</html>