<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$HptCode = $_SESSION['HptCode'];
$PM = $_SESSION['PmID'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$language = $_SESSION['lang'];
$xml = simplexml_load_file('../xml/Language/menu_lang.xml');
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

	<title><?php echo $array['title'][$language]; ?></title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>

	<script>
		$(document).ready(function(e) {
			sendTimeout();
		});

		// function
		function menu_click(menu) {
			if (menu == 'tools') {
				window.location.href = 'setting.php?';

			} else if (menu == 'dirty') {
				window.location.href = 'dirty.php?siteCode=<?php echo $HptCode; ?>&Menu=dirty';
			} else if (menu == 'clean') {
				window.location.href = 'clean.php?siteCode=<?php echo $HptCode; ?>&Menu=clean';
			} else if (menu == 'qc') {
				window.location.href = 'qc.php?siteCode=<?php echo $HptCode; ?>&Menu=qc';
			} else if (menu == 'track') {
				window.location.href = 'dirty_to_track.php?siteCode=<?php echo $HptCode; ?>&Menu=track';
			} else if (menu == 'shelfcount') {
				window.location.href = 'shelfcount.php?siteCode=<?php echo $HptCode; ?>&Menu=shelfcount';
			} else if (menu == 'newlinentable') {
				window.location.href = 'new_linen_item.php?siteCode=<?php echo $HptCode; ?>&Menu=newlinentable';
			} else {
				window.location.href = 'hospital.php?Menu=' + menu;
			}
		}

		function sendTimeout() {
			console.log(<?php echo $_SESSION['TimeOut']; ?>);
			Android.setTimeout(<?php echo $_SESSION['TimeOut']; ?>);
		}

		function back() {
			swal({
				title: '',
				text: 'Logout',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				showConfirmButton: true,
			}).then(function() {
				logout(1);
			});
		}
		// end function

		// display
		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/menu.php';
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
						if (temp["form"] == 'logout') {
							window.location.href = '../index.html';
						}
					} else if (temp['status'] == "failed") {
						swal({
							title: '',
							text: '',
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
	<section data-role="page">

		<header data-role="header">
			<div class="head-bar d-flex justify-content-between">
				<div style="width:139.14px;"></div>
				<div class="head-text text-truncate font-weight-bold align-self-center"><?php echo $UserFName ?> <?php echo "[ ".$Per." ]" ?></div>
				<div class="text-right" style="width:139.14px;">
					<button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
				</div>
			</div>
		</header>
		<div data-role="content">
			<div align="center" style="margin:1rem 0;"><img src="../img/Linen4.0.png" width="230" height="40" /></div>
			<div class="text-center text-truncate font-weight-bold my-4" style="font-size:30px;"><?php echo $array['menu'][$language]; ?></div>
			<div id="hospital"></div>
		</div>

		<div class="row w-100 m-0">
			<?php
			if ($PM == 4) {
				echo '<div class="my-col-menu">
                        <button onclick="menu_click(' . "'factory'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/Factory.png">
                            <div class="text-truncate">' . $array["factory"][$language] . '</div>
                        </button>
                    </div>
                    ';
			} else {
				echo '<div class="my-col-menu">
                    <button onclick="menu_click(' . "'dirty'" . ')" type="button" class="btn btn-mylight btn-block">
                        <img src="../img/tshirt.png">
                        <div class="text-truncate">' . $array["dirty"][$language] . '</div>
                        </button>
					</div>
					
                    <div class="my-col-menu">
                        <button onclick="menu_click(' . "'newlinentable'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/fabric.png">
                            <div class="text-truncate">' . $array["newLinen"][$language] . '</div>
                        </button>
					</div>
					
                    <div class="my-col-menu">
                        <button onclick="menu_click(' . "'track'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/tracking.png">
                            <div class="text-truncate">' . $array["tracking"][$language] . '</div>
                        </button>
                    </div>
                    <div class="my-col-menu">
                        <button onclick="menu_click(' . "'clean'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/laundry.png">
                            <div class="text-truncate">' . $array["clean"][$language] . '</div>
                        </button>
                    </div>
                    <div class="my-col-menu">
                        <button onclick="menu_click(' . "'qc'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/QC.png">
                            <div class="text-truncate">' . $array["QC"][$language] . '</div>
                        </button>
                    </div>
                    <!-- <div class="my-col-menu">
                        <button onclick="menu_click(' . "'shelfcount'" . ')" type="button" class="btn btn-mylight btn-block">
                            <img src="../img/storage.png">
                            <div class="text-truncate">' . $array["shelfcount"][$language] . '</div>
                        </button>
					</div> --!>
					
                    ';
			}

			?>
			<div class="my-col-menu">
				<button onclick="menu_click('tools')" type="button" class="btn btn-mylight btn-block">
					<img src="../img/Tools.png">
					<div class="text-truncate"><?php echo $array["setting"][$language]; ?></div>
				</button>
			</div>
		</div>
	</section>
</body>

</html>