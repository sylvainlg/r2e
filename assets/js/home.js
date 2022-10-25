$(function() {
	$('#deleteModal').on('show.bs.modal', function(e) {
		console.log($(e.relatedTarget).data('href'));
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
	});
});