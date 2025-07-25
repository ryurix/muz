<?

namespace Type;

class Log extends \Flydom\Type\Log
{

const DATA = parent::DATA + [

	10=>['name'=>'Редактирование пользователя', 'days'=>365],
	13=>['name'=>'Редактирование товара', 'days'=>1000],
	14=>['name'=>'Создание товара', 'days'=>365],
	15=>['name'=>'Импорт товара', 'days'=>365],
	16=>['name'=>'Импорт прайса', 'days'=>365],
	17=>['name'=>'Синхронизация товара', 'days'=>365],
	18=>['name'=>'Синхронизация изменение', 'days'=>365],

	21=>['name'=>'Товар добавлен в корзину', 'days'=>31],
	22=>['name'=>'Новый заказ на существующий логин', 'days'=>365],
	25=>['name'=>'Заказ оформлен', 'days'=>365],
	27=>['name'=>'Изменение статуса заказа', 'days'=>365],
	36=>['name'=>'Списание товара со склада', 'days'=>365],
	37=>['name'=>'Возврат на склад при откате статуса', 'days'=>365],
	38=>['name'=>'Возврат на склад при отмене заказа', 'days'=>365],
	39=>['name'=>'Удаление заказа', 'days'=>365],
	40=>['name'=>'Рассылка', 'days'=>365],
	41=>['name'=>'Письмо о регистрации', 'days'=>365],
	42=>['name'=>'Письмо о восстановлении пароля', 'days'=>365],
	43=>['name'=>'Письмо о подтверждении заказа', 'days'=>365],
	45=>['name'=>'Письмо об изменении статуса заказа', 'days'=>365],
	49=>['name'=>'Ошибка отправки письма', 'days'=>365],
	95=>['name'=>'Ошибка фискализации', 'days'=>365],

	100=>['name'=>'Черновик', 'days'=>365],
	101=>['name'=>'В обработке', 'days'=>365],
	102=>['name'=>'Обработан', 'days'=>365],
	103=>['name'=>'Ожидание оплаты', 'days'=>365],
	107=>['name'=>'Подтвержден', 'days'=>365],
	110=>['name'=>'Счет запрошен', 'days'=>365],
	113=>['name'=>'Оплачено поставщику', 'days'=>365],
	115=>['name'=>'Отправлено поставщиком', 'days'=>365],
	120=>['name'=>'Готов к выдаче', 'days'=>365],
	123=>['name'=>'Отправлено клиенту', 'days'=>365],
	125=>['name'=>'Доставка на дом', 'days'=>365],
	127=>['name'=>'Собран', 'days'=>365],
	130=>['name'=>'Закрыт', 'days'=>365],
	135=>['name'=>'Отменен', 'days'=>365],

	201=>['name'=>'Яндекс /cart', 'days'=>31],
	205=>['name'=>'Яндекс /order/accept', 'days'=>31],
	206=>['name'=>'Яндекс заказ товара нет в наличии', 'days'=>31],
	209=>['name'=>'Яндекс /order/status', 'days'=>31],
	211=>['name'=>'Яндекс /stocks', 'days'=>7],
	213=>['name'=>'Яндекс /offers/stocks ошибка получения остатков', 'days'=>7],
	220=>['name'=>'Яндекс уведомление', 'days'=>7],
	235=>['name'=>'Яндекс заказ отменён', 'days'=>31],
	250=>['name'=>'Яндекс изменение статуса', 'days'=>31],
	255=>['name'=>'Яндекс ошибка изменения статуса', 'days'=>31],
	295=>['name'=>'Яндекс ошибка авторизации', 'days'=>31],
	355=>['name'=>'Ozon API ошибка получения списка', 'days'=>31],
	375=>['name'=>'Ozon API ошибка сбора заказа', 'days'=>31],
	385=>['name'=>'Ozon API ошибка отмены заказа', 'days'=>31],
	395=>['name'=>'Ozon API выгрузка ошибка', 'days'=>31],
	399=>['name'=>'Ozon API выгрузка', 'days'=>7],
	401=>['name'=>'Wildberries обновление количества', 'days'=>7],
	403=>['name'=>'Wildberries обновление цен', 'days'=>7],
	420=>['name'=>'Wildberries список заказов', 'days'=>7],
	425=>['name'=>'Wildberries отмена заказов', 'days'=>31],
	501=>['name'=>'Goods API /new', 'days'=>31],
	508=>['name'=>'Goods API /cancel', 'days'=>31],
];

} // Log