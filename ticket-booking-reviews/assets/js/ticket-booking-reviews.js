(function(){
	function updateQuantity(form){
		var checked=form.querySelectorAll('input[name="tbr_seats[]"]:checked').length;
		var quantity=form.querySelector('input[name="quantity"]');
		if(!quantity){quantity=document.createElement('input');quantity.type='hidden';quantity.name='quantity';form.appendChild(quantity);}
		quantity.value=Math.max(1,checked);
	}
	function toggleSeat(target){
		var seat=target.closest('.tbr-seat');
		if(seat){seat.classList.toggle('is-selected',target.checked);}
	}
	document.addEventListener('change',function(event){
		if(event.target.matches('input[name="tbr_seats[]"]')){toggleSeat(event.target);updateQuantity(event.target.form);}
	});
	document.addEventListener('submit',function(event){
		var booking=event.target.closest('.tbr-booking');
		if(!booking){return;}
		updateQuantity(event.target);
		if(!event.target.querySelector('input[name="tbr_seats[]"]:checked')){event.preventDefault();window.alert('Please select at least one seat before booking.');}
	});
}());
