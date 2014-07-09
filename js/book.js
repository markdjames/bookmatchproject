var book = {
	
	init: function() {
		$('.book a').each(function() {
			$(this).unbind('click');
			$(this).attr('href', 'javascript:void(0)');
			$(this).bind('click', function() {
				modal($(this).data('id'), 'book_info');
			});
		});
	},
	
	mouseEnter: function(b) {
		$(b).append("<p class='remover'><a href='javascript:void(0)' onclick=\"list.remove($(this).parent().parent().data('id'), event)\">remove</a></p>");
	},
	
	mouseLeave: function(b) {
		$(b).children('.remover').remove();
	},
	
	discard:function(id) {
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/add_discarded.php',
			data:{
				id:id
			},
			success:function(response) {
				search.recommendations();
			}
		});
	},
	
	read:function(id) {
		$.ajax({
			type:'POST',
			url:BASE+'/lib/ajax/add_read.php',
			data:{
				id:id
			},
			success:function(response) {
				search.recommendations();
			}
		});
	}
}