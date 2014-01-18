jQuery(document).ready(function($) {
	//nemus_slider - Nemus slider shortcode
	var nemus_slider_shortcode_tooltip = $('<div>').addClass('nemus_slider_shortcode_tooltip');
	var nemus_slider_shortcode_tooltip_close = $('<a>').addClass('nemus_slider_shortcode_tooltip_close nemusicon-cancel');
	var nemus_slider_shortcode_tooltip_select = $('<select>').attr('name','test').attr('data-placeholder','Select a slider').append("<option></option>");
	var nemus_ajax_called = false;

	tinymce.create('tinymce.plugins.feature_list', {  
		init : function(ed, url) {  
			ed.addButton('nemus_slider', {  
				title : 'Nemus Slider',  
				onclick : function(e) {  
				
					var offset = $('a#content_nemus_slider').offset();
					nemus_slider_shortcode_tooltip.css('left',offset.left).css('top',offset.top);
				
					$('body').append(nemus_slider_shortcode_tooltip.append(nemus_slider_shortcode_tooltip_close));
					
					$('.nemus_slider_shortcode_tooltip').addClass('active');
				
					var data = {
						action: 'nemus_slider_load_sliders'
					};
				
					if (!nemus_ajax_called) {
				
						$.post(ajaxurl, data, function(response) {
						
							var response = jQuery.parseJSON(response);
						
							$.each( response, function( key, value ) {
								nemus_slider_shortcode_tooltip_select.append('<option value="'+value.id+'">'+value.title+'</option>');
							});
						
							$('.nemus_slider_shortcode_tooltip').prepend(nemus_slider_shortcode_tooltip_select);
						
							$(".nemus_slider_shortcode_tooltip select").change(function(e){
								
								var id = $(".nemus_slider_shortcode_tooltip select").val();
								
								ed.selection.setContent('[nemus_slider id="'+id+'"]'); 
								
								$(".nemus_slider_shortcode_tooltip select option").removeAttr('selected');
								
								$('.nemus_slider_shortcode_tooltip').removeClass('active');
								
							});
							
							nemus_ajax_called = true;
						});
					
					} 
					
				}  
			});  
		},  
		createControl : function(n, cm) {  
			return null;  
		},  
	});  
	tinymce.PluginManager.add('nemus_slider', tinymce.plugins.feature_list);

	nemus_slider_shortcode_tooltip_close.click(function() {
	    $('.nemus_slider_shortcode_tooltip').removeClass('active'); 
	});
	//nemus_slider - Nemus slider shortcode
	
});