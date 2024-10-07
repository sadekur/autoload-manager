(function ($) {
	$(document).ready(function () {
		// $("#autoload-manager_report-copy").click(function (e) {
		// 	e.preventDefault();
		// 	$("#autoload-manager_tools-report").select();

		// 	try {
		// 		if (document.execCommand("copy")) {
		// 			$(this).html(
		// 				'<span class="dashicons dashicons-saved"></span>'
		// 			);
		// 		}
		// 	} catch (err) {
		// 		console.log("Oops, unable to copy!");
		// 	}
		// });

		// Filter on/off
		function filterOptions(status) {
			var rows = $("#autoloadOptionsTable tbody tr");
			switch (status) {
				case "all":
					rows.show();
					break;
				case "on":
					rows.hide().filter(".status-on").show();
					break;
				case "off":
					rows.hide().filter(".status-off").show();
					break;
			}
		}
		// Bind buttons to filter function
		$("#filter-all").click(function () {
			filterOptions("all");
		});
		$("#filter-on").click(function () {
			filterOptions("on");
		});
		$("#filter-off").click(function () {
			filterOptions("off");
		});

		// On/Off toggle value Saved
		$(".autoload-manager-checkbox").on("change", function () {
			var optionId = $(this).data("option-id");
			console.log(optionId);
			var newAutoload = $(this).is(":checked") ? "yes" : "no";

			$.post({
				url: AUTOLOADMANAGER.ajaxurl,
				data: {
					action: "update_autoload_option",
					option_id: optionId,
					autoload: newAutoload,
					nonce: AUTOLOADMANAGER._wpnonce,
				},
				success: function (response) {
					alert("Autoload updated!");
				},
				error: function () {
					alert("Error updating autoload.");
				},
			});
		});

		// Bulk toggle value Saved
		$("#select-all").click(function () {
			$(".row-select").prop("checked", $(this).prop("checked"));
		});
		function updateBulkAutoload(newStatus) {
			var selectedOptions = $(".row-select:checked")
				.map(function () {
					return $(this).data("option-id");
				})
				.get();

			$.post({
				url: AUTOLOADMANAGER.ajaxurl,
				data: {
					action: "update_bulk_autoload_option",
					option_ids: selectedOptions,
					autoload: newStatus,
					nonce: AUTOLOADMANAGER._wpnonce,
				},
				success: function (response) {
					alert("Bulk autoload update successful!");
					//location.reload();
				},
				error: function () {
					alert("Error updating autoload.");
				},
			});
		}

		$("#bulk-on").click(function () {
			updateBulkAutoload("yes");
		});
		$("#bulk-off").click(function () {
			updateBulkAutoload("no");
		});

		// Pagination
		var currentPage = 1;
		var itemsPerPage = 50;

		function loadPage(page) {
			$.ajax({
				url: AUTOLOADMANAGER.ajaxurl,
				type: "POST",
				data: {
					action: "load_options_data",
					page: page,
					items_per_page: itemsPerPage,
				},
				success: function (response) {
					$("#autoloadOptionsTable tbody").html(
						response.data.table_content
					);
					// Update pagination controls
					currentPage = page;
					$("#prev-page").prop("disabled", currentPage <= 1);
					$("#next-page").prop(
						"disabled",
						currentPage >= response.data.total_pages
					);
				},
			});
		}

		// On/Off toggle value Saved
		$(".autoload-manager-checkbox").on("change", function () {
			var optionId = $(this).data("option-id");
			console.log(optionId);
			var newAutoload = $(this).is(":checked") ? "yes" : "no";

			$.post({
				url: AUTOLOADMANAGER.ajaxurl,
				data: {
					action: "update_autoload_option",
					option_id: optionId,
					autoload: newAutoload,
					nonce: AUTOLOADMANAGER._wpnonce,
				},
				success: function (response) {
					alert("Autoload updated!");
				},
				error: function () {
					alert("Error updating autoload.");
				},
			});
		});

		// Pagination buttons
		var currentPage = 1;
		var itemsPerPage = 50;
		$("#prev-page").click(function () {
			if (currentPage > 1) {
				currentPage -= 1;
				refreshTable();
			}
		});

		$("#next-page").click(function () {
			currentPage += 1;
			refreshTable();
		});

		function refreshTable() {
			$.ajax({
				url: AUTOLOADMANAGER.ajaxurl,
				type: "POST",
				data: {
					action: "load_options_data",
					page: currentPage,
					items_per_page: itemsPerPage,
				},
				success: function (response) {
					$("#autoloadOptionsTable tbody").html(
						response.data.table_content
					);
					$("#prev-page").prop("disabled", currentPage <= 1);
					$("#next-page").prop(
						"disabled",
						currentPage >= response.data.total_pages
					);
				},
			});
		}

		// Event delegation for on/off toggle
		$("#autoloadOptionsTable").on(
			"change",
			".autoload-manager-checkbox",
			function () {
				var optionId = $(this).data("option-id");
				var newAutoload = $(this).is(":checked") ? "yes" : "no";
				var $statusTd = $(this).closest("tr").find(".autoload-status");
				$.post({
					url: AUTOLOADMANAGER.ajaxurl,
					data: {
						action: "update_autoload_option",
						option_id: optionId,
						autoload: newAutoload,
						nonce: AUTOLOADMANAGER._wpnonce,
					},
					success: function (response) {
						alert("Autoload updated!");
						$statusTd.text(newAutoload);
					},
					error: function () {
						alert("Error updating autoload.");
					},
				});
			}
		);
	});
})(jQuery);
