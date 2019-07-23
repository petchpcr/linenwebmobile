<?php
    session_start();
    $Userid = $_SESSION['Userid'];
    $UserName = $_SESSION['Username'];
    $UserFName = $_SESSION['FName'];
    if($Userid==""){
      header("location:../index.html");
    }
    $language = $_SESSION['lang'];
    $xml = simplexml_load_file('../xml/Language/menu_lang.xml');
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    $genxml = simplexml_load_file('../xml/Language/general_lang.xml');
    $json = json_encode($genxml);
    $genarray = json_decode($json,TRUE);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?php echo $array['title'][$language]; ?></title>
    
	<script src="../js/jquery-3.3.1.min.js"></script>
    
	<link rel="shortcut icon" href="../favicon.ico">
	<link rel="stylesheet" href="../fontawesome/css/all.min.css">
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.css">
	<link rel="stylesheet" href="../css/themes/default/nhealth.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,700">

    <script src="../js/gijgo.min.js" type="text/javascript"></script>
    <link href="../css/gijgo.min.css" rel="stylesheet" type="text/css"/>

    <script src="../dist/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../dist/css/sweetalert2.min.css">
    <script>
        function menu_click(num) {
            window.location.href='hospital.php?Menu='+num;
        }

        function logout(num){
            var data = {
                'Confirm': num,
                'STATUS': 'logout'
            };
            senddata(JSON.stringify(data));
        }

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
                success: function (result) {
                try {
                    var temp = $.parseJSON(result);
                } catch (e) {
                    console.log('Error#542-decode error');
                }

                if (temp["status"] == 'success') {
                    if(temp["form"] == 'logout'){
                        window.location.href='../index.html';
                    }
                } else if (temp['status'] == "failed") {
                    swal({
                    title: '',
                    text: '<?php echo $genarray['NotFoundHpt'][$language]; ?>',
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
    </script>
</head>

<body>
    <section data-role="page">

        <header data-role="header">
            <div class="head-bar d-flex justify-content-between">
                <div style="margin-right:75px"></div >
                <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName?></div>
                <button  onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button >
            </div>
        </header>
        <div data-role="content" style="font-family:sans-serif;">
            <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45"/></div>
            <div class="text-center my-4"><h4 class="text-truncate"><?php echo $array['menu'][$language]; ?></h4></div>
            <div id="hospital"></div>
        </div>

        <div class="row w-100 m-0">
            <div class="my-col-menu">
                <button onclick="menu_click(1)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/tshirt.png">
                    <div class="text-truncate"><?php echo $array['dirty'][$language]; ?></div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(2)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/Factory.png">
                    <div class="text-truncate"><?php echo $array['factory'][$language]; ?></div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(3)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/laundry.png">
                    <div class="text-truncate"><?php echo $array['clean'][$language]; ?></div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(4)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/QC.png">
                    <div class="text-truncate"><?php echo $array['QC'][$language]; ?></div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(5)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/Report.png">
                    <div class="text-truncate"><?php echo $array['report'][$language]; ?></div>
                </button>
            </div>
            <div class="my-col-menu">
                <button onclick="menu_click(6)" type="button" class="btn btn-mylight btn-block">
                    <img src="../img/Tools.png">
                    <div class="text-truncate"><?php echo $array['setting'][$language]; ?></div>
                </button>
            </div>
        </div>
	</section>			
</body>
</html>
