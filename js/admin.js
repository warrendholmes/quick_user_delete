jQuery(document).ready(function($){

	$('.quick_user_delete').click(function(e){

		e.preventDefault();

		if (confirm("Are you sure you want to delete this user?")) { 
				
			window.location = $(this).attr('href');

		}else{

			return false;

		}
	});

});