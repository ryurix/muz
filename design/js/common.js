// function declarations:
// cookie
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}
function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i = 0; i < ca.length; i++) {
	var c = ca[i];
	while (c.charAt(0) == ' ') {
		c = c.substring(1);
	}
	if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
	}
	}
	return "";
}
function checkCookie() {
	var user = getCookie("username");
	if (user != "") {
		alert("Welcome again " + user);
	} else {
		user = prompt("Please enter your name:", "");
		if (user != "" && user != null) {
			setCookie("username", user, 365);
		}
	}
}





$(document).ready(function() {
	// mobile menu
	$("#sm_btn").on("click", function(){
		$("#sm_menu").show();
	});
	$("#sm_menu").on("click", "button.sm_btn-close", function(){
		$("#sm_menu").hide();
	});

	//scroll add .scroll to buttons for slowly move to anchor
	$('.scroll').bind('click.smoothscroll',function (e) {
		e.preventDefault();

		var target = this.hash,
		$target = $(target);

		$('html, body').stop().animate({
			'scrollTop': $target.offset().top
		}, 900, 'swing', function () {
			window.location.hash = target;
		});
	});
/*
	// прилипшое меню к верху на моб устройствах
	window.onscroll = function(){
		if( window.innerWidth < 992 && window.pageYOffset > 380){
			$(".header_manu").addClass("fixed");
		}else{
			$(".header_manu").removeClass("fixed");
		}
	}
*/
	if( window.innerWidth > 992){
		window.onscroll = function(){
			if(  window.pageYOffset > 250){
				$(".header_fixed").addClass("slick");
			}else if(  window.pageYOffset < 250 ){
				$(".header_fixed").removeClass("slick");
			}
		};
	}

	$(document).on("scroll", function(){
		if( window.pageYOffset >= 300) {
			$("footer .toTop").show();
		}else{
			$("footer .toTop").hide();
		}
	});



// header
	// Личный кабинет меню
	var personal 		= $("#personal");
	var personalMenu  	= $("#personal_menu");

	personal.on("click", function(e){
		//показывает/скрываем меню
		personalMenu.slideToggle(300);
	});
	$(document).mouseup(function (e) {
		var itemRing  = $("#personal");
		var container = $("#personal_menu");
		if ( !container.is(e.target)  && !itemRing.is(e.target) &&  container.is(":visible") ) {
		  personalMenu.slideToggle(300);
		}
	});

// popup Ваш город
	// $(".city_item").on("click", function(){
	// 	var dataCity = $(this).data("city");
	// 	$("#city_input").val(dataCity);
	// });

// КОРЗИНА
	// Корзина - показывает список товаров
	var basket 		= $("#menu-basket");

	basket.on("click", "button#basket", function(e){
		$("#basket_list").slideToggle(300, function(){
			// console.log("click on btn basket");
		});
	});

	 // закрываем окно при клике вне вып.меню
	$(document).click(function (e) {
	    var basket2   	= $("#basket");
	    var container 	= $("#basket_list");
	    var close 		= $(".bl_close");

	    // if ( !basket2.is(e.target) && !container.is(e.target) && container.is(":visible")){
	    if ( !basket2.is(e.target) && !container.is(e.target) && container.has(e.target).length === 0 && container.is(":visible")){
	    	// console.log(e.target);
	    	if ( $(e.target).hasClass('bl_close') ) return;

	        container.slideUp();
	    }

	});

	/*
	// удаляем с корзины товар с динамически генерируемым html
	$('body').on('click', '.bl_close', function(){
		var IDItemInCart = $(this).parents(".bl_item").data('id');
		console.log(IDItemInCart);
		removeIconFromItemInCatalog(IDItemInCart);

		$(this).parents(".bl_item").remove();
		checkItemsInCart();
	});

	// проверяем есть ли в корзине товар
	function checkItemsInCart(){
		if( document.getElementsByClassName("bl_item").length === 0 ){
			$(".bl_empty").show();
			$(".bl_btncheckout").addClass("transparent");
		}else{
			$(".bl_empty").hide();
			$(".bl_btncheckout").removeClass("transparent");
		}
	}
	// удаляем иконку на которой стоит зеленая галочка
	function removeIconFromItemInCatalog(id){
		var btn = $(".catalog_items .catalog_item[data-id="+id+"] .catalog_addToCard");
		btn.removeClass("added");
		btn.find("i").removeClass("fa-check").addClass("fa-shopping-cart");
	}

	// Добавить в козину с КАТАЛОГА
	$(".catalog_addToCard").on("click", function(){
		if( $(this).hasClass("added") ){
			// $(this).removeClass("added");
		}else{
			$(this).addClass("added");

			var parent 	= $(this).parents(".catalog_item");
			var id 		= parent.data("id");
			var name 	= parent.find(".catalog_name-product").html();
			var href 	= parent.find(".catalog_name-product").prop("href");
			var price 	= parent.find(".catalog_price").html();
			var img 	= parent.find(".item_img").prop("src");

			addToCart(name, id, href, price, img);
			checkItemsInCart();

			$(this).find("i").removeClass("fa-shopping-cart").addClass("fa-check");
		}
	});

	function addToCart(name, id, href, price, img){
		var html = "<li class=\"bl_item\" data-id=\""+ id +"\">\
			<div class=\"bl_section1\">\
				<img src=\""+ img +"\" class=\"img-fluid bs_img\">\
			</div><div class=\"bl_section2\">\
				<button type=\"button\" class=\"bl_close\">&times;</button>\
				<a  href=\""+ href +"\" class=\"bl_title\">"+ name +"</a>\
				<p class=\"bl_price\"><span class=\"bl_count\">1</span> x <span class=\"bl_pricein\">"+ price +"</span></p>\
			</div></li>";

		$("#basket_list .bl_items").append( html );
	}
	*/






// Главная
	if( document.getElementById("bootstrap_slider") ){

		$(".about_toggle").on("click", function(){
			$(this).hide();
			$("#hide_section").toggle();
			$(".about_hidden").toggle();
		});

	}

// Каталог
	// муз. инструменты - скрывающиесе меню
	var catalog = $(".catalog_submenu");
	if( window.innerWidth < 990 ){
		// $(".catalog_btnsubmenu").addClass("opened");
	}
	if( $(".catalog_btnsubmenu").hasClass("opened") ){
		catalog.slideUp(0);
	}else{
		catalog.slideDown(0);
	}

	$(".catalog_btnsubmenu").on("click", function(){
		if( $(this).hasClass("opened") ){
			$(this).removeClass("opened");
			catalog.slideDown();
		}else{
			$(this).addClass("opened");
			catalog.slideUp();
		}
	});

	// фильтр
	var filter_subtitle = $(".filter_subtitle");
	var filter_list 	= $(".filter_list");

	if( window.innerWidth < 990 ){
		$(".filter_subtitle").toggleClass("opened");
	}

	if( $(".filter_subtitle").hasClass("opened") ){
		filter_list.slideUp(0);
	}else{
		filter_list.slideDown(0);
	}


	$(".filter_subtitle").on("click", function(){
		var id = $(this).data( "list");
		$(this).toggleClass("opened");
		$(".filter_list[data-list="+id+"]").slideToggle(100);
	});
	$(".filter_expand").on("click", function(){
		var id = $(this).data( "list");
		$(".filter_list[data-list="+id+"]").addClass("expand").removeClass("show_more");
		// console.log("expand");
		$(this).hide();
	});


	// кастомизация select
	$('#modal-city').on('show.bs.modal', function() {
		$('#modal-city .modal-body').load('/city-dialog', function() {
			setTimeout(function() {
					$('#city_select').styler({
					selectSearch: true,
					selectPlaceholder: "Выберите город"
				});
			}, 100);
		});
	});

	$('#modal-help').on('show.bs.modal', function (event) {
		var data = $(event.relatedTarget).data('load');
		data+= data.indexOf('?') > 0 ? '&' : '?';
		$('#modal-help .modal-body').load('/' + data + '_win');
	});

	if( document.getElementsByClassName('basket2_select').length ){
		$('.basket2_select').styler({
			selectSearch: true,
			selectPlaceholder: "Выберите город"
		});
	}

	if( document.getElementsByClassName('catalog_dd').length ){
		$('.catalog_dd').styler();
	}


// Product
	if( document.getElementById("product_owl") ){
	  $("#product_owl").owlCarousel({
			loop: true,
			margin: 10,
			responsiveClass: true,
			items: 1
		});
	}

	$(function () {
	  $('[data-toggle="tooltip"]').tooltip();
	})

	$(".product_tab").on("click", function(){
		$(".product_tab").removeClass("current");
		$(this).addClass( "current");
		var id = $(this).data( "prodbtn");

		var $tabs = $(".product_description");
		$tabs.children('div[data-area]').hide(0);
		$tabs.children("div[data-area="+id+"]").removeClass("d-none").show(0);
	});

    $('.minus').click(function () {
        var $input = $(this).parent().find('input');
        var count = parseInt($input.val()) - 1;
        count = count < 1 ? 1 : count;
        $input.val(count);
        $input.change();
        return false;
    });
    $('.plus').click(function () {
        var $input = $(this).parent().find('input');
        $input.val(parseInt($input.val()) + 1);
        $input.change();
        return false;
    });

	$(".basket_deletebtn").on("click", function(){
		$(this).parents("tr").remove();
		if( $(".basket_table tbody tr").length === 0){
			$(".basket_table tbody").append('\
				<tr>\
					<td colspan="6" class="basket_empty">Корзина пустая!</td>\
				</tr>\
				');
			// console.log("yes");
		}
		oil_timed();
	});

	// иконки скидки
	var curIcon;
	$(".product_icon").on("mouseover", function(){
		var current = $(this).data("icon");
		$(".product_discountcode").find(".product_icondescr").removeClass("active");
		$(".product_discountcode").find(".product_icondescr[data-icon="+ current +"]").addClass("active");
	});


// end product


//basket
	if( document.getElementById('contact_phone') ){
		$("#contact_phone").mask("+9(999) 999 99 99");
	}
	if( document.getElementById('ur_checkbox') ){
		 $("#ur_checkbox").change(function(){
			if( $("#ur_checkbox").prop("checked") === true){
				$(".ur_cont").slideDown();
			}else{
				$(".ur_cont").slideUp();
			}
		 });
	}

	jQuery.validator.setDefaults({
		errorPlacement: function(error,element) {
			return true;
		},
		highlight: function (element, errorClass, validClass) {
			$(element).addClass('is-invalid');
		},
		unhighlight: function (element, errorClass, validClass) {
			$(element).removeClass('is-invalid');
		}
	});

	jQuery.extend(jQuery.validator.messages, {
			required: 	"Обязательное поле.",
			remote: 	"Пожалуйста исправьте это поле.",
			email: 		"Пожалуйста введите корректный email адрес.",
			url: 		"Введите корректный URL.",
			date: 		"Введите корректный дату.",
			dateISO: 	"Введите корректный дату (ISO).",
			number: 	"Введите корректный номер.",
			digits: 	"Please enter only digits.",
			creditcard: "Please enter a valid credit card number.",
			equalTo: 	"Please enter the same value again.",
			accept: 	"Please enter a value with a valid extension.",
			maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
			minlength: jQuery.validator.format("Please enter at least {0} characters."),
			rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
			range: jQuery.validator.format("Please enter a value between {0} and {1}."),
			max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
			min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
	});
	$("#signin_form").validate({
		rules: {
			email_phone: {
				required: true
			},
			password: {
				required: true
			}
		},
		messages: {
			email_phone: {
				required: "Обязательное поле"
			},
			password: {
				required: "Обязательное поле",
			}
		}
		/*,
		submitHandler: function(form){
			var dataBlock = $('#signin_form').serializeArray();
			$.ajax({
				url: '/basket/next',
				type: 'POST',
				data: dataBlock,
				success: function(data){
					// do some thing
					$('#signin_form')[0].reset();
				},
				fail: function(data){
					// do some thing
					console.log("fail");
				},
			});
		}
		*/
	});

	$("#basket2_form").validate({
		rules: {
			name: {
				required: true,
				minlength: 2
			},
			phone: {
				required: true,
				minlength: 11
			}
/*
			,city: {
				required: true,
				minlength: 2
			}
*/
		},
		messages: {
			name: {
				required: "Обязательное поле",
				minlength: "Минимальная длинна 2"
			},
			phone: {
				required: "Обязательное поле",
				minlength: "Минимальная длинна 11"
			}
/*
			,city: {
				required: "Обязательное поле",
				minlength: "Минимальная длинна 2"
			}
*/
		}
		/*,
		submitHandler: function(form){
			var dataBlock = $('#basket2_form').serializeArray();
			$.ajax({
				url: 'hendler.php',
				type: 'POST',
				data: dataBlock,
				success: function(data){
					// do some thing
					$('#basket2_form')[0].reset();
				},
				fail: function(data){
					// do some thing
					console.log("fail");
				},
			});
		}
		*/
	});




//end ready
});
