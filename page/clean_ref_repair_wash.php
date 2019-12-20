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
$form_out = $_GET['form_out'];
$siteCode = $_GET['siteCode'];
$DepCode = $_GET['DepCode'];
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
		var Userid = "<?php echo $Userid ?>";
		var DepCode = "<?php echo $DepCode ?>";
		var siteCode = "<?php echo $siteCode ?>";
		var Menu = "<?php echo $Menu ?>";

		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			load_site();
			load_doc();
		});

		// function
		function load_site() {
			$('#datepicker').val("<?php echo date("d-m-Y"); ?>");
			$('#datepicker2').val("<?php echo date("d-m-Y"); ?>");
			var data = {
				'siteCode': siteCode,
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function load_doc() {
			var search = $('#datepicker').val();
			var search2 = $('#datepicker2').val();
			var data = {
				'search': search,
				'search2': search2,
				'siteCode': siteCode,
				'Menu': Menu,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function check_doc(chk_id) {
			var id = "#" + chk_id;
			if ($(id).is(':checked')) {
				$(id).prop("checked", false);
			} else {
				$(id).prop("checked", true);
			}
		}

		function add_repair_wash() {
			var RefDocNo_ar = [];
			var FacCode_ar = [];
			var have = 0;

			$(".chk-doc").each(function() {
				if ($(this).is(':checked')) {
					FacCode_ar.push($(this).attr("data-faccode"));
					RefDocNo_ar.push($(this).val());
					have++;
				}
			});
			var FacLength = jQuery.unique(FacCode_ar);

			if (have > 0) {
				if (FacLength.length == 1) {
					$("#add_doc").prop("disabled", true);
					var data = {
						'Userid': Userid,
						'siteCode': siteCode,
						'DepCode': DepCode,
						'FacCode': FacLength[0],
						'RefDocNo_ar': RefDocNo_ar,
						'STATUS': 'add_repair_wash'
					};
					senddata(JSON.stringify(data));
				} else {
					swal({
						title: '',
						text: '<?php echo $genarray['docsamefacpls'][$language]; ?>',
						type: 'warning',
						showCancelButton: false,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						showConfirmButton: false,
						timer: 2500
					})
				}
			}

		}

		function back() {
			window.location.href = Menu + ".php?siteCode=" + siteCode + "&Menu=" + Menu + txt_form_out;
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);

			$.ajax({
				url: '../process/clean_ref_repair_wash.php',
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
							for (var i = 0; i < temp['cnt']; i++) {
								var status_class = "";
								var status_text = "";
								var status_line = "";

								status_class = "status1";
								status_text = "<?php echo $genarray['statusfin'][$language]; ?>";
								status_line = "StatusLine_1";

								if (temp[i]['IsStatus'] == 3) {
									var dep = "<div class='my-col-7 text-left'>";
									dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div>";
									dep += "<div class='text-truncate font-weight-light align-self-center'>" + temp[i]['FacName'] + "</div></div></div></button>";
									if (temp[i]['FacName'] == null) {
										dep = "<div class='my-col-7 text-left d-flex'>";
										dep += "<div class='text-truncate font-weight-bold align-self-center'>" + temp[i]['DocNo'] + "</div></div></div></button>";
									}

									var Str = "<button onclick='check_doc(\"chk" + i + "\")' class='btn btn-mylight btn-block px-0' style='align-items: center !important;'><div class='row'><div class='my-col-5 d-flex justify-content-end align-items-center'>";
									Str += "<div class='row'>";
									Str += "<div class='d-flex align-items-center mr-2'><input id='chk" + i + "' value='" + temp[i]['DocNo'] + "' data-faccode='" + temp[i]['FacCode'] + "' class='chk-doc' type='checkbox'></div>";
									Str += "<div class='card " + status_class + "' style='max-width:105px;'>" + status_text + "</div>";
									Str += "<img src='../img/" + status_line + ".png' height='50'/></div></div>" + dep;

									$("#document").append(Str);
								}

							}
						} else if (temp["form"] == 'add_repair_wash') {
							var Userid = temp['user']
							var siteCode = temp['siteCode']
							var DepCode = temp['DepCode']
							var DocNo = temp['DocNo']
							window.location.href = 'add_items_clean_real.php?siteCode=' + siteCode + '&DepCode=' + DepCode + '&DocNo=' + DocNo + '&Menu=' + Menu + '&user=' + Userid + '&Delback=1&Ref=repair_wash' + txt_form_out;
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
				<button onclick="back()" class="head-btn btn-primary"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ " . $Per . " ]" ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-primary" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3 pb-4 mb-5">

		<div align="center" style="margin:1rem 0;">
			<div class="mb-3">
				<img src="../img/logo.png" width="156" height="60" />
			</div>
			<!-- <div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div> -->
		</div>
		<div class="text-center my-4">
			<h4 id="HptName" class="text-truncate"></h4>
		</div>
		<div id="document">
			<div class="row py-0 px-3 d-flex justify-content-center mb-3">
				<div class="col-12 col-md-auto p-0 mb-2">
					<div class="row p-0 m-0">
						<div class="col-12 col-md-4 text-center p-0"><?php echo $genarray['startdate'][$language]; ?> </div>
						<div class="col-12 col-md-8 p-0">
							<input type="text" id="datepicker" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly>
						</div>
					</div>
				</div>

				<div class="col-12 col-md-auto p-0 mb-2">
					<div class="row p-0 m-0">
						<div class="col-12 col-md-4 text-center p-0"><?php echo $genarray['enddate'][$language]; ?> </div>
						<div class="col-12 col-md-8 p-0">
							<input type="text" id="datepicker2" class="form-control bg-white text-center datepicker-here" style="font-size:20px;" data-language=<?php echo $language ?> data-date-format='dd-mm-yyyy' readonly>
						</div>
					</div>
				</div>

				<div class="col-12 col-md-auto py-0 px-1">
					<button onclick="load_doc()" class="btn btn-info btn-block p-1" style="max-height:40px;" type="button"><i class="fas fa-search mr-1"></i><?php echo $genarray['search'][$language]; ?></button>
				</div>
			</div>

			<div class="fixed-bottom py-2 px-3 bg-white d-flex justify-content-center">
				<button id="add_doc" onclick="add_repair_wash()" class="btn btn-success btn-block d-flex justify-content-center align-items-center" type="button" style="max-width:250px;">
					<i class="fas fa-check-circle mr-2"></i><?php echo $genarray['confirm'][$language]; ?>
				</button>
			</div>
		</div>
	</div>

</body>

</html>