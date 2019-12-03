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
	<title><?php echo $genarray['titleclean'][$language] . $array['title'][$language]; ?></title>


	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		var DocNo = '<?php echo $DocNo ?>';
		var Menu = '<?php echo $Menu ?>';
		var siteCode = "<?php echo $siteCode ?>";

		$(document).ready(function(e) {
			load_doc();
		});

		// function
		function load_doc() {
			var data = {
				'siteCode': siteCode,
				'DocNo': DocNo,
				'Menu': Menu,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function back() {
				window.location.href = Menu + '.php?siteCode=' + siteCode + '&Menu=' + Menu;
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
			var RefDocNo = $("#RefDocNo").val();
			window.location.href = 'add_items_rewash.php?siteCode=' + siteCode + '&DocNo=' + DocNo + '&Menu=' + Menu + '&user=' + Userid + '&DepCode=' + DepCode + '&RefDocNo=' + RefDocNo  + '&NotDelDetail=1';
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
					'Menu': Menu,
					'STATUS': 'CancelDoc'
				};
				senddata(JSON.stringify(data));
			});
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/rewash_view.php';
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
							$("#add_doc").attr("data-depcode", temp['DepCode']);
							$("#HptName").val(temp['HptName']);
							$("#DepName").val(temp['DepName']);
							$("#RefDocNo").val(temp['RefDocNo']);
							var FName = temp['FName'];
							$("#FName").val(FName);
							$("#Date").val(temp['xdate'] + " - " + temp['xtime']);
							var Weight = temp['Total'] + " <?php echo $array['KG'][$language]; ?>";
							$("#Weight").val(Weight);
							if (temp['IsStatus'] > 1) {
								$("#btn_create").prop("disabled", true);
								$("#btn_cancel").prop("disabled", true);
							}
							for (var i = 0; i < temp['cnt']; i++) {
								var num = i + 1;
								var Str = "<tr><td><div class='row'>";
								Str += "<div scope='row' class='col-5 d-flex align-items-center justify-content-center'>" + num + "</div>";
								Str += "<div class='col-7'><div class='row'><div class='col-12 text-truncate font-weight-bold mb-1'>" + temp[i]['ItemName'] + "</div>";
								Str += "<div class='col-12 text-black-50 mb-1'><?php echo $array['numberSize'][$language]; ?> " + temp[i]['Qty'] + " / <?php echo $array['weight'][$language]; ?> " + temp[i]['Weight'] + " </div></div></div></div></td></tr>";

								$("#item").append(Str);
							}
						} else if (temp["form"] == 'CancelDoc') {
							window.location.href = 'clean.php?siteCode=' + siteCode + '&Menu=' + Menu;

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
		<div class="row justify-content-center">
			<div class="col-lg-9 col-md-10 col-sm-12 mb-3">
				<div class="row">
					<div class="col-sm-6 col-12 text-left">
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
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['department'][$language] ?></span>
							</div>
							<input type="text" id="DepName" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
					</div>
					<div class="col-sm-6 col-12 text-left">
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $array['userEditer'][$language] ?></span>
							</div>
							<input type="text" id="FName" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $array['referentDocument'][$language] ?></span>
							</div>
							<input type="text" id="RefDocNo" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>
						<div class="input-group mb-1">
							<div class="input-group-prepend">
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['date'][$language] ?> - <?php echo $genarray['time'][$language] ?></span>
							</div>
							<input type="text" id="Date" class="form-control bg-white" style="color:#1659a2;" readonly>
						</div>

					</div>
					<div class="col-md-6 col-sm-12 col-12 text-left">
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
								<div class="col-5 text-center"><?php echo $array['no'][$language]; ?></div>
								<div class="col-7 text-left"><?php echo $array['list'][$language]; ?></div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="item">

				</tbody>
			</table>
		</div>


		<div id="add_doc" data-depcode="" class="fixed-bottom d-flex justify-content-center py-2 bg-white">
			<div class="col-lg-9 col-md-10 col-sm-12">

				<div class="row">
					<div class="col-12 d-flex justify-content-center">
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

</body>

</html>