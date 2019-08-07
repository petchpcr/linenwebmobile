<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
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
        $(document).ready(function(e) {
            $("#lang").val('<?php echo $language; ?>');
        });

        function back() {
            window.location.href = "menu.php";
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
                    if($("#lang").val() == "th"){
                        var lang = '<?php echo $genarray['savesuccess']["th"]; ?>';
                    }else{
                        var lang = '<?php echo $genarray['savesuccess']["en"]; ?>';
                    }
                    if (temp["status"] == 'success') {
                        if (temp["form"] == 'save_lang') {
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
                            setTimeout( 'window.location.href = "menu.php"', 2000 );
                        } else if (temp["form"] == 'logout') {
                            window.location.href = '../index.html';
                        }
                    } else if (temp['status'] == "failed") {
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
            <button onclick="back()" class="head-btn btn-light"><i class="fas fa-arrow-circle-left mr-1"></i><?php echo $genarray['back'][$language]; ?></button>
            <div class="head-text text-truncate align-self-center"><?php echo $UserName ?> : <?php echo $UserFName ?></div>
            <button onclick="logout(1)" class="head-btn btn-dark" role="button"><?php echo $genarray['logout'][$language]; ?><i class="fas fa-power-off ml-1"></i></button>
        </div>
    </header>
    <div class="px-3">
        <div align="center" style="margin:1rem 0;"><img src="../img/logo.png" width="220" height="45" /></div>
        <div class="modal-body text-center">
            <div class="input-group my-3">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="inputGroupSelect01"><?php echo $array['changelang'][$language]; ?></label>
                    </div>
                    <select id="lang" class="custom-select">
                        <option value="th" selected><?php echo $array['th'][$language]; ?></option>
                        <option value="en" selected><?php echo $array['en'][$language]; ?></option>
                    </select>
                </div>
        </div>
        <div class="modal-footer text-center">
            <div class="row w-100 d-flex align-items-center">
                <div class="col-12 text-right">
                    <button id="btn_save" onclick="save()" type="button" class="btn btn-primary"><?php echo $genarray['save'][$language]; ?></button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>