(function ( ) {
	'use strict';

	var $outlets =  $('.outlet-row');

	// populate the table
	$('.outlet-row').each(function() {
		var $this = $(this),
			id,
			name,
			state;

		id = $this.data('id');
		state = $this.data('state');
		name = $this.data('name');

		// add toggle button
		$this.append($(
			'<div class="outlet-button outlet-state-' + state + '">' + state + '</div>'
		));

		// add label
		$this.append($(
			'<div class="outlet-name">' +
			name +
			'</div>'
		));
	});

	// attach on-click handlers
	$('.outlet-button').click(function() {
		var $this = $(this),
			$parent = $this.parent(),
			id,
			state;

		id = $parent.data('id');
		state = $parent.data('state');

		// toggle the state
		state = state === 'off' ? 'on' : 'off';
		$this.text(state);
		$parent.data('state', state);

		// change the class
		$this.toggleClass('outlet-state-off');
		$this.toggleClass('outlet-state-on');

		// debug
		console.log('turned outlet ' + id + ' ' + state);

		$.post('outletctl.php', {
			outlet: id,
			command: state
		});
	});

	$('.outlet-name').click(function() {
		var $this = $(this),
			$input;

		$input = $(
			'<input type="text" class="outlet-update-name">'
		);

		// hide the name field
		$this.hide();

		// add the input field
		$this.after($input);

		// populate the input field
		$input.focus();
		$input.val($this.text());

		$input.keypress(function(event) {
			var id,
				text;

			if (event.which != 13) {
				return;
			}
			
			event.preventDefault();

			// update the name
			text = $input.val();
			if (text) {
				id = $(this).parent().data('id');
				$this.text($input.val());
				$.post('update_name.php', {
					outlet: id,
					name: $input.val()
				});
			}

			// restore the name label
			$input.remove();
			$this.show();
		});
	});

}());
