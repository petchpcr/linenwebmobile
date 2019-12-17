<script>
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
		} else {
			var data = {
				'Confirm': num,
				'STATUS': 'logout'
			};
			senddata(JSON.stringify(data));
		}
	}

	function updateOnlineStatus(event) {
		window.location.assign("../index.html");
		console.log("Disconnect Internet!");
	}
	window.addEventListener('offline', updateOnlineStatus);

</script>