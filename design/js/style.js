window.dataLayer = window.dataLayer || [];

function oil_cleaner(alerts) {
	return function() {
		if (alerts.is(':visible')) {
			alerts.slideToggle(300, function () {alerts.remove()});
		} else {
			alerts.remove();
		}
	};
};

function oil_alert(text, type) {
	var css = '';
	if (typeof(type)!=='undefined') {
		css = ' alert-'+type;
	}
	$('#drawer').append('<div class="alert'+css+'"><button type="button" class="close" data-dismiss="alert">&times;</button>'+text+'</div>');
	setTimeout(oil_cleaner($('div.alert')), 5000);
}

var oil_timer = 0;
function oil_timed() {
	if (oil_timer != 0) {
		clearTimeout(oil_timer);
	}
	oil_timer = setTimeout(oil_submit, 2000);
}
function oil_submit() {
	$("form.auto").submit();
}

function basket_x() {
	$('button.buyx').click(function(e) {
//		e.preventDefault();
//		e.stopPropagation();
		var $i = $(this);

		var a = gtm_productInfo($i);
		gtm_removeCartItems([a]);

		//$i.ne.remove();
		basket_remove($i.data('i'));
	});
}

function basket_remove(store) {
	$.ajax({
		url: '/basket/buy',
		type: 'POST',
		data: {'i':store,'c':0},
		dataType: 'html',
		success: function (data, status, jqxhr) {
			$('#menu-basket').html(data);
			basket_x();
//			oil_alert('Товар удалён из корзины!', 'success');
//			if ($('#basket').find('a.item').length == 0) {
//				$('#basket').trigger('click');
//			}
		},
		error: function (data, status, jqxhr) {
			oil_alert('Ошибка покупки: ' + status);
		}
	});
}

function basket_add(store, count) {
	$.ajax({
		url: '/basket/buy',
		type: 'POST',
		data: {'i':store,'c':count},
		dataType: 'html',
		success: function (data, status, jqxhr) {
			$('#menu-basket').html(data);
			basket_x();
			oil_alert('Товар добавлен в заказ! Перейдите <a href="/basket">в Корзину</a> для оформления.', 'success');
/*
			// <!-- Conversion tracking code: purchases. Step 1: Product added to cart -->
			(function(w, p) {
				var a, s;
				(w[p] = w[p] || []).push({
					counter_id: 418529050,
					tag: 'c69e36a4155019c4c59e8dd565bafc49'
				});
				a = document.createElement('script'); a.type = 'text/javascript'; a.async = true;
				a.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'autocontext.begun.ru/analytics.js';
				s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(a, s);
			})(window, 'begun_analytics_params');
*/
		},
		error: function (data, status, jqxhr) {
			oil_alert('Ошибка покупки: ' + status);
		}
	});
}

$(function() {
	basket_x();

	$('.buy').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		var count = $('.product_inbasket');
		count = count.length ? count.val() : 1;
		var $btn = $(this);

		var a = gtm_productInfo($btn);
		a['quantity'] = count;
		gtm_addCartItems([a]);

		basket_add($btn.data('i'), count);
		return false;
	});
	setTimeout(oil_cleaner($('#drawer div.alert')), 10000);

	$('.bl_close').click(function(e) {
		var $btn = $(this);
		var a = gtm_productInfo($btn);
		a['quantity'] = cnt ? cnt : 1;
		gtm_removeCartItems([a]);
	});

	$('button[name=basket-clean]').click(function(){
		var products = [];
		$('div#cartContent div.line').each(function(ii, ele) {
			var product = gtm_productInfo($(ele));
			product['quantity'] = $(ele).find('input.count').val();
			products.push(product);
		});
		gtm_removeCartItems(products);
		return true;
	});

	$('div#cartContent div.line input.count').each(function(ii, ele) {
		$(ele).prop('prevcount', $(ele).val());
	});

	$('button.fileplus').click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		var $plus = $(this);
		var $div = $plus.closest('div');

		$plus.before('<br />');
		$plus.before($div.find('span.filesel:first').clone());
		$plus.before(' ');
		$plus.before($div.find('input.filesel:first').clone());
		$plus.before(' ');
	});

	$('input.auto').bind('input', oil_timed);
	$('input.auto').bind('change', oil_submit);
	$('input.auto2').bind('change', oil_timed);
	$('select.auto').bind('change', oil_submit);
	$('select.auto2').bind('change', oil_timed);

	$('.dropdown .checkbox').click(function(e) {e.stopPropagation();});
});

function gtm_elementIsVisible(item)
{
	var wnd = $(window);
	var wndtop = wnd.scrollTop();
	var itemtop = item.offset().top;
	var itembottom = itemtop + item.height();
	return ((itembottom <= wndtop + wnd.height()) && (itemtop >= wndtop));
}
function gtm_addCartItems(products)
{
	console.log('adding ' + products[0]['name'] + ' products to cart');
	dataLayer.push({'event':'gtm-ee-event',
		'gtm-ee-event-category':'Enhanced Ecommerce',
		'gtm-ee-event-action':'Adding a Product to a Shopping Cart',
		'gtm-ee-event-non-interaction':'False',
		'ecommerce':{'currencyCode':'RUB','add':{'products': products}}});
}

function gtm_removeCartItems(products)
{
	console.log('removing ' + products[0]['name'] + ' products from cart');
	dataLayer.push({'event':'gtm-ee-event',
		'gtm-ee-event-category':'Enhanced Ecommerce',
		'gtm-ee-event-action':'Removing a Product from a Shopping Cart',
		'gtm-ee-event-non-interaction':'False',
		'ecommerce':{'currencyCode':'RUB','remove':{'products': products}}});
}

function gtm_productInfo(btn)
{
	var $btn = $(btn);
	var product = {'id': $btn.data('i')};
	if($btn.prop('ordernum')) product['position'] = $btn.prop('ordernum');
	if($btn.data('price')) product['price'] = $btn.data('price');
	if($btn.data('name')) product['name'] = $btn.data('name');
	if($btn.data('brand')) product['brand'] = $btn.data('brand');
	if($btn.data('category')) product['category'] = $btn.data('category');
	if($btn.data('count')) product['quantity'] = $btn.data('count');

	return product;
}
function gtm_checkScrollingItems()
{
	var prods = [];
	$('.buy.notshown:visible').each(function(ii, ele){
		var $ele = $(ele);
		if(! $ele.prop('ordernum')) $ele.prop('ordernum', ii + 1);
		if(gtm_elementIsVisible($ele)) {
			$ele.removeClass('notshown');
			var product = gtm_productInfo($ele);
			var listname = gtm_listname($ele);
			if(listname) product['list'] = listname;
			prods.push(product);
		}
	});
	if(prods.length)
	{
		dataLayer.push({
			'ecommerce': {
				'currencyCode': 'RUB',
				'impressions': prods
			},
			'event': 'gtm-ee-event',
			'gtm-ee-event-category': 'Enhanced Ecommerce',
			'gtm-ee-event-action': 'Product Impressions',
			'gtm-ee-event-non-interaction': 'True',
		});
	}
}

function gtm_listname($obj)
{
	var sect = $obj.closest('section[id]');
	var listname = '';
	if(sect.length)
	{
		if(sect.attr('id') == 'sale') listname = 'Популярные товары';
	}
	if(! listname && /^\/?catalog/.test(document.location.pathname))
		$('header#page-title ul.breadcrumb li,header#page-title h1').each(function(ii, ele) {
			listname += (listname ? '/' : '') + $(ele).text();
		});
	return listname;
}

$(function(){
	$('.buy').addClass('notshown');
	$('a.item_imgcont, a.catalog_name-product').click(function(){
		var urlhref = $(this).attr('href');
		var btn = $(this).closest('div.catalog_item').find('.buy');
		if(btn.length)
		{
			var product = gtm_productInfo(btn);
			var listname = gtm_listname(btn);
			dataLayer.push({
				'ecommerce': {
					'currencyCode': 'RUB',
					'click': {
						'actionField': {'list': listname},
						'products': [product]
					}
				},
				'event': 'gtm-ee-event',
				'gtm-ee-event-category': 'Enhanced Ecommerce',
				'gtm-ee-event-action': 'Product Clicks',
				'gtm-ee-event-non-interaction': 'False',
				'eventCallBack': function(){document.location = urlhref;}
			});
			setTimeout(function(){document.location = urlhref;}, 250);
			return false;
		}
	});
  $.fn.scrollToTop=function(){
	$(this).hide().removeAttr("href");
	if($(window).scrollTop()!="0"){
		$(this).fadeIn("slow")
  }
  var scrollDiv=$(this);
  $(window).scroll(function(){
	  gtm_checkScrollingItems();
	if($(window).scrollTop()=="0"){
	$(scrollDiv).fadeOut("slow")
	}else{
	$(scrollDiv).fadeIn("slow")
  }
  });
	$(this).click(function(){
	  $("html, body").animate({scrollTop:0},"slow")
	})
  }
	gtm_checkScrollingItems();
});
$(function() {$("#toTop").scrollToTop();});