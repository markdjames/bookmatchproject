/****************
* loads a modal dialog over the page (positioned center)
*
* @id:int = id of record that needs editing
* @form:String = path to form (base /lib/forms) to be called via ajax
* @size:Strng = auto / small / medium / large
* @data:Object = extra vars to send to form
*****************/
function modal(id, form, size, data) {
	"use strict";
	size = (size==null) ? 'auto' : size;
	
	var alldata = {id:id};
	if (data!==null) {
		$.extend(alldata, data);
	}

	$('#modal').css({
		width:'100px',
		height:'100px'
	});
	$('#screener').css({opacity:0});
	$('#screener').show();
	$('#screener').animate({opacity:0.8});
	
	$('#modal_inner').html("<div class='loading'><img src='"+BASE+"/core/images/loading.gif' /></div>");
	$('#modal_wrap').fadeIn();
	
	$.ajax({
		url:BASE+'/lib/forms/'+form+'.php',
		type:'POST',
		data:alldata,
		success:function(response) {
			if (form=='login') {
				var width = 400;
				var height = 220;
				var top = 100;
			} else {
				var width = $(window).innerWidth()-300;
				var height = $(window).innerHeight()-150;
				var top = 50;
			}
			$('#modal_wrap').css({	
				top:top
			});
			$('#modal').animate({
				width:width,
				height:height
			}, function() {
				$('#modal_inner').hide();
				var cross = "<img src='"+BASE+"/core/images/icons/cross.png' onclick='exitModal()' class='modal_cross' />";
				var clearer = "<div style='clear:both'></div>";
				$('#modal_inner').html(cross+response+clearer);
				$('#modal_inner').fadeIn(function() {
					help.init();
					if (form=='login') {
						$("#username").focus();
					}
					if (form=='users/data_protection') {
						$('.modal_cross').remove();
					}
				});
			});
		}
	});
}

/****************
* removes modal dialog from page
*****************/
function exitModal() {
	"use strict";
	$('#modal_wrap').fadeOut(500, function() {
		$('#modal_inner').html('');
	});
	$('#screener').fadeOut(1000);	
}

function resizeModal() {
	"use strict";
	position();
	$('#modal').animate({
		height:$('#modal_inner').innerHeight()+10 //-40
	});
	if (parseInt($('#modal').css('top'), 2)<0) {
		$('#modal').css('top', '35px');
		$('#modal').css('height', ($(window).height()-100)+'px');
	}
}