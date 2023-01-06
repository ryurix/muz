function sync_load(range) {
	$.ajax({
		url: '/sync/sync2',
		type: 'POST',
		data: {'range':range},
		dataType: 'html',
		success: function (data, status, jqxhr) {
			$('#sync2').closest('tr').remove();
			$('#sync').find('tbody').append(data);
		},
		error: function (data, status, jqxhr) {
			oil_alert('Ошибка загрузки: ' + status);
		}
	});
}

$(function() {
	$('table#sync').on('change', 'select.sync', function() {
		var sel = $(this);
		sel.closest('td').find('input').val(sel.val());
	});
	$('table#sync').on('click', 'button.sync', function() {
		var btn = $(this);
		var td = btn.closest('td');

		td.find('button').attr('disabled', 'disabled');
		btn.addClass('btn-success');

		$.ajax({
			url: '/sync',
			type: 'POST',
			data: {
				'name':btn.closest('tr').find('span.sync').text(),
				'vendor':$('select.vendor').val(),
				'code':btn.data('code'),
				'store':td.find('input').val()
			},
			dataType: 'html',
			success: function (data, status, jqxhr) {
				if (data.length) {
					oil_alert(data, 'warning');
				}
			},
			error: function (data, status, jqxhr) {
				oil_alert("Синхронизация не удалась", 'danger');
			}
		});
	});
	$('table#sync').on('click', 'button.nosync', function() {
		var btn = $(this);
		var td = btn.closest('td');

		td.find('button').attr('disabled', 'disabled');
		btn.addClass('btn-danger');
		$.ajax({
			url: '/sync',
			type: 'POST',
			data: {
				'name':btn.closest('tr').find('span.sync').text(),
				'vendor':$('select.vendor').val(),
				'code':btn.data('code'),
				'store':0
			},
			dataType: 'html',
			success: function (data, status, jqxhr) {
				if (data.length) {
					oil_alert(data, 'warning');
				}
			},
			error: function (data, status, jqxhr) {
				oil_alert("Отмена синхронизации не удалась", 'danger');
			}
		});
	});
	$('table#sync').on('click', 'button#sync2', function() {
		var skip = $('#sync1').val();
		var btn = $(this);
		btn.text('Загрузка, пропуск: ' + skip);
		btn.addClass('disabled');
		sync_load(skip);
	});
});