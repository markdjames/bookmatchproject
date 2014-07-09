var list = {
	linkify: function() {
		$('#results div p').each(function() {
			$(this).bind('click', function() {
				list.add($(this).parent());
			});
		});
	},
	
	add: function(b) {
		
		$('#list').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
		$('#results').hide();
		$('#search').val('');	
		
		this.added.push($(b).data('asin'));
		
		$.ajax({
			type:'POST',
			url:'/books/lib/ajax/store_list.php',
			data:{
				title:		$(b).data('title'),
				author:		$(b).data('author'),
				ASIN:		$(b).data('asin'),
			},
			success:function(response) {
				list.update(response);
			}
		});		
	},
	
	getShelf: function(b) {
		$.ajax({
			type:'POST',
			url:'/books/lib/ajax/get_shelf.php',
			success:function(response) {
				list.update(response);
			}
		});		
	},
	
	update: function(output) {
		$('#list').html(output);
		search.recommendations();
		
		$('#list .book').each(function() {
			$(this).bind('mouseenter', function() {
				book.mouseEnter($(this));
			});
		});
		
		$('#list .book').each(function() {
			$(this).bind('mouseleave', function() {
				book.mouseLeave($(this));
			});
		});
		
		book.init();
	},
	
	remove: function(id, event) {
		event.stopPropagation();
		$('#list').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
		$.ajax({
			type:'POST',
			url:'/books/lib/ajax/remove_book.php',
			data:{
				id:id
			},
			success:function(response) {
				list.update(response);
			}
		});		
	},
	
	added: new Array(),
	related: new Array()
}