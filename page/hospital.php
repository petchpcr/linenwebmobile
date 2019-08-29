<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
if ($Userid == "") {
	header("location:../index.html");
}

$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/hospital_lang.xml');
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
	<?php
	$Menu = $_GET['Menu'];
	if ($Menu == 'dirty') {
		echo "<title>" . $genarray['titledirty'][$language] . $array['title'][$language] . "</title>";
	} else if ($Menu == 'factory') {
		echo "<title>" . $genarray['titlefactory'][$language] . $array['title'][$language] . "</title>";
	} else if ($Menu == 'clean') {
		echo "<title>" . $genarray['titleclean'][$language] . $array['title'][$language] . "</title>";
	} else if ($Menu == 'qc') {
		echo "<title>" . $genarray['titleQC'][$language] . $array['title'][$language] . "</title>";
	}
	?>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var Menu = '<?php echo $Menu; ?>';
		var SiteCode;

		$(document).ready(function(e) {
			load_site();
		});

		// function
		function ImgToText() {
			var ocrad = "../img/0.png";
			var str = OCRAD(ocrad);
			alert(str);
		}

		function load_site() {
			var data = {
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function show_doc(sltHpt) {
			SiteCode = sltHpt;
			if (Menu == "factory") {
				$("#choose_doc").modal('show');
			} else {
				window.location.href = 'dirty.php?siteCode=' + SiteCode + '&Menu=' + Menu;
			}
		}

		function doc_of_type() {
			var slt = $("#DocType").val();
			window.location.href = 'dirty.php?siteCode=' + SiteCode + '&Menu=' + Menu + '&From=' + slt;
		}

		function back() {
			window.location.href = "menu.php";
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/hospital.php';
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
							for (var i = 0; i < (Object.keys(temp).length - 2); i++) {
								var picture = temp[i]['picture'];
								if (temp[i]['picture'] == null || temp[i]['picture'] == "") {
									picture = "logo.png";
								}

								var Str = "<button onclick='show_doc(\"" + temp[i]['HptCode'] + "\")' class='btn btn-mylight btn-block' style='align-items: center !important;'>";
								Str += "<div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>";
								Str += "<img class='hpt_img' src='../img/" + picture + "'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>";
								Str += "<img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name font-weight-bold'>" + temp[i]['HptName'] + "</div></div></div></button>";

								$("#hospital").append(Str);
							}
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						swal({
							title: '',
							text: "<?php echo $genarray['NotFoundHpt'][$language] ?>",
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
	<header data-role="header">
		<div class="head-bar d-flex justify-content-between">
			<div style="width:139.14px;">
				<?php
				$Menu = $_GET['Menu'];
				if ($Menu == 'dirty') {
					echo "<button onclick='back()' class='head-btn btn-light'><i class='fas fa-arrow-circle-left mr-1'></i>" . $genarray['back'][$language] . "</button>";
				} else {
					echo "<button onclick='back()' class='head-btn btn-light'><i class='fas fa-arrow-circle-left mr-1'></i>" . $genarray['back'][$language] . "</button>";
				}
				?>
			</div>
			<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
			<div class="text-right" style="width:139.14px;">
				<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
			</div>
		</div>
	</header>
	<div class="px-3">
		<div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="156" height="40" /></div>
		<div class="text-center my-4">
			<h4 class="text-truncate"><?php $genarray['AllHospital'][$language] ?></h4>
		</div>
		<div id="hospital"></div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="choose_doc" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel"><?php echo $genarray['confirmCreatedocno'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center">
					<?php echo $genarray['selecttypedoc'][$language]; ?>
					<div class="input-group my-3">
						<div class="input-group-prepend">
							<label class="input-group-text" for="inputGroupSelect01"><?php echo $genarray['selecttypedoc'][$language]; ?></label>
						</div>
						<select id="DocType" class="custom-select">
					
							<option value="all" selected><?php echo $array['refDocall'][$language]; ?></option>
							<option value="dirty"><?php echo $array['refDocDirty'][$language]; ?></option>
							<option value="newlinentable"><?php echo $array['refDocRewash'][$language]; ?></option>
							<option value="rewash"><?php echo $array['refDocnew'][$language]; ?></option>
						</select>
					</div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-6 text-right">
							<button id="btn_confirm" onclick="doc_of_type()" type="button" class="btn btn-primary m-2"><?php echo $genarray['yes'][$language]; ?></button>
						</div>
						<div class="col-6 text-left">
							<button type="button" class="btn btn-secondary m-2" data-dismiss="modal"><?php echo $genarray['isno'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>