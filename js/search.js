var search = {
	init: function() {
		$('#search').bind('keyup', function() {
			$('#results').show();
			$('#results').html('<p>loading...</p>');
			if ($(this).val().length > 2) {
				search.getBooks($(this).val());
			} else {
				$('#results').hide();
				$('#results').html('');
			}
		});
	},
	
	xhr_local:null,
	xhr:null,
	getBooks:function(q) {
		
		$(document).bind('click', function() {
			search.close();
		});
		
		if (this.xhr!=null) this.xhr.abort();
		if (this.xhr_local!=null) this.xhr_local.abort();
		
		this.xhr_local = $.ajax({
			type:'GET',
			url:'/lib/ajax/get_local_books.php',
			dataType:"json",
			data:{
				q:q
			},
			success:function(response) {
				$('#results').html('');
				$.each( response, function( key, val ) {			
					$('#results').append("<div data-title=\""+val.title+"\" data-author=\""+val.author+"\" data-asin='"+val.ASIN+"'><p>"+val.title+" by "+val.author+"<span><a href='javascript:void(0)'>add</a></span></p></div>");
				});
				$('#results').append("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
				list.linkify();
				
				this.xhr = $.ajax({
					type:'GET',
					url:'/lib/ajax/get_books.php',
					dataType:"json",
					data:{
						q:q
					},
					success:function(response) {
						$('#results .loading').remove();
						$.each( response, function( key, val ) {			
							$('#results').append("<div data-title=\""+val.title+"\" data-author=\""+val.author+"\" data-asin='"+val.ASIN+"'><p>"+val.title+" by "+val.author+"<span><a href='javascript:void(0)'>add</a></span></p></div>");
						});
						list.linkify();
		
					}
				});
			}
		});
		
	},
	
	lookup:function(q) {
		$.ajax({
			type:'GET',
			url:'/lib/ajax/get_book_by_asin.php',
			dataType:"json",
			data:{
				q:q
			},
			success:function(response) {
				$('#related').append("<div class='book'><img src='"+response.thumbnail+"' /><p><strong>"+response.title+"</strong><br /><em>"+response.author+"</em></p><div style='clear:both'></div></div>");
			}
		});
	},
	
	recommendations:function(q) {
		$('#related').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
		$.ajax({
			type:'POST',
			url:'/lib/ajax/recommendations.php',
			data:{
				q:q
			},
			success:function(response) {
				if (response!="") {
					$('#related').html(response);
					book.init();
				} else {
					$('#related').html("");
				}
			}
		});
	},
	
	close:function() {
		$('#results').hide();
		$('#results').html('');
		$(document).unbind('click');
	},
	
	related: new Object(),
}

$(document).ready(function() {
	search.init();
});