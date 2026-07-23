(function(){
	function updateQuantity(form){
		var checked=form.querySelectorAll('input[name="tbr_seats[]"]:checked').length;
		var quantity=form.querySelector('input[name="quantity"]');
		if(!quantity){quantity=document.createElement('input');quantity.type='hidden';quantity.name='quantity';form.appendChild(quantity);}
		quantity.value=Math.max(1,checked);
	}
	document.addEventListener('change',function(event){
		if(event.target.matches('input[name="tbr_seats[]"]')){updateQuantity(event.target.form);}
	});
	document.addEventListener('submit',function(event){
		if(event.target.closest('.tbr-booking')){updateQuantity(event.target);}
	});
}());
