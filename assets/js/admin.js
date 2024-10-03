(function ($) {
	$(document).ready(function () {
		$("#autoload-manager_report-copy").click(function (e) {
			e.preventDefault();
			$("#autoload-manager_tools-report").select();

			try {
				if (document.execCommand("copy")) {
					$(this).html(
						'<span class="dashicons dashicons-saved"></span>'
					);
				}
			} catch (err) {
				console.log("Oops, unable to copy!");
			}
		});

		$(".autoload-submit").on("click", function () {
			console.log("Button clicked");
			var button = $(this);
			var optionName = button.data("option");
			var autoloadValue = button
				.closest("tr")
				.find(".autoload-toggle")
				.is(":checked")
				? "yes"
				: "no";

			$.ajax({
				url: AUTOLOADMANAGER.ajaxurl,
				type: "POST",
				data: {
					action: "toggle_autoload_option",
					option_name: optionName,
					autoload: autoloadValue,
					// nonce: AUTOLOADMANAGER._wpnonce,
				},
				success: function (response) {
					if (response.success) {
						alert("Autoload updated successfully");
					} else {
						alert("Failed to update autoload");
					}
				},
				error: function () {
					alert("An error occurred while updating autoload");
				},
			});
		});
	});
})(jQuery);
