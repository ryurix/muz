<?

namespace Order;

class Model {

	protected $row;
	protected $orig;

	function default() {
		return [
			'i'=>0,
			'dt'=>null,
			'last'=>null,
			'user'=>null,
			'staff'=>null,
			'state'=>null,
			'cire'=>null,
			'city'=>null,
			'lat'=>null,
			'lon'=>null,
			'adres'=>null,
			'dost'=>null,
			'vendor'=>0,
			'store'=>null,
			'name'=>null,
			'price'=>null,
			'count'=>null,
			'money0'=>0,
			'pay'=>0,
			'money'=>0,
			'pay2'=>0,
			'money2'=>0,
			'bill'=>null,
			'sale'=>null,
			'info'=>null,
			'note'=>null,
			'docs'=>null,
			'files'=>null,
			'mark'=>null, // Метки через запятую
			'kkm'=>0, // Фискализация предоплаты
			'kkm2'=>0, // Фискализация оплаты
			'mpi'=>null, // Код заказа marketplace
			'mpdt'=>null, // День доставки marketplace
			'sku'=>null,
			'complex'=>0,
		];
	}

	function __construct($id = null)
	{

		if (is_array($id)) {
			$row = array_intersect_key($id, $this->default());
		} else {
			$row = $id ? \Db::fetchRow('SELECT * FROM orst WHERE i='.$id) : null;
		}

		if (is_null($row)) {
			$row = $this->default();
		}

		$this->row = $row;
		$this->orig = $row;
	}

	function getData() { return $this->row; }

	function getId() { return $this->row['i']; }
	function getDt() { return $this->row['dt']; }
	function getLast() { return $this->row['last']; }
	function getUser() { return $this->row['user']; }

	function getStaff() { return $this->row['staff']; }
	function setStaff($staff) { $this->row['staff'] = $staff; return $this; }

	function getState() { return $this->row['state']; }
	function setState($state) { $this->row['state'] = $state; return $this; }
	//function setOrigState($state) { $this->orig['state'] = $state; return $this; }
	function getCire() { return $this->row['cire']; }
	function setCire($cire) { $this->row['cire'] = $cire; return $this; }
	function getCity() { return $this->row['city']; }
	function setCity($city) { $this->row['city'] = $city; return $this; }
	function getLat() { return $this->row['lat']; }
	function setLat($lat) { $this->row['lat'] = $lat; return $this; }
	function getLon() { return $this->row['lon']; }
	function setLon($lon) { $this->row['lon'] = $lon; return $this; }
	function getAdres() { return $this->row['adres']; }
	function setAdres($adres) { $this->row['adres'] = $adres; return $this; }
	function getDost() { return $this->row['dost']; }
	function setDost($dost) { $this->row['dost'] = $dost; return $this; }
	function getVendor() { return $this->row['vendor']; }
	function setVendor($vendor) { $this->row['vendor'] = $vendor; return $this; }
	function getStore() { return $this->row['store']; }
	function setStore($store) { $this->row['store'] = $store; return $this; }
	function getName() { return $this->row['name']; }
	function getPrice() { return $this->row['price']; }
	function setPrice($price) { $this->row['price'] = $price; return $this; }
	function getCount() { return $this->row['count']; }
	function setCount($count) { $this->row['count'] = $count; return $this; }
	function getMoney0() { return $this->row['money0']; }
	function setMoney0($money) { $this->row['money0'] = $money; return $this; }
	function getPay() { return $this->row['pay']; }
	function setPay($pay) { $this->row['pay'] = $pay; return $this; }
	function getMoney() { return $this->row['money']; }
	function setMoney($money) { $this->row['money'] = $money; return $this; }
	function getPay2() { return $this->row['pay2']; }
	function setPay2($pay) { $this->row['pay2'] = $pay; return $this; }
	function getMoney2() { return $this->row['money2']; }
	function setMoney2($money) { $this->row['money2'] = $money; return $this; }
	function getBill() { return $this->row['bill']; }
	function setBill($bill) { $this->row['bill'] = $bill; return $this; }
	function getSale() { return $this->row['sale']; }
	function setSale($sale) { $this->row['sale'] = $sale; return $this; }
	function getInfo() { return $this->row['info']; }
	function setInfo($info) { $this->row['info'] = $info; return $this; }
	function getNote() { return $this->row['note']; }
	function setNote($note) { $this->row['note'] = $note; return $this; }
	function getDocs() { return $this->row['docs']; }
	function setDocs($docs) { $this->row['docs'] = $docs; return $this; }
	function getFiles() { return $this->row['files']; }
	function setFiles($files) { $this->row['files'] = $files; return $this; }
	function getMark() { return $this->row['mark']; }
	function setMark($mark) { $this->row['mark'] = $mark; return $this; }

	function getKkm() { return $this->row['kkm']; }
	function setKkm($kkm) { $this->row['kkm'] = $kkm; return $this; }
	function getKkm2() { return $this->row['kkm2']; }
	function setKkm2($kkm) { $this->row['kkm2'] = $kkm; return $this; }
	function getMpi() { return $this->row['mpi']; }
	function setMpi($mpi) { $this->row['mpi'] = $mpi; return $this; }
	function getMpdt() { return $this->row['mpdt']; }
	function setMpdt($mpdt) { $this->row['mpdt'] = $mpdt; return $this; }
	function getSku() { return $this->row['sku']; }
	function setSku($sku) { $this->row['sku'] = $sku; return $this; }
	function getComplex() { return $this->row['complex']; }

	function save() {
		if (!$this->row['i']) {
			return $this->create();
		} else {
			return $this->update();
		}
	}

	function create() {
		$data = $this->row + $this->default();
		unset($data['i']);

		if (!$data['dt']) { $data['dt'] = now(); }
		if (!$data['last']) { $data['last'] = now(); }
		if (!isset($data['state'])) {
			$data['state'] = 1;
		}

		$ids = [];

		$complex = \Db::fetchAll('SELECT c.*, s.price, s.brand, s.model, s.name FROM complex c LEFT JOIN store s ON c.store=s.i WHERE c.up='.$data['store']);

		if (count($complex)) {
			$first = null;
			$data['complex'] = $data['store'];

			foreach ($complex as $i) {
				$brand = \Flydom\Cache::get('brand');
				$child = $data;
				$child['store'] = $i['store'];
				$child['name'] = (isset($brand[$i['brand']]) ? $brand[$i['brand']].' ' : '').(strlen($i['model']) ? $i['model'].' ' : '').$i['name'];
				$child['price'] = round($i['price'] * (100 + $i['sale']) / 100);
				$child['count'] = $i['amount'];
				$child['note'] = trim($data['note'].' составной: '.$data['name']);
				//$child['info'] = trim($data['info'].' составной: '.$data['name']);

				$id = \Db::insert('orst', $child);
				$ids[] = $id;

				\Tool\Reserve::create($id, $child['store'], $child['count']);

				if (is_null($first)) {
					$first = $child;
					$first['i'] = $id;
				}
			}

			$data = $first;

		} else {
			$data['i'] = \Db::insert('orst', $data);
			$ids[] = $data['i'];

			\Tool\Reserve::create($data['i'], $this->getStore(), $this->getCount());
		}

		$this->row = $data;
		$this->orig = $data;

		return $ids;
	}

	function update() {

		if ($this->row['state'] < 30 || $this->orig['state'] < 30) {
			$this->row['last'] = now();
		}

		$data = $this->row;
		unset($data['i']);
		\Db::update('orst', $data, ['i'=>$this->getId()]);

		$new = $this->row['state'];
		$old = $this->orig['state'];

		if ($old <= 1 && $new > 1 && $new <= 30) {
			$this->process();
		} else if ($old > 1 && $old < 35 && $new <= 1) {
			$this->revert();
			\Tool\Reserve::create($this->getId(), $this->getStore(), $this->getCount());
		} else if ($old > 1 && $old != 27 && $old != 35 && $old != 30 && $new == 35) {
			$this->cancel();
		}

		if ($old <= 1 && $new > 1) {
			\Tool\Reserve::delete($this->getId(), $this->getStore());
		}

		if ($old <= 1 && $old < $new && $new < 35) { // обработка заказа маркетплейсами
			\Cron\Ozon::pack($this);
		}

		if ($old != $new) { \Flydom\Log::add(100 + $new, $this->getId()); }

		$this->orig = $this->row;
	}

	function delete() {
		if ($this->getState() == 1) {
			\Tool\Reserve::delete($this->getId(), $this->getStore());
		}
		if ($this->getState() > 1 && $this->getState() < 30) {
			$this->cancel();
		}
		\Db::delete('orst', ['i'=>$this->getId()]);
		$this->row['i'] = 0;
	}

	// Принятие заказа в обработку
	protected function process() {
		$cnt = db_result('SELECT count FROM sync WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		if ($cnt == false) { $cnt = 0; }
		$text = $this->getVendorName().' '.$cnt.' - '.$this->row['count'].' = '.($cnt - $this->row['count']);

		db_query('UPDATE sync SET count=count-'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		db_query('UPDATE store SET count=count-'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND i='.$this->row['store']);
		alert('Количество '.$this->row['name'].' на складе '.$text);

		w('log');
		logs(36, $this->row['i'], $text);

		\Tool\Reserve::delete($this->getId(), $this->getStore());
	}

	// Возвращение заказа в начальный статус
	protected function revert() {
		$cnt = db_result('SELECT count FROM sync WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		if ($cnt == false) { $cnt = 0; }
		$text = $this->getVendorName().' '.$cnt.' + '.$this->row['count'].' = '.($cnt + $this->row['count']);

		db_query('UPDATE sync SET count=count+'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		db_query('UPDATE store SET count=count+'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND i='.$this->row['store']);
		alert('Количество '.$this->row['name'].' на складе '.$text);

		w('log');
		logs(37, $this->row['i'], $text);

		\Tool\Reserve::delete($this->getId(), $this->getStore());
	}

	// Отмена заказа
	protected function cancel() {
		$cnt = db_result('SELECT count FROM sync WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		$text = $this->getVendorName().' '.$cnt.' + '.$this->row['count'].' = '.($cnt + $this->row['count']);

		db_query('UPDATE sync SET count=count+'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND store='.$this->row['store']);
		db_query('UPDATE store SET count=count+'.$this->row['count']
			.' WHERE vendor='.$this->row['vendor'].' AND i='.$this->row['store']);
		\Flydom\Alert::info('Количество '.$this->row['name'].' на складе '.$text);

//		w('log');
//		logs(38, $this->getId(), $text);
		\Flydom\Log::add(38, $this->getId(), $text);

		$bills = \Db::fetchAll('SELECT * FROM bill WHERE orst LIKE "%'.$this->getId().'%" AND state<=1');
		foreach ($bills as $i) {
			$bill_orders = explode('|', $i['orst']);
			if (in_array($this->getId(), $bill_orders)) {
				\Db::update('bill', array('state'=>5), array('i'=>$i['i']));
			}
		}

		\Tool\Reserve::delete($this->getId(), $this->getStore());
	}

	function getVendorName() {
		$vendors = \Flydom\Cache::get('vendor');
		return kv($vendors, $this->row['vendor']);
	}

	function getUserName() {
		return $this->row['user'] ? \Db::result('SELECT name FROM user WHERE i='.$this->row['user']) : '';
	}
}