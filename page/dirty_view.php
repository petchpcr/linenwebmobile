<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$siteCode = $_GET['siteCode'];
$Menu = $_GET['Menu'];
$DocNo = $_GET['DocNo'];
$From = $_GET['From'];
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/clean&dirty_view_lang.xml');
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
	<title><?php echo $genarray['titledirty'][$language] . $array['title'][$language]; ?></title>


	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	if ($From == "") {
		$From = "dirty";
	}
	?>
	<script>
		var DocNo = "<?php echo $DocNo ?>";
		var Menu = "<?php echo $Menu ?>";
		var siteCode = "<?php echo $siteCode ?>";

		$(document).ready(function(e) {
			// load_site();
			load_doc();
		});

		function load_site() {
			var From = "<?php echo $From ?>";
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'load_site'
			};
			senddata(JSON.stringify(data));
		}

		function load_doc() {
			var From = "<?php echo $From ?>";
			var data = {
				'DocNo': DocNo,
				'From': From,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function view_dep(Item, Request) {
			var data = {
				'DocNo': DocNo,
				'Item': Item,
				'Request': Request,
				'STATUS': 'view_dep'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
			var From = '<?php echo $From; ?>';
			if (From == "dirty") {
				window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu;
			} else {
				window.location.href = 'new_linen_item.php?siteCode=' + siteCode + '&Menu=' + Menu;
			}

		}

		function logout(num) {
			if (num == 0) {
				var data = {
					'Confirm': 1,
					'STATUS': 'logout'
				};
				senddata(JSON.stringify(data));
			} else if (num == 1) {
				swal({
					title: '<?php echo $genarray['logout'][$language]; ?>',
					text: '<?php echo $genarray['wantlogout'][$language]; ?>',
					type: 'question',
					showCancelButton: true,
					showConfirmButton: true,
					cancelButtonText: '<?php echo $genarray['isno'][$language]; ?>',
					confirmButtonText: '<?php echo $genarray['yes'][$language]; ?>',
					reverseButton: true,
				}).then(function() {
					var data = {
						'Confirm': num,
						'STATUS': 'logout'
					};
					senddata(JSON.stringify(data));
				});
			}
		}

		function movetoAddItem() {
			var Userid = '<?php echo $Userid; ?>';
			var DepCode = $("#add_doc").data("depcode");
			window.location.href = 'add_items_dirty.php?siteCode=' + siteCode + '&DocNo=' + DocNo + '&Menu=' + Menu + '&user=' + Userid;
		}

		function CancelDoc() {
			swal({
				title: '<?php echo $genarray['confirmCanceldocno'][$language]; ?>',
				text: '<?php echo $genarray['wantcanceldoc'][$language]; ?>',
				type: 'question',
				showCancelButton: true,
				showConfirmButton: true,
				cancelButtonText: '<?php echo $genarray['isno'][$language]; ?>',
				confirmButtonText: '<?php echo $genarray['yes'][$language]; ?>',
				reverseButton: true,
			}).then(function() {
				var data = {
					'DocNo': DocNo,
					'STATUS': 'CancelDoc'
				};
				senddata(JSON.stringify(data));
			});
		}

		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/dirty_view.php';
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
							$("#add_doc").attr("data-depcode", temp['DepCode']);
							$("#HptName").val(temp['HptName']);
							$("#DepName").val(temp['DepName']);
						} else if (temp["form"] == 'load_doc') {
							$("#HptName").val(temp['HptName']);
							$("#FName").val(temp['FName']);
							$("#FacName").val(temp['FacName']);
							$("#RoundTime").val(temp['RoundTime']);
							$("#Date").val(temp['xdate'] + " - " + temp['xtime']);
							var Weight = temp['Total'] + " <?php echo $array['KG'][$language] ?>";
							$("#Weight").val(Weight);
							if (temp['IsStatus'] == 9) {
								$("#btn_create").prop("disabled", true);
								$("#btn_cancel").prop("disabled", true);
							}
							for (var i = 0; i < temp['cnt']; i++) {
								var num = i + 1;
								var Str = "<tr><td><div class='row'>";
								Str += "<div scope='row' class='col-3 d-flex align-items-center justify-content-center'>" + num + "</div>";
								Str += "<div class='col-6'><div class='row'><div class='col-12 text-truncate font-weight-bold mb-1'>" + temp[i]['ItemName'] + "</div>";
								Str += "<div class='col-12 text-black-50 mb-1'><?php echo $array['numberSize'][$language]; ?> " + temp[i]['Qty'] + " / <?php echo $array['weight'][$language]; ?> " + temp[i]['Weight'] + " </div></div></div>";
								Str += "<div class='col-3 justify-content-center d-flex align-items-center'><button onclick='view_dep(\"" + temp[i]['ItemCode'] + "\",\"" + temp[i]['RequestName'] + "\")' class='btn btn-info'>แผนก</button></div></div></td></tr>";

								$("#item").append(Str);
							}
						} else if (temp["form"] == 'view_dep') {
							$("#item_name").text(temp['ItemName']);
							$('#show_dep').empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var Str = "<div class='btn btn-block alert alert-info py-1 px-3 mb-2'>";
								Str += "<div class='d-flex align-items-center col-12 text-truncate text-left font-weight-bold px-0'>";
								Str += "<div class='mr-auto'>" + temp['DepName'][i] + "</div>";
								Str += "<input type='text' class='form-control text-center bg-white ml-2' style='max-width:80px;' value='" + temp['Qty'][i] + "' disabled>";
								Str += "<input type='text' class='form-control text-center bg-white mx-2' style='max-width:80px;' value='" + temp['Weight'][i] + "' disabled>";
								Str += "</div>";
								Str += "</div>";
								$('#show_dep').append(Str);
							}
							$('#md_view_dep').modal('show');

						} else if (temp["form"] == 'CancelDoc') {
							window.location.href = 'dirty.php?siteCode=' + siteCode + '&Menu=' + Menu;

						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';

						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_doc') {
							$(".btn.btn-mylight.btn-block").remove();
							swal({
								title: '',
								type: 'warning',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000,
								confirmButtonText: 'Data found'
							})

						} else if (temp["form"] == 'CancelDoc') {
							swal({
								title: 'Cancel document error !',
								type: 'error',
								showCancelButton: false,
								confirmButtonColor: '#3085d6',
								cancelButtonColor: '#d33',
								showConfirmButton: false,
								timer: 2000
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

		<div class="row justify-content-center">
			<div class="col-lg-9 col-md-10 col-sm-12 mb-3">
				<div class="row">
					<div class="col-md-6 col-12 text-left">
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['docno'][$language] ?></span>
							</div>
							<input type="text" class="form-control bg-white" value="<?php echo $DocNo; ?>" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['Hospital'][$language] ?></span>
							</div>
							<input type="text" id="HptName" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['factory'][$language] ?></span>
							</div>
							<input type="text" id="FacName" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['roundtime'][$language] ?></span>
							</div>
							<input type="text" id="RoundTime" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
					</div>

					<div class="col-md-6 col-12 text-left">
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $array['userEditer'][$language] ?></span>
							</div>
							<input type="text" id="FName" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['date'][$language] ?> - <?php echo $genarray['time'][$language] ?></span>
							</div>
							<input type="text" id="Date" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-2">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $array['weightSum'][$language] ?></span>
							</div>
							<input type="text" id="Weight" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row justify-content-center mb-5 px-3">
			<table class="table table-hover col-lg-9 col-md-10 col-sm-12">
				<thead>
					<tr class="bg-primary text-white">
						<th scope="col">
							<div class="row">
								<div class="col-3 text-center"><?php echo $array['no'][$language]; ?></div>
								<div class="col-6 text-left"><?php echo $array['list'][$language]; ?></div>
								<div class="col-3 text-center"><?php echo $genarray['department'][$language]; ?></div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="item"></tbody>
			</table>
		</div>

		<div id="add_doc" data-depcode="" class="fixed-bottom d-flex justify-content-center bg-white">
			<div class="col-lg-9 col-md-10 col-sm-12">

				<div class="row">
					<div class="col-12 d-flex justify-content-center py-2">
						<button id="btn_create" class="btn btn-create btn-block mr-4" type="button" style="max-width:250px;" onclick="movetoAddItem()">
							<i class="fas fa-plus mr-1"></i><?php echo $array['addList'][$language]; ?>
						</button>
						<button id="btn_cancel" class="btn btn-danger btn-block mt-0" type="button" style="max-width:250px;" onclick="CancelDoc()">
							<i class="fas fa-times mr-1"></i><?php echo $genarray['canceldocno'][$language]; ?>
						</button>
					</div>
				</div>

			</div>
		</div>

	</div>

	<!-- Modal -->
	<div class="modal fade" id="md_view_dep" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="item_name"></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body text-center" style="max-height: calc(100vh - 210px);overflow-y: auto;">
					<div class="bg-primary text-white d-flex mb-2 mx-0" style="border-radius:0.25rem;">
						<div class="text-left w-100 py-0 pr-0 pl-4"><?php echo $genarray['department'][$language]; ?></div>
						<div class="text-left p-0" style="width:140px;"><?php echo $genarray['qty'][$language]; ?></div>
						<div class="text-left p-0" style="width:145px;"><?php echo $genarray['weight'][$language]; ?></div>
					</div>
					<div id="show_dep"></div>
				</div>

				<div class="modal-footer text-center">
					<div class="w-100 d-flex justify-content-center m-0">
						<button id="btn_add_items" data-dismiss="modal" type="button" class="btn btn-secondary m-2"><?php echo $genarray['close'][$language]; ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>

</html>