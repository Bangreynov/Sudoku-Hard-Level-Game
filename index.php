<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Sudoku Hard Level</title>
	<meta name="viewport" content="width=462,height=device-height"/>
		<link rel="icon" type="image/png" href="/favicon.ico">
    <link rel="stylesheet" href="../sudoku/style.css">
	<link rel="stylesheet" href="../sudoku/font-awesome.min.css">
    <script src="../sudoku/jquery-2.1.4.min.js"></script>
	<script src="../sudoku/jquery.mobile.custom.min.js"></script>

	 </head>

  <body>
 
 <div id="printableArea">
    <div id="GameView"></div>
 </div>
<script type="text/JavaScript">
 function Redirect() {
window.location.assign("https://hard-sudoku.com/?github");
 }
</script>

	<script src="../sudoku/sudoku.js"></script>
	<script>
		; (function ($, window, document, undefined) {


			$.Sudoku = function (game) {


				// Set Playing Data
				//
				var generatingLevel = 3;
				var playingLevel = 1;
				var playingNumber = 1;

				var level_label = ['Beginners Sudoku', 'Easy Sudoku', 'Medium Sudoku', 'Hard Sudoku', 'Evil Sudoku'];
				var generate_label = function () { return 'Play ' + level_label[generatingLevel] };


				// Build html structure first
				//
				var $this = $('<div class="sudoku">');
				var $generator = $('<button class="btn-generate initial" title="swipe left/right to change level">' + generate_label() + '</button>');
				var $btnEasier = $('');
				var $btnHarder = $('');
				var $info = $('<div class="sudoku-info"></div>');
				var $overlay = $('<div class="overlay layer"><div class="message"></div><div class="record"></div><div class="record-level"></div></div>');
				var $playRecord = $('<div class="play-record">00:00:00</div>');

				var $cells = $('<table class="cells layer">'),
					$clickables = $('<table class="clickables layer">');
				for (var i = 0; i < 9; i++) {
					var $tr = $('<tr>');
					for (var j = 0; j < 9; j++) {
						var id = i * 9 + j;
						var $td = $('<td></td>').data('id', id);
						$tr.append($td);
					}
					$cells.append($tr);
					$clickables.append($tr.clone(true, true));
				}

				var $blockBg = $('<table class="block-bg layer">'),
					$blockBorder = $('<table class="block-border layer">');
				for (var i = 0; i < 3; i++) {
					var $tr = $('<tr>');
					for (var j = 0; j < 3; j++) {
						var id = i * 3 + j;
						var $td = $('<td class="block-' + id + '"></td>').data('id', id);
						$tr.append($td);
					}
					$blockBg.append($tr);
					$blockBorder.append($tr.clone());
				}

				var $bottomline = $('<div class="menu menu-bottom"></div>');
				for (var i = 1; i <= 9; i++) {
					var $btn = $('<button class="numpad numpad-' + i + '">' + i + '</button>').data('number', i)
					if (i == 1) $btn.addClass('numpad-selected');
					$bottomline.append($btn)
				}


				//Assembly html
				//
				$this
					.append($('<div class="menu menu-top">')
						.append($btnEasier)
						.append($generator)
						.append($btnHarder))
					.append($info)
					.append($('<div class="table-container">')
						.append($blockBg)
						.append($cells)
						.append($blockBorder)
						.append($clickables)
						.append($overlay))
				$this.append($bottomline
					.append($playRecord));


				// Define all actions
				//
				function renderGameTable() {
					$overlay.fadeOut();

					//Reset number pads
					$('.solved-number').removeClass('solved-number');

					//Reset cells
					var num_clues = 0;
					$cells.find('.note').remove();
					$cells.find('td').removeClass().each(function (index) {
						if (game.table.cells[index].isClue) {
							num_clues++;
							$(this).html(game.table.cells[index].value).addClass('clue number-' + game.table.cells[index].value);
						} else {
							game.table.cells[index].isUserInput = false;
							$(this).html('<span class="input"></div>');
						}
					});
					$info.html('<span class="btn-print" onclick="printDiv(printableArea)">Print</span>' + ' ' + level_label[generatingLevel] + ' ');
					$info.append($('<span class="btn-reset">Restart</span>').click(function () { renderGameTable() }));

					//Reset record
					$playRecord.data('record', 0);
					clearInterval($playRecord.data('timer'));
					$playRecord.html(' 00:00:00 ');
					$playRecord.data('timer', setInterval(function () {
						$playRecord.data('record', $playRecord.data('record') + 1);
						var record = $playRecord.data('record');
						var hour = parseInt(record / 3600);
						var min = parseInt((record / 60)) % 60;
						var sec = record % 60;
						if (hour < 10) hour = '0' + hour;
						if (min < 10) min = '0' + min;
						if (sec < 10) sec = '0' + sec;
						$playRecord.html(' ' + hour + ':' + min + ':' + sec + ' ');
					}, 1000));
					$('.number-' + playingNumber).addClass('active-number');
					for (var i = 1; i <= 9; i++) {
						if ($('.number-' + i).length == 9)
							$('.numpad-' + i + ', .number-' + i).addClass('solved-number');
					}
				}

				//for debugging, uncomment below and you can use this in console
				//window.renderGameTable = renderGameTable;

				$generator.on('tap', function (e) {

					e.preventDefault();
					$(this).removeClass('initial');
					playingLevel = generatingLevel;
					if (generatingLevel >= Sudoku.Difficulty.length) {
						console.time('Load sudoku 17');
						window._sudoku17 = window._sudoku17 || [];
						if (window._sudoku17.length > 0) {
							var i = Math.floor(Math.random() * window._sudoku17.length);
							var line = window._sudoku17[i];
							game.load(line);
							renderGameTable();
							console.timeEnd('Load sudoku 17');
							return;
						}

						$.ajax('sudoku17.txt').then(function (lines) {
							window._sudoku17 = lines.split('\n');
							var i = Math.floor(Math.random() * window._sudoku17.length);
							var line = window._sudoku17[i];
							game.load(line);
							renderGameTable();
							console.timeEnd('Load sudoku 17');
						})
						return;
					}
					console.timeEnd('Generate sudoku');
					game.generate(generatingLevel);
					renderGameTable();
					console.timeEnd('Generate sudoku');

				}).on('swipeleft', function (e) {
					$btnEasier.trigger('click');
					e.preventDefault();
					e.stopPropagation();
				}).on('swiperight', function (e) {
					$btnHarder.trigger('click');
					e.preventDefault();
					e.stopPropagation();
				});


				$btnEasier.click(function () {
					generatingLevel = Math.max(0, generatingLevel - 1);
					$generator.html(generate_label());
				});

				$btnHarder.click(function () {
					generatingLevel = Math.min(4, generatingLevel + 1);
					$generator.html(generate_label());
				});


				$clickables.find('td').on('mousedown', function (e) {
					e.preventDefault();
					var id = $(this).data('id');
					var the_cell = game.table.cells[id];
					var $cell = $cells.find('td').eq(id);

					//Ignore if the cell is a clue
					if (the_cell.isClue) {
						return false;
					}

					//Remove number
					if (e.which == 3 || (e.which == 1 && the_cell.isUserInput && playingNumber == the_cell.value)) {
						$cell.removeClass();
						the_cell.isUserInput = false;
						the_cell.value = 0;
						$cell.find('.input').html('');

						//Remove or put a note
					} else if (!the_cell.isUserInput && (e.which == 2 || (e.which == 1 && $('body').is('.in-ctrl')))) {
						if ($cell.find('.note-' + playingNumber).length == 0)
							$cell.append('<span class="note note-' + playingNumber + '">' + playingNumber + '</span>');
						else
							$cell.find('.note-' + playingNumber).remove();

						//Put Number
					} else if (e.which == 1) {
						$cell.removeClass();
						the_cell.isUserInput = true;
						the_cell.value = playingNumber;
						if (the_cell.isError()) {
							$cell.addClass('error');
						} else {

							//Remove guesses
							var rel = the_cell.related;
							for (var i = 0; i < rel.length; i++) {
								$cells.find('td').eq(rel[i].id).find('.note-' + playingNumber).remove();
							}
							$cell.find('.note').remove();

							$cell.addClass('active-number solving number-' + playingNumber);
							if ($('.number-' + playingNumber).length == 9) {
								$('.numpad-' + playingNumber + ', .number-' + playingNumber).addClass('solved-number');
							}
						}
						$cell.find('.input').html(playingNumber);
					}

					//Check errors and correct colors of cells
					for (var i = 0; i < 81; i++) {
						var c = game.table.cells[i];
						if (c.isUserInput) {
							var $c = $cells.find('td').eq(i);
							if (c.isError() && !$c.is('.error')) {
								$c.removeClass();
								$c.addClass('error');
							} else if (!c.isError() && $c.is('.error')) {
								$c.removeClass();
								var number = parseInt($c.find('.input').html());
								if (number == playingNumber)
									$c.addClass('active-number');
								$c.addClass('solving number-' + number);
							}
						}
					}

					//Update number button's color
					for (var i = 1; i <= 9; i++) {
						if ($('.number-' + i).length == 9)
							$('.numpad-' + i + ', .number-' + i).addClass('solved-number');
						else
							$('.numpad-' + i + ', .number-' + i).removeClass('solved-number');
					}

					if ($('.numpad.solved-number').length == 9) {
						clearInterval($playRecord.data('timer'));
						$overlay.find('.record').html($playRecord.html());
						$overlay.find('.message').html('Congratulations!');
						$overlay.find('.record-level').html(level_label[playingLevel]);						
						$overlay.fadeIn();
						//level_label[playingLevel]
						//FINISH
					}

					return false;
				}).on('contextmenu', function () { return false; })


				$bottomline.find('.numpad').on('click', function (e) {
					$('.numpad-selected').removeClass('numpad-selected');
					$(this).addClass('numpad-selected');
					$('.active-number').removeClass('active-number');
					$('.number-' + $(this).data('number')).addClass('active-number');
					if (playingNumber == $(this).data('number')) {
						$('body').toggleClass('in-ctrl');
					} else {
						$('body').removeClass('in-ctrl');
					}
					playingNumber = $(this).data('number');

				});


				$(window).on('keydown', function (e) {
					if (49 <= e.keyCode && e.keyCode <= 57) {
						var number = e.keyCode - 48;
						$('.numpad-' + number).trigger('click');
					} else if (97 <= e.keyCode && e.keyCode <= 105) {
						var number = e.keyCode - 96;
						$('.numpad-' + number).trigger('click');
					} else if (71 == e.keyCode) {
						$('#generate').trigger('click');
					} else if (e.keyCode == 17 || e.keyCode == 16) { //To note(mark) a guessing number. control key for quick or shift key for toggle
						$('body').toggleClass('in-ctrl');
					}
				}).on('keyup', function (e) {
					if (e.keyCode == 17) { //To note(mark) a guessing number.
						$('body').toggleClass('in-ctrl');
					}
				});


				return $this;
			}
		})($, window, document);

		var game = new Sudoku();
		var $sudoku = $.Sudoku(game);
		$('#GameView').append($sudoku);

		function getUrlParams(search) {
			return search.substr(1).split('&')
				.map(function (part) {
					return part.split('=')
				})
				.reduce(function (result, item) {
					if (item[0]) result[item[0]] = decodeURIComponent(item[1]);
					return result;
				}, {});
		}

		function printDiv(printableArea) {
     var printContents = document.getElementById("printableArea").innerHTML;
     var originalContents = document.body.innerHTML;
     var newWin=window.print();

     newWin.document.body.innerHTML = originalContents;
     newWin.document.close();
setTimeout(function(){newWin.close();},2);


}

	</script>

</script>
	
  </body>
</html>
