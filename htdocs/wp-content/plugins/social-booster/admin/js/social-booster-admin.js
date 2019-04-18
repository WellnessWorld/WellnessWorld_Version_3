(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(document).ready(function($){

		var flag_ok = false;
		$('#publish').on('click', function(e){
		var x =	$(this).val();
		if(x=='Publish' || x== 'Update') {
				if ( ! flag_ok ) {
			        e.preventDefault();

			        var postid = $("#post_ID").val();
				 	var post = $("#soctextareasmall").val();
				 	var schno = $("#post_ID").val();
				 	 var social = $('input[name="socmulticheckbox[]"]:checked').serialize();
				 	 var times = $("input[name='socradio_inline']:checked").val();
				 	 var postnow = $("input[name='socsocradio_inlinepostnow']:checked").val();
				 	 var url = $("#sample-permalink a").text();
				 	 var imgpath = $(".inside a img").attr('src');

					  	jQuery.ajax({

						    type:    "POST",
						    url:     ajaxurl,
						    data: { 
						      security: socialbooster_ajax_object.ajax_nonce,
						      action: "meta_results",
						      post: post,
						      schno: schno,
						      social: social,
						      postnow: postnow,
						      times: times,
						      url: url,
						      imgpath: imgpath,
						      postid: postid
						      },
			    	
					    	success: function( response ){
					    		flag_ok = true;
					      		$('#publish').trigger('click');
				    		}
				  		});
			    }   
		    } 
		});
	
    });

    jQuery(document).ready(function($){

		$('.delform').submit(function(e){
			e.preventDefault();
			$('#loadimg').show();
		    $('#deleteform').attr('disabled' , true);
			jQuery(this).closest(".tr").hide();
		  	var newFormRecherche = $(this).serialize();

		  	var name = $("#fullname").val();

		  	jQuery.ajax({

			    type:    "POST",
			    url:     ajaxurl,
			    data: { 
			      security: socialbooster_ajax_object.ajax_nonce,
			      action: "delete_form",
			      newFormRecherche: newFormRecherche
			    },
		    	
			    	success: function( response ){
			      		console.log( response );
			      		$('#loadimg').hide();
			      		jQuery(this).closest(".tr").hide();
						$(this).closest('.tr').hide();
						$('#deleteform').attr('disabled' , false);

			    	}
		  	});

		  return false;
		});
	});

	jQuery(document).ready(function($){

		$('#twform').submit(function(e){
			e.preventDefault();
		    $('#loadimg').show();
		    $('#submit_form').attr('disabled' , true);

				var data = {
					security: socialbooster_ajax_object.ajax_nonce,

					action: 'tw_results',
					'usernametw'              : $('input[name=usernametw]').val(),
		            'conkeytw'             : $('input[name=conkeytw]').val(),
		            'consecrettw'    : $('input[name=consecrettw]').val(),
		            'accesstokentw'    : $('input[name=accesstokentw]').val(),
		            'accesstokensecrettw'    : $('input[name=accesstokensecrettw]').val()
				};

				$.post(ajaxurl, data, function(response) {
					console.log(response.success);
					$('#loadimg').hide();
					if(response.error){
						$('#twsavedinfo').html(response.error);
					}else{
						window.location.replace(response.url);
					}
		      		
					
					$('#submit_form').attr('disabled' , false);
					
				}, 'json');
				return false;
			});
		});

	   jQuery(document).ready(function($){

		$('#insform').submit(function(e){
			e.preventDefault();
			$('#loadimg').show();
		    $('#insform').attr('disabled' , true);		  	
		  	var usernameins = $(".usernameins").val();
		  	var pasins = $(".passwordins").val();
		  	jQuery.ajax({

			    type:    "POST",
			    url:     ajaxurl,
			    data: { 
			      security: socialbooster_ajax_object.ajax_nonce,
			      action: "instagram_form",
			      usernameins: usernameins,
			      pasins: pasins,
			    },
		    	
			    success: function( response ){
	                jQuery('#loadimg').hide();
	                jQuery('#yesimg').show();
	                jQuery('#savedmessage').show();
	                jQuery('#insform').attr('disabled' , false);
	                location.reload();
			    }
		  	});
		  return false;
		});
	});

	jQuery(document).ready(function($){

		$('.authbtn').on('click', function(e){
			e.preventDefault();
			$('#loadimg1').show();
		    $('.authbtn').attr('disabled' , true);		  	
		  	var authcode = $("#authcode").val();
		  	jQuery.ajax({

			    type:    "POST",
			    url:     ajaxurl,
			    data: { 
			      security: socialbooster_ajax_object.ajax_nonce,
			      action: "instagram_auth",
			      authcode: authcode,
			    },
		    	
			    success: function( response ){
	                jQuery('#loadimg1').hide();
	                jQuery('.authbtn').attr('disabled' , false);
	                location.reload();
			    }
		  	});
		  return false;
		});
	});

	jQuery(document).ready(function($){

		$('.fabform').submit(function(e){
			e.preventDefault();
			$('#loadimg').show();
		  	var fbformdata = $(this).serialize();		  	
		  	var appid = $(".fbappid").val();
		function init() {
			
		  	FB.login(function(response) {
			  if (response.authResponse) {
			  	console.log(response);
			        console.log('You are logged in &amp; cookie set!');
			       
			      } else {
			        console.log('User cancelled login or did not fully authorize.');
			      }
			    },{scope: 'publish_pages,manage_pages,pages_show_list'});
		}
			  window.fbAsyncInit = function() {
			    FB.init({
			      appId: appid,
			      cookie: true, // This is important, it's not enabled by default
			      version: 'v2.2'
			    });
			    init();
			  };

			  (function(d, s, id){
			    var js, fjs = d.getElementsByTagName(s)[0];
			    if (d.getElementById(id)) {return;}
			    js = d.createElement(s); js.id = id;
			    js.src = "https://connect.facebook.net/en_US/sdk.js";
			    fjs.parentNode.insertBefore(js, fjs);
			  }(document, 'script', 'facebook-jssdk'));

		  	jQuery.ajax({

			    type:    "POST",
			    url:     ajaxurl,
			    data: { 
			      security: socialbooster_ajax_object.ajax_nonce,
			      action: "fb_results",
			      fbformdata: fbformdata
			    },
		    	
		    	success: function( response ){
		    		jQuery('#loadimg').hide();
		    		if (response.success == false) {
		    			jQuery('#fbsavedinfo').html(response.data);
		    		}
		    		else{
			    		jQuery('#yesimg').show();
		                jQuery('#savedmessage').show();	                
		    		}
		    	}
		  	});
		});
	});  

})( jQuery );
