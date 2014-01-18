jQuery(document).ready(function($) {

	//Autoplay toggle
	$('input[name="slider_options[autoplay]"]').change(function(){
		if ($(this).is(':checked')) {
			$('#autoplay-delay').show();
		} else {
			$('#autoplay-delay').hide();
		}
	});

	//Autoheight toggle
	$('input[name="slider_options[autoheight]"]').change(function(){
		if ($(this).is(':checked')) {
			$('#slider-height').hide();
		} else {
			$('#slider-height').show();
		}
	});

	//Create a new slide	
	$('#add_slide').click(function(){
		$('#slides').append($('#slide_empty').clone().removeAttr('id').show());
		slideNumbers();
		return false;
	});
	
	//If its a new slider, create an empty slide to start with
	if (!$('#nemus_slider_slides #slides .slide').length) {
		$('#add_slide').trigger('click');
	}
	
	$('#add_automated_slide').click(function(){
		$('#slides').append($('#slide_auto_empty').clone().removeAttr('id').show());
		slideNumbers();
		return false;
	});

	//Sortable slides	
	if ($('#nemus_slider_slides').length) {
		$('#slides').sortable({
			update: function(event, ui){  
				slideNumbers();
			},
			axis: 'y',
			containment: "#normal-sortables",
			smooth: true
		});
	}
	
	//Update the slide field names and indexes
	function slideNumbers() {
		$('#slides .slide').each(function(){
			var index = $(this).index();
			$(this).find('header em').html('#'+parseInt(index+1));
			
			$(this).find('input.image-link').attr('name','slide_data['+index+'][image]');
			$(this).find('input.video-link').attr('name','slide_data['+index+'][video]');
			
			$(this).find('textarea.caption').attr('name','slide_data['+index+'][caption]');
			$(this).find('input.caption_position').attr('name','slide_data['+index+'][caption_position]');
			$(this).find('input.animation_position').attr('name','slide_data['+index+'][animation_position]');
			
			$(this).find('input.slide-link').attr('name','slide_data['+index+'][link]');
			$(this).find('input.link_target').attr('name','slide_data['+index+'][link_target]');
			
			$(this).find('input.autoslide').attr('name','slide_data['+index+'][autoslide]');
			$(this).find('select.post_type').attr('name','slide_data['+index+'][auto_slide][post_type]');
			$(this).find('select#cat').attr('name','slide_data['+index+'][auto_slide][category]');
			$(this).find('select.orderby').attr('name','slide_data['+index+'][auto_slide][orderby]');
			$(this).find('select.order').attr('name','slide_data['+index+'][auto_slide][order]');
			$(this).find('input.limit').attr('name','slide_data['+index+'][auto_slide][limit]');
			
			$(this).find('input.field-autoslide_type').attr('name','slide_data['+index+'][auto_slide][type]');

			//flickr
			$(this).find('input.field-flickr-apikey').attr('name','slide_data['+index+'][auto_slide][flickr][api_key]');
			$(this).find('input.field-flickr-setid').attr('name','slide_data['+index+'][auto_slide][flickr][set_id]');
			$(this).find('input.field-flickr-user_id').attr('name','slide_data['+index+'][auto_slide][flickr][user_id]');
			$(this).find('select.field-flickr-linkto').attr('name','slide_data['+index+'][auto_slide][flickr][linkto]');
			$(this).find('input.field-flickr-limit').attr('name','slide_data['+index+'][auto_slide][flickr][limit]');
			$(this).find('input.field-flickr-caption_position').attr('name','slide_data['+index+'][auto_slide][flickr][caption_position]');
			$(this).find('input.field-flickr-animation_position').attr('name','slide_data['+index+'][auto_slide][flickr][animation_position]');

			//instagram
			$(this).find('input.field-instagram-access_token').attr('name','slide_data['+index+'][auto_slide][instagram][access_token]');
			$(this).find('input.field-instagram-user_id').attr('name','slide_data['+index+'][auto_slide][instagram][user_id]');
			$(this).find('input.field-instagram-hash').attr('name','slide_data['+index+'][auto_slide][instagram][hash]');
			$(this).find('input.field-instagram-limit').attr('name','slide_data['+index+'][auto_slide][instagram][limit]');
			$(this).find('select.field-instagram-linkto').attr('name','slide_data['+index+'][auto_slide][instagram][linkto]');
			$(this).find('input.field-instagram-caption_position').attr('name','slide_data['+index+'][auto_slide][instagram][caption_position]');
			$(this).find('input.field-instagram-animation_position').attr('name','slide_data['+index+'][auto_slide][instagram][animation_position]');

			//attached
			$(this).find('select.field-attached-linkto').attr('name','slide_data['+index+'][auto_slide][attached][linkto]');
			$(this).find('select.field-attached-caption').attr('name','slide_data['+index+'][auto_slide][attached][caption]');
			$(this).find('input.field-attached-caption_position').attr('name','slide_data['+index+'][auto_slide][attached][caption_position]');
			$(this).find('input.field-attached-animation_position').attr('name','slide_data['+index+'][auto_slide][attached][animation_position]');
		});
	}
	
	//Remove a slide
	$(document).on('click', '#slides .slide header a.delete', function(){
		$(this).parents('.slide').remove();
		slideNumbers();
		return false;
	});
	
	//Duplicate a slide
	$(document).on('click', '#slides .slide header a.duplicate', function(){
		var slide = $(this).parents('.slide');
		var textarea = slide.find('textarea').val();
		slide.clone().find('textarea').val(textarea).parents('.slide').insertAfter(slide);
		slideNumbers();
		return false;
	});

	//Upload a new image
	if ($('#slides').length) {
	var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;
	}
	
	$(document).on('click', '#slides .slide .image a.upload', function(e){
		var send_attachment_bkp = wp.media.editor.send.attachment;
		var button = $(this);
		var image = button.parent();
		var image_field = image.find('input.image-link');
		_custom_media = true;
		wp.media.editor.send.attachment = function(props, attachment){
			if ( _custom_media ) {
			
				image.attr('style','background-image:url('+attachment.url+')').addClass('hasimg');
				image_field.val(attachment.id);
			
			} else {
				return _orig_send_attachment.apply( this, [props, attachment] );
			};
		}
		wp.media.editor.open(button);
		return false;
	});
	
	//Upload a video
	$(document).on('click', '#slides .slide .image a.upload_video', function(e){
		var button = $(this);
		var title = button.attr('title');
		var image = button.parent();
		var image_field = image.find('input.image-link');
		var video_field = image.find('input.video-link');
		
		if (video_field.val()!='') {
		
			button.text('or video');
			button.removeClass('selected nemusicon-cancel-circled');
			video_field.val('');
			
		} else {
			var video = prompt(title,'http://www.youtube.com/watch?v=OmLNs6zQIHo');
			
			function processURL(url){
				var id;
				var image;
				var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
				
				if (url.indexOf('youtube.com') > -1) {
					id = url.split('v=')[1].split('&')[0];
					processYouTube(id);
				} else if (url.indexOf('youtu.be') > -1) {
					id = url.split('/')[1];
				    processYouTube(id);
				} else if (url.match(regExp)) {
				   
					id = url.match(regExp);
					id = id[5];
				        
					$.ajax({
						url: 'http://vimeo.com/api/v2/video/' + id + '.json',
						dataType: 'jsonp',
						success: function(data) {
							image = data[0].thumbnail_large;
								inset_video(image);
						}
					});
				    
				}
				
				function processYouTube(id) {
				    if (!id) {
				        throw new Error('Unsupported YouTube URL');
				    }
				    image = 'http://i2.ytimg.com/vi/' + id + '/maxresdefault.jpg';
				    
				    inset_video(image);
				}
				
			}
			
			processURL(video);		
			
			function inset_video(video_img) {
				video_field.val(video);
				image_field.val(video_img);
				image.attr('style','background-image:url('+video_img+')').addClass('hasimg');
				
				button.text('Video selected');
				button.addClass('selected nemusicon-cancel-circled');
				
			}
		}
		return false;
	});
	
	$('.add_media').on('click', function(){
		_custom_media = false;
	});
	
	//Caption position
	$(document).on('click', '.caption_position a', function(e){
		if($(this).hasClass('active')) {
			$(this).removeClass('active');
			var val = 'html';
		} else {
			$(this).parent().find('a').removeClass('active');
			$(this).addClass('active');
			var val = 'caption '+$(this).attr('href');
		}
		$(this).parent().find('input').val(val);
		return false;
	});

	//Caption animation direction
	$(document).on('click', '.animation_direction a', function(e){
		var positions = ['top','none','right','bottom','left'];
		var current = $(this).attr('href');
		var current_pos = $.inArray(current, positions);
		
		if (current == 'left') {
			$(this).attr('href','top');
		} else {
			$(this).attr('href',positions[current_pos+1]);
		}
		
		var new_post = $(this).attr('href');
		$(this).parent().find('input').val(new_post);
		
		return false;
	});	
	
	//Link target
	$(document).on('click', 'a.link_target', function(e){
		$(this).toggleClass('active');
		if ($(this).hasClass('active')) {
			$(this).parent().find('.link_target_input').val('1');
		} else {
			$(this).parent().find('.link_target_input').val(0);
		}
		return false;
	});
	
	//Animation type
	$('.slider-extra-checkbox.fade-type a').click(function(){
		$(this).parent().find('strong').removeClass('active');
		if ($(this).hasClass('on')) {
			$(this).removeClass('on');
			$(this).parent().find('input').val('fade');
			$(this).parent().find('strong.fade').addClass('active');
		} else {
			$(this).addClass('on');
			$(this).parent().find('input').val('slide');
			$(this).parent().find('strong.slide').addClass('active');
		}
		return false;
	});
	
	//Orientation type
	$('.slider-extra-checkbox.orientation-type a').click(function(){
		$(this).parent().find('strong').removeClass('active');
		if ($(this).hasClass('on')) {
			$(this).removeClass('on');
			$(this).parent().find('input').val('horizontal');
			$(this).parent().find('strong.horizontal').addClass('active');
		} else {
			$(this).addClass('on');
			$(this).parent().find('input').val('vertical');
			$(this).parent().find('strong.vertical').addClass('active');
		}
		return false;
	});
	
	//Color picker
	if ($('.slider-extra').length) $("input.color").wpColorPicker();
		
	//Select skin
	//$(".nemus-chosen").chosen({disable_search: true});
	
	//Image Scale Mode
	$('.nemus-slider-option.image-scale-option .image-scale-options a').click(function(){
		var val = $(this).data('type');
		$(".nemus-slider-option.image-scale-option select").val(val);
		$('.image-scale-options a').removeClass('active');
		$(this).addClass('active');
		return false;
	});

	//Carousel toggle
	$('input[name="slider_options[carousel]"]').change(function(){
		if ($(this).is(':checked')) {
			$('#carousel-fields').addClass('visible');
		} else {
			$('#carousel-fields').removeClass('visible');
		}
	});
	
	//Purchase code validation
	$('.nemus_slider-auto-update').submit(function(e) {
		e.preventDefault();

		$('.nemus_slider-auto-update tfoot span').text('Validating ...').css('color', '#333');

		$.post( ajaxurl, $(this).serialize(), function(data) {

			data = jQuery.parseJSON(data);

			$('.nemus_slider-auto-update tfoot span').text(data['message']);

			if(data['success'] == true) {
				$('.nemus_slider-auto-update tfoot span').css('color', '#4b982f');
			} else {
				$('.nemus_slider-auto-update tfoot span').css('color', '#c33219');
			}
		});
	});
	
	$('.nemus-slider-unlicenced_copy,.nemus-slider-licenced_copy').click(function(){
		$('.nemus_slider-auto-update').show();
		return false;
	});
	
	//Auto slide type	
	$('#nemus_slider_slides').on('click', '.auto-slide-type a', function(e){
		
		$(this).parent().find('a').removeClass('active');
		$(this).addClass('active');
		
		var type = $(this).data('type');
		$(this).parent().parent().find('input').val(type);

		var eq = $(this).index();
		
		$(this).parent().parent().parent().find('.auto-slide-type-tab').hide();
		$(this).parent().parent().parent().find('.auto-slide-type-tab').eq(eq).show();

		return false;
	});	
	
	//Advanced settings
	$('.nemus-slider-advanced-settings').click(function(){
		$('.nemus-slider-advanced-settings-section').toggle();
		return false;
	});

});