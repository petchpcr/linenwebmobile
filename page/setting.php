<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}

$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/setting_lang.xml');
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
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var Del_fac = [];
		var Del_time = [];

		$(document).ready(function(e) {
			$("#lang").val('<?php echo $language; ?>');
		});

		// function
		function back() {
			window.location.href = "menu.php";
		}

		function time_to_hpt() {
			$("#md_fac_time").modal('hide');
			$("#md_fac_nhealth").modal('show');
		}

		function enable_add() {
			$("#AddFacNhealth").prop("disabled",false);
		}

		function load_site_fac() {
			var data = {
				'STATUS': 'load_site_fac'
			};
			senddata(JSON.stringify(data));
		}

		function load_site() {
			var data = {
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function load_send_time() {
			var data = {
				'STATUS': 'load_site_time'
			};
			senddata(JSON.stringify(data));
		}

		function show_fac() {
			var HptCode = '<?php echo $_SESSION['HptCode']; ?>';
			var data = {
				'HptCode': HptCode,
				'STATUS': 'show_fac'
			};
			senddata(JSON.stringify(data));
		}

		function show_fac_time(FacCode) {
			var data = {
				'FacCode': FacCode,
				'STATUS': 'show_fac_time'
			};
			senddata(JSON.stringify(data));
		}

		function show_hpt_time(HptCode) {
			var data = {
				'HptCode': HptCode,
				'STATUS': 'show_hpt_time'
			};
			senddata(JSON.stringify(data));
		}

		function save_edit_factime() {
			var ar_fac = [];
			var ar_time = [];
			var ar_newtime = [];
			$(".fac_time").each(function() {
				var FacCode = $(this).attr("data-fac");
				var Time = $(this).attr("data-time");
				var New_Time = $(this).val();
				ar_fac.push(FacCode);
				ar_time.push(Time);
				ar_newtime.push(New_Time);
			});

			var data = {
				'Del_fac': Del_fac,
				'Del_time': Del_time,
				'ar_fac': ar_fac,
				'ar_time': ar_time,
				'ar_newtime': ar_newtime,
				'STATUS': 'save_edit_factime'
			};
			senddata(JSON.stringify(data));
		}

		function del_list_time(list, fac, time) {
			$("#" + list).remove();
			Del_fac.push(fac);
			Del_time.push(time);
		}

		function LoadDeliveryFacNhealth() {
			var data = {
				'STATUS': 'LoadDeliveryFacNhealth'
			};
			senddata(JSON.stringify(data));
		}

		function AddFacNhealth() {
			var FacCode = $("#from_fac").val();
			var SendTime = $("#new_send_time").val();
			var data = {
				'FacCode': FacCode,
				'SendTime': SendTime,
				'STATUS': 'AddFacNhealth'
			};
			senddata(JSON.stringify(data));
		}

		function EditFacNhealth(count) {
			var arr_fac_FacCode = [];
			var arr_fac_HptCode = [];
			var arr_fac_sentTime = [];
			for (var i = 0; i < count; i++) {
				var id = "#fac_nhealth_time" + i;
				var FacCode = $(id).attr("data-FacCode");
				var HptCode = $(id).attr("data-HptCode");
				var SendTime = $(id).val();
				arr_fac_FacCode.push(FacCode);
				arr_fac_HptCode.push(HptCode);
				arr_fac_sentTime.push(SendTime);
			}
			var str_FacCode = arr_fac_FacCode.join(',');
			var str_HptCode = arr_fac_HptCode.join(',');
			var str_sentTime = arr_fac_sentTime.join(',');

			var data = {
				'str_FacCode': str_FacCode,
				'str_HptCode': str_HptCode,
				'str_sentTime': str_sentTime,
				'STATUS': 'EditFacNhealth'
			};
			senddata(JSON.stringify(data));
		}

		function save_lang() {
			var lang = $("#lang").val();
			var Userid = '<?php echo $Userid; ?>';

			var data = {
				'lang': lang,
				'Userid': Userid,
				'STATUS': 'save_lang'
			};
			senddata(JSON.stringify(data));
		}

		function save() {
			save_lang()
		}

		function make_number() {
			$('.numonly').on('input', function() {
				this.value = this.value.replace(/[^0-9]/g, ''); //<-- replace all other than given set of values\
				this.value = Number(this.value);
			});
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
			var URL = '../process/setting.php';
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
					if ($("#lang").val() == "th") {
						var lang = '<?php echo $genarray['savesuccess']["th"]; ?>';
					} else {
						var lang = '<?php echo $genarray['savesuccess']["en"]; ?>';
					}
					if (temp["status"] == 'success') {
						if (temp["form"] == 'load_site_fac') {
							$("#from_fac").empty();
							if (temp['cnt_Fac'] > 0) {
								for (var i = 0; i < temp['cnt_Fac']; i++) {
									var Str = "<option value='" + temp[i]['FacCode'] + "'>" + temp[i]['FacName'] + "</option>";
									$("#from_fac").append(Str);
								}
							} else {
								var Str = "<option value='false'><?php echo $array['noFac'][$language]; ?></option>";
								$("#from_fac").append(Str);
								$("#AddFacNhealth").hide();
							}

							$("#AddFacNhealth").prop("disabled",true);
							$("#new_send_time").val("");
							$("#md_add_fac_nhealth").modal("show");
						} else if (temp["form"] == 'load_site') {
							$("#show_fac_nhealth_time").empty();
							for (var i = 0; i < temp['count']; i++) {
								var Str = "<button class='btn btn-block btn-mylight font-weight-bold' onclick='show_hpt_time(\"" + temp['HptCode'][i] + "\")'>" + temp['HptName'][i] + "</button>";

								$("#show_fac_nhealth_time").append(Str);
							}

							$("#md_fac_nhealth").modal("show");

						} else if (temp["form"] == 'load_site_time') {
							if (temp['count'] > 0) {
								$("#show_hpt_fac_time").empty();
								for (var i = 0; i < temp['count']; i++) {

									var Str = "<div class='input-group my-3'><div class='input-group-prepend '><label class='input-group-text' style='width:180px;' >" + temp[i]['HptCode'] + "</label></div>";
									Str += "<input type='text' class='form-control ' value='" + temp[i]['SendTime'] + "' readonly >";
									Str += "<div class='input-group-append'><span class='input-group-text'><?php echo $genarray['minute'][$language]; ?></span></div></div>";
									$("#show_hpt_fac_time").append(Str);
								}
								$("#md_fac_send_time").modal("show");
							}
						} else if (temp["form"] == 'show_fac') {
							$("#show_fac_nhealth_time").empty();
							for (var i = 0; i < temp['count']; i++) {
								var Str = "<button class='btn btn-block btn-mylight font-weight-bold' onclick='show_fac_time(\"" + temp['Fcode'][i] + "\")'>" + temp['Fname'][i] + "</button>";

								$("#show_fac_nhealth_time").append(Str);
							}

							$("#md_fac_nhealth").modal("show");
							$("#btn_edit_fac_send_time").attr("onclick", "EditFacNhealth(" + temp['count'] + ")");
							$("#btn_edit_fac_send_time").prop("disabled", false);
							$("#show_hpt_fac").hide();
							$("#show_fac_nhealth_time").show();

						} else if (temp["form"] == 'show_fac_time') {
							Del_fac = [];
							Del_time = [];
							$("#fac_time_list").empty();
							for (var i = 0; i < temp['count']; i++) {
								var list_time = "list_time" + i;
								var Str = "<div id='" + list_time + "' class='d-flex'><div class='text-center mr-2' style='width:75px;'><?php echo $array['round'][$language]; ?> " + (i + 1) + "</div>";
								Str += "<input type='time' class='form-control text-center mb-3 fac_time' data-fac='" + temp['FacCode'] + "' data-time='" + temp['SendTime'][i] + "' value='" + temp['SendTime'][i] + "'>";
								Str += "<button onclick='del_list_time(\"" + list_time + "\",\"" + temp['FacCode'] + "\",\"" + temp['SendTime'][i] + "\")' class='btn btn-danger ml-2 px-3 py-2' style='height:37px;'><i class='fas fa-times'></i></button></div>";

								$("#fac_time_list").append(Str);
							}

							$("#btn_to_hpt").remove();
							$("#md_fac_nhealth").modal("hide");
							$("#md_fac_time").modal("show");

						} else if (temp["form"] == 'show_hpt_time') {
							$("#fac_time_list").empty();
							for (var i = 0; i < temp['count']; i++) {
								var text = temp['SendTime'][i];
								var Str = "<div class='d-flex'><div class='text-center mr-2' style='width:75px;'><?php echo $array['round'][$language]; ?> " + (i + 1) + "</div>";
								Str += "<input type='time' class='form-control text-center mb-3 bg-white' value='" + text + "' disabled>";
								Str += "</div>";

								$("#fac_time_list").append(Str);
							}

							$("#btn_save_edit").remove();
							$("#md_fac_nhealth").modal("hide");
							$("#md_fac_time").modal("show");

						} else if (temp["form"] == 'save_edit_factime') {
							$("#md_fac_time").modal("hide");
							$("#md_fac_nhealth").modal("show");

						} else if (temp["form"] == 'save_lang') {
							swal({
								title: '',
								text: lang,
								type: 'success',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Error!!'
							})
							setTimeout('window.location.href = "menu.php"', 1000);
						} else if (temp["form"] == 'AddFacNhealth') {
							$("#md_add_fac_nhealth").modal("hide");
						} else if (temp["form"] == 'EditFacNhealth') {
							$("#md_fac_nhealth").modal('hide');
							swal({
								title: '',
								text: lang,
								type: 'success',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 1000,
							})
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'AddFacNhealth') {
							var Title = "<?php echo $array['havetime'][$language]; ?>";
							var Text = "";
							var Type = "warning";
							AlertError(Title, Text, Type);
						} else {
							swal({
								title: '',
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
				<img src="../img/logo.png" width="156" height="40" />
			</div>
			<div>
				<img src="../img/nlinen.png" width="95" height="14" />
			</div>
		</div>

		<div class="modal-body text-center">
			<div class="row">
				<div class="col-auto"><?php echo $array['settinglang'][$language]; ?><i class="fas fa-lg fa-language ml-2"></i></div>
				<div class="col">
					<hr>
				</div>
			</div>
			<div id="set_language">
				<div class="input-group mb-5">
					<div class="input-group-prepend">
						<label class="input-group-text"><?php echo $array['changelang'][$language]; ?></label>
					</div>
					<select id="lang" class="custom-select">
						<option value="th"><?php echo $array['th'][$language]; ?></option>
						<option value="en"><?php echo $array['en'][$language]; ?></option>
					</select>
				</div>
			</div>

			<div id="set_fac_nhealth" <?php if ($_SESSION['PmID'] == 2) {
																	echo hidden;
																} ?>>
				<div class="row">
					<div class="col-auto"><?php echo $array['settingTime'][$language]; ?><i class="fas fa-truck ml-2"></i></div>
					<div class="col">
						<hr>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-12">
						<button onclick="load_site_fac()" class="btn btn-block btn-outline-primary mb-2" <?php if ($_SESSION['PmID'] == 4) {
																																																echo hidden;
																																															} ?>>
							<i class="fas fa-plus mr-2"></i><?php echo $array['addTime'][$language]; ?>
						</button>
					</div>
					<div class="col-md-6 col-sm-6 col-12">
						<button onclick="show_fac()" class="btn btn-block btn-outline-primary" <?php if ($_SESSION['PmID'] == 4) {
																																											echo hidden;
																																										} ?>>
							<i class="fas fa-edit mr-2"></i><?php echo $array['editTime'][$language]; ?>
						</button>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-12">
						<button onclick="load_site()" class="btn btn-block btn-outline-primary mb-2" <?php if ($_SESSION['PmID'] == 3) {
																																														echo hidden;
																																													} ?>>
							<i class="far fa-clock mr-2"></i><?php echo $array['showTime'][$language]; ?>
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-footer text-center">
			<div class="row w-100 d-flex align-items-center">
				<div class="col-12 text-right">
					<button id="btn_save" onclick="save()" type="button" class="btn btn-primary"><i class="fas fa-save mr-2"></i><?php echo $genarray['save'][$language]; ?></button>
				</div>
			</div>
		</div>
	</div>

	<!--------------------------------------- Modal Factory to N health --------------------------------------->
	<div class="modal fade" id="md_add_fac_nhealth" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-plus mr-2"></i><?php echo $array['addTime'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div class="form-row">
						<div class="col-md-12 col-sm-12 col-12">
							<div class="input-group mb-1">
								<div class="input-group-prepend">
									<label class="input-group-text" style="width:80px;"><?php echo $array['factory'][$language]; ?></label>
								</div>
								<select id="from_fac" class="custom-select"></select>
							</div>
						</div>
						<!-- <div class="col-md-6 col-sm-12 col-12">
                        <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <label class="input-group-text" style="width:60px;">ไปยัง</label>
                            </div>
                            <select id="to_hpt" class="custom-select"></select>                   
                        </div>
                    </div> -->
					</div>
					<div class="input-group my-3">
						<div class="input-group-prepend">
							<label class="input-group-text" style="width:80px;"><?php echo $array['Time'][$language]; ?></label>
						</div>
						<input id="new_send_time" onchange="enable_add()" type="time" class="form-control text-center" value="00:00">
						<div class="input-group-append">
							<span class="input-group-text"><?php echo $array['oclock'][$language]; ?></span>
						</div>
					</div>

				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button id="AddFacNhealth" onclick="AddFacNhealth()" type="button" class="btn btn-primary mx-3"><?php echo $genarray['save'][$language]; ?></button>
							<button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_fac_time" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-edit mr-2"></i><?php echo $array['editTime'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div id="fac_time_list"></div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button id="btn_save_edit" onclick="save_edit_factime()" type="button" class="btn btn-primary mx-3"><?php echo $genarray['save'][$language]; ?></button>
							<button id="btn_to_hpt" onclick="time_to_hpt()" type="button" class="btn btn-primary mx-3"><i class="fas fa-arrow-circle-left mr-1"></i> <?php echo $genarray['back'][$language]; ?></button>
							<button id="btn_cancel_edit" type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['cancel'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_fac_nhealth" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-edit mr-2"></i><?php echo $array['editTime'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div id="show_hpt_fac">
						<!-- <button onclick='' class='btn btn-mylight btn-block' style='align-items: center !important;'>
                        <div class='row'><div class='col-6'><div class='row d-flex justify-content-end'><div style='width:200px !important;'>
                        <img class='hpt_img' src='../img/logo_1.png'/></div></div></div><div class='col-6 d-flex justify-content-start align-items-center' style='padding-left:0;color:black;'>
                        <img src='../img/H-Line.png' height='40' style='margin-right:1rem;'/><div class='hpt_name'>444444444</div></div></div></button> -->
					</div>
					<div id="show_fac_nhealth_time"></div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="md_fac_send_time" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog  modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title font-weight-bold text-truncate"><i class="fas fa-edit mr-2"></i><?php echo $array['showTime'][$language]; ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div id="show_hpt_fac_time">
					</div>
				</div>
				<div class="modal-footer text-center">
					<div class="row w-100 d-flex align-items-center m-0">
						<div class="col-12 text-center">
							<button type="button" class="btn btn-secondary mx-3" data-dismiss="modal"><?php echo $genarray['close'][$language]; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

</html>