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


});
$(document).ready(function() {
  $("#search").keyup(function() {
    _this = this;
    $.each($('#mytable tbody tr'), function() {
    	show = false;
    	$(this).hide();
    	 $.each($(this)[0]['children'], function(){
    	 	if ($(this)[0]['textContent'].toLowerCase().indexOf($(_this).val().toLowerCase()) !== -1)
                    show = true;
    	 });
      if (show)
        $(this).show();        
    });
  });
});