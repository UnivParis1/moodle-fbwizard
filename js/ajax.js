  $(function() {
	function validation() {
		$.ajax({
		    type: "POST",
		    url: "validation.php",
		    data : $("#form_validation").serialize(),
		    success:
		    function(retour){
		    	$("#pbox-title-focus").html('Récapitulatif');
		    	$("#pbox-focus").html(retour).fadeIn();
		        $('#box-focus').css('visibility', 'visible');
		        $('.focus').css('visibility', 'visible');
		        $('html,body').animate({scrollTop: $("#box-focus").offset().top}, 'slow');
		    }
		});	
	}
	
	function closebox() {
		$('#box-focus').css('visibility', 'hidden');
		$('.focus').css('visibility', 'hidden');
	}


}