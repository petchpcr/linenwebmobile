<?php
session_start();
$Userid = $_SESSION['Userid'];
$UserName = $_SESSION['Username'];
$UserFName = $_SESSION['FName'];
$Per = $_SESSION['Permission'];
if ($Userid == "") {
	header("location:../index.html");
}
$form_out = $_GET['form_out'];
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
	<title></title>

	<?php
	require 'script_css.php';
	require 'logout_fun.php';
	?>
	<link rel="stylesheet" href="../css/signature-pad2.css">
	<script>
		var DocNo = "<?php echo $DocNo ?>";
		var Menu = "<?php echo $Menu ?>";
		var siteCode = "<?php echo $siteCode ?>";
		var sign_funciton = "";

		var form_out = '<?php echo $form_out ?>';
		if (form_out == 1) {
			var txt_form_out = "&form_out=1";
		} else {
			var txt_form_out = "";
		}

		$(document).ready(function(e) {
			load_doc();
			$('#ModalSign').on('shown.bs.modal', function () {
				resizeCanvas();
			})
			$('#ModalSign').on('hidden.bs.modal', function () {
				signaturePad.clear();
			})
		});

		function load_doc() {
			var data = {
				'DocNo': DocNo,
				'STATUS': 'load_doc'
			};
			senddata(JSON.stringify(data));
		}

		function md_signature(FNC) {
			sign_funciton = FNC;
			$("#ModalSign").modal('show');
		}

		function save_sign(dataURL) {
			$("#ModalSign").modal('hide');
			swal({
				title: 'Please wait...',
				text: 'Processing',
				allowOutsideClick: false
			})
			swal.showLoading();
			$.ajax({
				url: "../process/signature_sum.php",
				method: "POST",
				data: {
					IsMenu: "signdoc_return_doc_detail",
					DocNo: DocNo,
					SignCode: dataURL,
					sign_funciton: sign_funciton
				},
				success: function(data) {
					swal.hideLoading();
					swal({
						title: '',
						text: 'success',
						type: 'success',
						showCancelButton: false,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						timer: 1000,
						confirmButtonText: 'Ok',
						showConfirmButton: false
					}).then(function() {
							
						},
						function(dismiss) {
							swal.close();
							load_doc();
					})
				}
			});
		}

		function back() {
			window.location.href = 'signdoc_return_doc.php?siteCode=' + siteCode + txt_form_out;
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

		function senddata(data) {
			var form_data = new FormData();
			form_data.append("DATA", data);
			var URL = '../process/signdoc_return_doc_detail.php';
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
							$("#HptName").val(temp['HptName']);
							$("#FName").val(temp['FName']);
							$("#DepName").val(temp['DepName']);
							$("#Date").val(temp['xdate'] + " - " + temp['xtime']);
							var Weight = temp['Total'] + " <?php echo $array['KG'][$language] ?>";
							$("#Weight").val(Weight);
							if (temp['SignHospital'] != null) {
								$("#btn_SignHospital").remove();
							}
							if (temp['SignNH'] != null) {
								$("#btn_SignNH").remove();
							}
							$("#item").empty();
							for (var i = 0; i < temp['cnt']; i++) {
								var num = i + 1;
								var Str = "<tr><td><div class='row'>";
								Str += "<div scope='row' class='col-5 d-flex align-items-center justify-content-center'>" + num + "</div>";
								Str += "<div class='col-7'><div class='row'><div class='col-12 text-truncate font-weight-bold mb-1'>" + temp[i]['ItemName'] + "</div>";
								Str += "<div class='col-12 text-black-50 mb-1'><?php echo $array['numberSize'][$language]; ?> " + temp[i]['Qty'] + " / <?php echo $array['weight'][$language]; ?> " + temp[i]['Weight'] + " </div></div></div></div></td></tr>";

								$("#item").append(Str);
							}
						} else if (temp["form"] == 'logout') {
							window.location.href = '../index.html';

						}
					} else if (temp['status'] == "failed") {
						if (temp["form"] == 'load_doc') {
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
								<span class="input-group-text" style="width:100px;"><?php echo $genarray['fromdep'][$language] ?></span>
							</div>
							<input type="text" id="DepName" class="form-control bg-white" style="color:#1659a2;" readonly>
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
								<div class="col-5 text-center"><?php echo $array['no'][$language]; ?></div>
								<div class="col-7 text-left"><?php echo $array['list'][$language]; ?></div>
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
						<button id="btn_SignHospital" class="btn btn-success btn-block mr-4" style="max-width:250px;" onclick="md_signature('SignHospital')">
							<i class="fas fa-signature mr-1"></i><?php echo $array['signnurse'][$language]; ?>
						</button>
						<button id="btn_SignNH" class="btn btn-success btn-block mt-0" style="max-width:250px;" onclick="md_signature('SignNH')">
							<i class="fas fa-signature mr-1"></i><?php echo $array['signnh'][$language]; ?>
						</button>
					</div>
				</div>

			</div>
		</div>

	</div>

	<!-- Modal -->
	<div class="modal fade" id="ModalSign" tabindex="-1" role="dialog" aria-hidden='false'>
		<div class="modal-dialog" role="document">
			<div class="modal-content" style="background-color:#fff;">
				<div class="modal-body p-0">

					<div id="maxxx" onselectstart="return false">
						<div id="signature-pad" class="signature-pad">
							<div class="signature-pad--body">
								<canvas></canvas>
							</div>
							<div class="signature-pad--footer">
								<div class="signature-pad--actions">
									<div>
										<button type="button" class="button clear btn btn-secondary mr-2" data-action="clear"><?php echo $genarray['clear'][$language]; ?></button>
										<button type="button" class="button" data-action="change-color" hidden>Change color</button>
										<button type="button" class="button btn btn-warning" data-action="undo" hidden>ย้อนกลับ</button>

									</div>
									<div>
										<button type="button" class="button save" data-action="save-png" hidden>Save as PNG</button>
										<button type="button" class="button save" data-action="save-jpg" hidden>Save as JPG</button>

										<button type="button" class="button save btn btn-primary" data-action="save-svg"><?php echo $genarray['save'][$language]; ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

	<script>
		/*!
		 * Signature Pad v3.0.0-beta.3 | https://github.com/szimek/signature_pad
		 * (c) 2018 Szymon Nowak | Released under the MIT license
		 */

		(function(global, factory) {
			typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
				typeof define === 'function' && define.amd ? define(factory) :
				(global.SignaturePad = factory());
		}(this, (function() {
			'use strict';

			var Point = (function() {
				function Point(x, y, time) {
					this.x = x;
					this.y = y;
					this.time = time || Date.now();
				}
				Point.prototype.distanceTo = function(start) {
					return Math.sqrt(Math.pow(this.x - start.x, 2) + Math.pow(this.y - start.y, 2));
				};
				Point.prototype.equals = function(other) {
					return this.x === other.x && this.y === other.y && this.time === other.time;
				};
				Point.prototype.velocityFrom = function(start) {
					return this.time !== start.time ?
						this.distanceTo(start) / (this.time - start.time) :
						0;
				};
				return Point;
			}());

			var Bezier = (function() {
				function Bezier(startPoint, control2, control1, endPoint, startWidth, endWidth) {
					this.startPoint = startPoint;
					this.control2 = control2;
					this.control1 = control1;
					this.endPoint = endPoint;
					this.startWidth = startWidth;
					this.endWidth = endWidth;
				}
				Bezier.fromPoints = function(points, widths) {
					var c2 = this.calculateControlPoints(points[0], points[1], points[2]).c2;
					var c3 = this.calculateControlPoints(points[1], points[2], points[3]).c1;
					return new Bezier(points[1], c2, c3, points[2], widths.start, widths.end);
				};
				Bezier.calculateControlPoints = function(s1, s2, s3) {
					var dx1 = s1.x - s2.x;
					var dy1 = s1.y - s2.y;
					var dx2 = s2.x - s3.x;
					var dy2 = s2.y - s3.y;
					var m1 = {
						x: (s1.x + s2.x) / 2.0,
						y: (s1.y + s2.y) / 2.0
					};
					var m2 = {
						x: (s2.x + s3.x) / 2.0,
						y: (s2.y + s3.y) / 2.0
					};
					var l1 = Math.sqrt(dx1 * dx1 + dy1 * dy1);
					var l2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
					var dxm = m1.x - m2.x;
					var dym = m1.y - m2.y;
					var k = l2 / (l1 + l2);
					var cm = {
						x: m2.x + dxm * k,
						y: m2.y + dym * k
					};
					var tx = s2.x - cm.x;
					var ty = s2.y - cm.y;
					return {
						c1: new Point(m1.x + tx, m1.y + ty),
						c2: new Point(m2.x + tx, m2.y + ty)
					};
				};
				Bezier.prototype.length = function() {
					var steps = 10;
					var length = 0;
					var px;
					var py;
					for (var i = 0; i <= steps; i += 1) {
						var t = i / steps;
						var cx = this.point(t, this.startPoint.x, this.control1.x, this.control2.x, this.endPoint.x);
						var cy = this.point(t, this.startPoint.y, this.control1.y, this.control2.y, this.endPoint.y);
						if (i > 0) {
							var xdiff = cx - px;
							var ydiff = cy - py;
							length += Math.sqrt(xdiff * xdiff + ydiff * ydiff);
						}
						px = cx;
						py = cy;
					}
					return length;
				};
				Bezier.prototype.point = function(t, start, c1, c2, end) {
					return (start * (1.0 - t) * (1.0 - t) * (1.0 - t)) +
						(3.0 * c1 * (1.0 - t) * (1.0 - t) * t) +
						(3.0 * c2 * (1.0 - t) * t * t) +
						(end * t * t * t);
				};
				return Bezier;
			}());

			function throttle(fn, wait) {
				if (wait === void 0) {
					wait = 250;
				}
				var previous = 0;
				var timeout = null;
				var result;
				var storedContext;
				var storedArgs;
				var later = function() {
					previous = Date.now();
					timeout = null;
					result = fn.apply(storedContext, storedArgs);
					if (!timeout) {
						storedContext = null;
						storedArgs = [];
					}
				};
				return function wrapper() {
					var args = [];
					for (var _i = 0; _i < arguments.length; _i++) {
						args[_i] = arguments[_i];
					}
					var now = Date.now();
					var remaining = wait - (now - previous);
					storedContext = this;
					storedArgs = args;
					if (remaining <= 0 || remaining > wait) {
						if (timeout) {
							clearTimeout(timeout);
							timeout = null;
						}
						previous = now;
						result = fn.apply(storedContext, storedArgs);
						if (!timeout) {
							storedContext = null;
							storedArgs = [];
						}
					} else if (!timeout) {
						timeout = window.setTimeout(later, remaining);
					}
					return result;
				};
			}

			var SignaturePad = (function() {
				function SignaturePad(canvas, options) {
					if (options === void 0) {
						options = {};
					}
					var _this = this;
					this.canvas = canvas;
					this.options = options;
					this._handleMouseDown = function(event) {
						if (event.which === 1) {
							_this._mouseButtonDown = true;
							_this._strokeBegin(event);
						}
					};
					this._handleMouseMove = function(event) {
						if (_this._mouseButtonDown) {
							_this._strokeMoveUpdate(event);
						}
					};
					this._handleMouseUp = function(event) {
						if (event.which === 1 && _this._mouseButtonDown) {
							_this._mouseButtonDown = false;
							_this._strokeEnd(event);
						}
					};
					this._handleTouchStart = function(event) {
						event.preventDefault();
						if (event.targetTouches.length === 1) {
							var touch = event.changedTouches[0];
							_this._strokeBegin(touch);
						}
					};
					this._handleTouchMove = function(event) {
						event.preventDefault();
						var touch = event.targetTouches[0];
						_this._strokeMoveUpdate(touch);
					};
					this._handleTouchEnd = function(event) {
						var wasCanvasTouched = event.target === _this.canvas;
						if (wasCanvasTouched) {
							event.preventDefault();
							var touch = event.changedTouches[0];
							_this._strokeEnd(touch);
						}
					};
					this.velocityFilterWeight = options.velocityFilterWeight || 0.7;
					this.minWidth = options.minWidth || 0.5;
					this.maxWidth = options.maxWidth || 2.5;
					this.throttle = ('throttle' in options ? options.throttle : 16);
					this.minDistance = ('minDistance' in options ?
						options.minDistance :
						5);
					if (this.throttle) {
						this._strokeMoveUpdate = throttle(SignaturePad.prototype._strokeUpdate, this.throttle);
					} else {
						this._strokeMoveUpdate = SignaturePad.prototype._strokeUpdate;
					}
					this.dotSize =
						options.dotSize ||
						function dotSize() {
							return (this.minWidth + this.maxWidth) / 2;
						};
					this.penColor = options.penColor || 'black';
					this.backgroundColor = options.backgroundColor || 'rgba(0,0,0,0)';
					this.onBegin = options.onBegin;
					this.onEnd = options.onEnd;
					this._ctx = canvas.getContext('2d');
					this.clear();
					this.on();
				}
				SignaturePad.prototype.clear = function() {
					var ctx = this._ctx;
					var canvas = this.canvas;
					ctx.fillStyle = this.backgroundColor;
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					ctx.fillRect(0, 0, canvas.width, canvas.height);
					this._data = [];
					this._reset();
					this._isEmpty = true;
				};
				SignaturePad.prototype.fromDataURL = function(dataUrl, options, callback) {
					var _this = this;
					if (options === void 0) {
						options = {};
					}
					var image = new Image();
					var ratio = options.ratio || window.devicePixelRatio || 1;
					var width = options.width || this.canvas.width / ratio;
					var height = options.height || this.canvas.height / ratio;
					this._reset();
					image.onload = function() {
						_this._ctx.drawImage(image, 0, 0, width, height);
						if (callback) {
							callback();
						}
					};
					image.onerror = function(error) {
						if (callback) {
							callback(error);
						}
					};
					image.src = dataUrl;
					this._isEmpty = false;
				};
				SignaturePad.prototype.toDataURL = function(type, encoderOptions) {
					if (type === void 0) {
						type = 'image/png';
					}
					switch (type) {
						case 'image/svg+xml':
							return this._toSVG();
						default:
							return this.canvas.toDataURL(type, encoderOptions);
					}
				};
				SignaturePad.prototype.on = function() {
					this.canvas.style.touchAction = 'none';
					this.canvas.style.msTouchAction = 'none';
					if (window.PointerEvent) {
						this._handlePointerEvents();
					} else {
						this._handleMouseEvents();
						if ('ontouchstart' in window) {
							this._handleTouchEvents();
						}
					}
				};
				SignaturePad.prototype.off = function() {
					this.canvas.style.touchAction = 'auto';
					this.canvas.style.msTouchAction = 'auto';
					this.canvas.removeEventListener('pointerdown', this._handleMouseDown);
					this.canvas.removeEventListener('pointermove', this._handleMouseMove);
					document.removeEventListener('pointerup', this._handleMouseUp);
					this.canvas.removeEventListener('mousedown', this._handleMouseDown);
					this.canvas.removeEventListener('mousemove', this._handleMouseMove);
					document.removeEventListener('mouseup', this._handleMouseUp);
					this.canvas.removeEventListener('touchstart', this._handleTouchStart);
					this.canvas.removeEventListener('touchmove', this._handleTouchMove);
					this.canvas.removeEventListener('touchend', this._handleTouchEnd);
				};
				SignaturePad.prototype.isEmpty = function() {
					return this._isEmpty;
				};
				SignaturePad.prototype.fromData = function(pointGroups) {
					var _this = this;
					this.clear();
					this._fromData(pointGroups, function(_a) {
						var color = _a.color,
							curve = _a.curve;
						return _this._drawCurve({
							color: color,
							curve: curve
						});
					}, function(_a) {
						var color = _a.color,
							point = _a.point;
						return _this._drawDot({
							color: color,
							point: point
						});
					});
					this._data = pointGroups;
				};
				SignaturePad.prototype.toData = function() {
					return this._data;
				};
				SignaturePad.prototype._strokeBegin = function(event) {
					var newPointGroup = {
						color: this.penColor,
						points: []
					};
					if (typeof this.onBegin === 'function') {
						this.onBegin(event);
					}
					this._data.push(newPointGroup);
					this._reset();
					this._strokeUpdate(event);
				};
				SignaturePad.prototype._strokeUpdate = function(event) {
					var x = event.clientX;
					var y = event.clientY;
					var point = this._createPoint(x, y);
					var lastPointGroup = this._data[this._data.length - 1];
					var lastPoints = lastPointGroup.points;
					var lastPoint = lastPoints.length > 0 && lastPoints[lastPoints.length - 1];
					var isLastPointTooClose = lastPoint ?
						point.distanceTo(lastPoint) <= this.minDistance :
						false;
					var color = lastPointGroup.color;
					if (!lastPoint || !(lastPoint && isLastPointTooClose)) {
						var curve = this._addPoint(point);
						if (!lastPoint) {
							this._drawDot({
								color: color,
								point: point
							});
						} else if (curve) {
							this._drawCurve({
								color: color,
								curve: curve
							});
						}
						lastPoints.push({
							time: point.time,
							x: point.x,
							y: point.y
						});
					}
				};
				SignaturePad.prototype._strokeEnd = function(event) {
					this._strokeUpdate(event);
					if (typeof this.onEnd === 'function') {
						this.onEnd(event);
					}
				};
				SignaturePad.prototype._handlePointerEvents = function() {
					this._mouseButtonDown = false;
					this.canvas.addEventListener('pointerdown', this._handleMouseDown);
					this.canvas.addEventListener('pointermove', this._handleMouseMove);
					document.addEventListener('pointerup', this._handleMouseUp);
				};
				SignaturePad.prototype._handleMouseEvents = function() {
					this._mouseButtonDown = false;
					this.canvas.addEventListener('mousedown', this._handleMouseDown);
					this.canvas.addEventListener('mousemove', this._handleMouseMove);
					document.addEventListener('mouseup', this._handleMouseUp);
				};
				SignaturePad.prototype._handleTouchEvents = function() {
					this.canvas.addEventListener('touchstart', this._handleTouchStart);
					this.canvas.addEventListener('touchmove', this._handleTouchMove);
					this.canvas.addEventListener('touchend', this._handleTouchEnd);
				};
				SignaturePad.prototype._reset = function() {
					this._lastPoints = [];
					this._lastVelocity = 0;
					this._lastWidth = (this.minWidth + this.maxWidth) / 2;
					this._ctx.fillStyle = this.penColor;
				};
				SignaturePad.prototype._createPoint = function(x, y) {
					var rect = this.canvas.getBoundingClientRect();
					return new Point(x - rect.left, y - rect.top, new Date().getTime());
				};
				SignaturePad.prototype._addPoint = function(point) {
					var _lastPoints = this._lastPoints;
					_lastPoints.push(point);
					if (_lastPoints.length > 2) {
						if (_lastPoints.length === 3) {
							_lastPoints.unshift(_lastPoints[0]);
						}
						var widths = this._calculateCurveWidths(_lastPoints[1], _lastPoints[2]);
						var curve = Bezier.fromPoints(_lastPoints, widths);
						_lastPoints.shift();
						return curve;
					}
					return null;
				};
				SignaturePad.prototype._calculateCurveWidths = function(startPoint, endPoint) {
					var velocity = this.velocityFilterWeight * endPoint.velocityFrom(startPoint) +
						(1 - this.velocityFilterWeight) * this._lastVelocity;
					var newWidth = this._strokeWidth(velocity);
					var widths = {
						end: newWidth,
						start: this._lastWidth
					};
					this._lastVelocity = velocity;
					this._lastWidth = newWidth;
					return widths;
				};
				SignaturePad.prototype._strokeWidth = function(velocity) {
					return Math.max(this.maxWidth / (velocity + 1), this.minWidth);
				};
				SignaturePad.prototype._drawCurveSegment = function(x, y, width) {
					var ctx = this._ctx;
					ctx.moveTo(x, y);
					ctx.arc(x, y, width, 0, 2 * Math.PI, false);
					this._isEmpty = false;
				};
				SignaturePad.prototype._drawCurve = function(_a) {
					var color = _a.color,
						curve = _a.curve;
					var ctx = this._ctx;
					var widthDelta = curve.endWidth - curve.startWidth;
					var drawSteps = Math.floor(curve.length()) * 2;
					ctx.beginPath();
					ctx.fillStyle = color;
					for (var i = 0; i < drawSteps; i += 1) {
						var t = i / drawSteps;
						var tt = t * t;
						var ttt = tt * t;
						var u = 1 - t;
						var uu = u * u;
						var uuu = uu * u;
						var x = uuu * curve.startPoint.x;
						x += 3 * uu * t * curve.control1.x;
						x += 3 * u * tt * curve.control2.x;
						x += ttt * curve.endPoint.x;
						var y = uuu * curve.startPoint.y;
						y += 3 * uu * t * curve.control1.y;
						y += 3 * u * tt * curve.control2.y;
						y += ttt * curve.endPoint.y;
						var width = curve.startWidth + ttt * widthDelta;
						this._drawCurveSegment(x, y, width);
					}
					ctx.closePath();
					ctx.fill();
				};
				SignaturePad.prototype._drawDot = function(_a) {
					var color = _a.color,
						point = _a.point;
					var ctx = this._ctx;
					var width = typeof this.dotSize === 'function' ? this.dotSize() : this.dotSize;
					ctx.beginPath();
					this._drawCurveSegment(point.x, point.y, width);
					ctx.closePath();
					ctx.fillStyle = color;
					ctx.fill();
				};
				SignaturePad.prototype._fromData = function(pointGroups, drawCurve, drawDot) {
					for (var _i = 0, pointGroups_1 = pointGroups; _i < pointGroups_1.length; _i++) {
						var group = pointGroups_1[_i];
						var color = group.color,
							points = group.points;
						if (points.length > 1) {
							for (var j = 0; j < points.length; j += 1) {
								var basicPoint = points[j];
								var point = new Point(basicPoint.x, basicPoint.y, basicPoint.time);
								this.penColor = color;
								if (j === 0) {
									this._reset();
								}
								var curve = this._addPoint(point);
								if (curve) {
									drawCurve({
										color: color,
										curve: curve
									});
								}
							}
						} else {
							this._reset();
							drawDot({
								color: color,
								point: points[0]
							});
						}
					}
				};
				SignaturePad.prototype._toSVG = function() {
					var _this = this;
					var pointGroups = this._data;
					var ratio = Math.max(window.devicePixelRatio || 1, 1);
					var minX = 0;
					var minY = 0;
					var maxX = this.canvas.width / ratio;
					var maxY = this.canvas.height / ratio;
					var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
					svg.setAttribute('width', this.canvas.width.toString());
					svg.setAttribute('height', this.canvas.height.toString());
					this._fromData(pointGroups, function(_a) {
						var color = _a.color,
							curve = _a.curve;
						var path = document.createElement('path');
						if (!isNaN(curve.control1.x) &&
							!isNaN(curve.control1.y) &&
							!isNaN(curve.control2.x) &&
							!isNaN(curve.control2.y)) {
							var attr = "M " + curve.startPoint.x.toFixed(3) + "," + curve.startPoint.y.toFixed(3) + " " +
								("C " + curve.control1.x.toFixed(3) + "," + curve.control1.y.toFixed(3) + " ") +
								(curve.control2.x.toFixed(3) + "," + curve.control2.y.toFixed(3) + " ") +
								(curve.endPoint.x.toFixed(3) + "," + curve.endPoint.y.toFixed(3));
							path.setAttribute('d', attr);
							path.setAttribute('stroke-width', (curve.endWidth * 2.25).toFixed(3));
							path.setAttribute('stroke', color);
							path.setAttribute('fill', 'none');
							path.setAttribute('stroke-linecap', 'round');
							svg.appendChild(path);
						}
					}, function(_a) {
						var color = _a.color,
							point = _a.point;
						var circle = document.createElement('circle');
						var dotSize = typeof _this.dotSize === 'function' ? _this.dotSize() : _this.dotSize;
						circle.setAttribute('r', dotSize.toString());
						circle.setAttribute('cx', point.x.toString());
						circle.setAttribute('cy', point.y.toString());
						circle.setAttribute('fill', color);
						svg.appendChild(circle);
					});
					var prefix = 'data:image/svg+xml;base64,';
					var header = '<svg' +
						' xmlns="http://www.w3.org/2000/svg"' +
						' xmlns:xlink="http://www.w3.org/1999/xlink"' +
						(" viewBox=\"" + minX + " " + minY + " " + maxX + " " + maxY + "\"") +
						(" width=\"" + maxX + "\"") +
						(" height=\"" + maxY + "\"") +
						'>';
					var body = svg.innerHTML;
					if (body === undefined) {
						var dummy = document.createElement('dummy');
						var nodes = svg.childNodes;
						dummy.innerHTML = '';
						for (var i = 0; i < nodes.length; i += 1) {
							dummy.appendChild(nodes[i].cloneNode(true));
						}
						body = dummy.innerHTML;
					}
					var footer = '</svg>';
					var data = header + body + footer;
					// alert("SVG : "+data);
					save_sign(data);
					return prefix + btoa(data);
				};
				return SignaturePad;
			}());

			return SignaturePad;

		})));
	</script>
	<script>
		var wrapper = document.getElementById("signature-pad");
		var clearButton = wrapper.querySelector("[data-action=clear]");
		var changeColorButton = wrapper.querySelector("[data-action=change-color]");
		var undoButton = wrapper.querySelector("[data-action=undo]");
		var savePNGButton = wrapper.querySelector("[data-action=save-png]");
		var saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
		var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
		var canvas = wrapper.querySelector("canvas");
		var signaturePad = new SignaturePad(canvas, {
			// It's Necessary to use an opaque color when saving image as JPEG;
			// this option can be omitted if only saving as PNG or SVG
			backgroundColor: 'rgb(255, 255, 255)'
		});

		// Adjust canvas coordinate space taking into account pixel ratio,
		// to make it look crisp on mobile devices.
		// This also causes canvas to be cleared.
		function resizeCanvas() {
			// When zoomed out to less than 100%, for some very strange reason,
			// some browsers report devicePixelRatio as less than 1
			// and only part of the canvas is cleared then.
			var ratio = Math.max(window.devicePixelRatio || 1, 1);
			// This part causes the canvas to be cleared
			canvas.width = canvas.offsetWidth * ratio;
			canvas.height = canvas.offsetHeight * ratio;
			canvas.getContext("2d").scale(ratio, ratio);

			// This library does not listen for canvas changes, so after the canvas is automatically
			// cleared by the browser, SignaturePad#isEmpty might still return false, even though the
			// canvas looks empty, because the internal data of this library wasn't cleared. To make sure
			// that the state of this library is consistent with visual state of the canvas, you
			// have to clear it manually.
			signaturePad.clear();
		}

		// On mobile devices it might make more sense to listen to orientation change,
		// rather than window resize events.
		window.onresize = resizeCanvas;
		resizeCanvas();

		function download(dataURL, filename) {
			if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
				window.open(dataURL);
			} else {
				var blob = dataURLToBlob(dataURL);
				var url = window.URL.createObjectURL(blob);

				var a = document.createElement("a");
				a.style = "display: none";
				a.href = url;
				a.download = filename;
				// document.body.appendChild(a);
				// a.click();

				// window.URL.revokeObjectURL(url);
			}
		}

		// One could simply use Canvas#toBlob method instead, but it's just to show
		// that it can be done using result of SignaturePad#toDataURL.
		function dataURLToBlob(dataURL) {
			// Code taken from https://github.com/ebidel/filer.js
			var parts = dataURL.split(';base64,');
			var contentType = parts[0].split(":")[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			var Str = "";
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
				Str += uInt8Array[i];
			}

			var bbb = new Blob([uInt8Array], {
				type: contentType
			});
			return new Blob([uInt8Array], {
				type: contentType
			});
		}

		clearButton.addEventListener("click", function(event) {
			signaturePad.clear();
		});

		undoButton.addEventListener("click", function(event) {
			var data = signaturePad.toData();

			if (data) {
				data.pop(); // remove the last dot or line
				signaturePad.fromData(data);
			}
		});

		changeColorButton.addEventListener("click", function(event) {
			var r = Math.round(Math.random() * 255);
			var g = Math.round(Math.random() * 255);
			var b = Math.round(Math.random() * 255);
			var color = "rgb(" + r + "," + g + "," + b + ")";

			signaturePad.penColor = color;
		});

		savePNGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL();
				download(dataURL, "signature.png");
			}
		});

		saveJPGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL("image/jpeg");
				download(dataURL, "signature.jpg");
			}
		});

		saveSVGButton.addEventListener("click", function(event) {
			if (signaturePad.isEmpty()) {
				alert("Please provide a signature first.");
			} else {
				var dataURL = signaturePad.toDataURL('image/svg+xml');
				download(dataURL, "signature.svg");
			}
		});
	</script>

</body>

</html>