<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$DocNo = $_GET['DocNo'];
$form_out = $_GET['form_out'];
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
		var DocNo = "<?php echo $DocNo ?>";
		var RadioName = [];
		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			$("#DocNo").text(DocNo);
			load_question();
		});

		// function
		function load_question() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'load_question'
			};
			senddata(JSON.stringify(data));
		}

		function click_rd(ID,val) {
			$("input[name=" + ID + "][value=" + val + "]").prop('checked', true);
		}

		function save_checklist() {
			var ChkResult = [];
			var HaveNull = 0;
			RadioName.forEach(function(id, i) {
				var val = $("input[name=" + id + "]:checked").val();
				ChkResult.push(val);
			});
			
			var data = {
				'DocNo': DocNo,
				'RadioName': RadioName,
				'ChkResult': ChkResult,
				'STATUS': 'save_checklist'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			window.location.href = 'kpi_clean1.php?siteCode=' + siteCode + txt_form_out;
		}

		function AlertError(Title, Text, Type) {
			swal({
				title: Title,
				text: Text,
				type: Type,
				showConfirmButton: true,
				confirmButtonColor: '#3085d6',
				confirmButtonText: '<?php echo $genarray['yes2'][$language]; ?>'
			})
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/kpi_clean1_checklist.php';
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
						if (temp["form"] == 'load_question') {
							$("#question").empty();

							for (var i = 0; i < temp['cnt']; i++) {
								var ID = temp['ID'][i];
								RadioName.push(ID);
								var Pchk = "";
								var Uchk = "";
								if (temp['IsCheck'][i] == 0) {
									Uchk = "checked";
								} else if (temp['IsCheck'][i] == 1) {
									Pchk = "checked";
								}
								var Str = "<tr>";
								Str += "			<td>";
								Str += "					<div class='row'>";
								Str += "						<div class='col-2 text-center'>" + Number(i+1) + "</div>";
								Str += "						<div class='col-2 text-center'>" + temp['Standard'][i] + "</div>";
								Str += "						<div class='col-6 text-left'>" + temp['Question'][i] + "</div>";
								Str += "						<div class='col-2 text-center row p-0'>";
								Str += "							<div class='col-6 p-0 bg_rd' onclick='click_rd(\"" + ID + "\",1)'>";
								Str += "								<input type='radio' name='" + ID + "' value='1' " + Pchk + ">";
								Str += "								<label><?php echo $genarray['pass'][$language]; ?></label>";
								Str += "							</div>";
								Str += "							<div class='col-6 p-0 bg_rd' onclick='click_rd(\"" + ID + "\",0)'>";
								Str += "								<input type='radio' name='" + ID + "' value='0' " + Uchk + ">";
								Str += "								<label><?php echo $genarray['notpass'][$language]; ?></label>";
								Str += "							</div>";
								Str += "						</div>";
								Str += "					</div>";
								Str += "				</td>";
								Str += "			</tr>";

								$("#question").append(Str);
							}

							if (temp['IsStatus'] == 1) {
								$("#btn_save").remove();
								$(".bg_rd").removeAttr("onclick");
								$("input").prop("disabled",true);
							}
						} else if (temp["form"] == 'save_checklist') {
							swal({
								title: '',
								text: '<?php echo $genarray['savesuccess'][$language]; ?>',
								type: 'success',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 1500,
								confirmButtonText: 'Data found'
							})
							load_question();

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_question') {
							$("#question").empty();
							swal({
								title: '',
								text: '',
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 1500,
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
		<div class="text-center mb-3">
			<div class="text-truncate font-weight-bold" style="font-size:25px;"><?php echo $genarray['docno'][$language]; ?></div>
			<div id="DocNo" class="text-truncate font-weight-bold" style="font-size:25px;"></div>
		</div>
		<div style="margin-bottom:70px;">

			<div class="row justify-content-center mb-5 px-3">
				<table class="table table-hover col-lg-9 col-md-10 col-sm-12">
					<thead>
						<tr class="bg-primary text-white">
							<th scope="col">
								<div class="row">
									<div class="col-2 text-center"><?php echo $genarray['no'][$language]; ?></div>
									<div class="col-2 text-center"><?php echo $genarray['standard'][$language]; ?></div>
									<div class="col-6 text-center"><?php echo $genarray['question'][$language]; ?></div>
									<div class="col-2 text-center"><?php echo $genarray['result'][$language]; ?></div>
								</div>
							</th>
						</tr>
					</thead>
					<tbody id="question"></tbody>
				</table>
			</div>

			<div class="fixed-bottom py-2 px-3 bg-white d-flex justify-content-center">
				<button id="btn_save" onclick="save_checklist()" class="btn btn-success btn-block" type="button" style="max-width:250px;">
					<i class="fas fa-plus mr-1"></i><?php echo $genarray['save'][$language]; ?>
				</button>
			</div>
		</div>
	</div>

</body>

</html>