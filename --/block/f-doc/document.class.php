<?

// Copyright © 2015 Elena Bakun Contacts: <floppox@gmail.com>
// License: http://opensource.org/licenses/MIT

class Document{

	protected $processor;

	public function process() {
		$class = $this->file_type.'_processor';
		w($class);
		$this->processor = new $class($this);
	}

	public function __call($name, $arguments) {
		if (count($arguments) == 1 ) $arguments = $arguments[0];
		return $this->processor->$name($arguments);
	}

	function __construct($template, $filename) {
		$this->template = $template;
		$this->filename = $filename;

		copy($template, $filename);
		$this->open_archive();
		$this->file_type = $this->check_document_type();
	}

	private $template;
	public $filename;

	public $opened;
	private $file_type;

	private function open_archive() {
		$this->opened = new ZipArchive;
		$this->opened->open($this->filename);
	}

	private function check_document_type() {
		if ($this->opened->locateName('xl/sharedStrings.xml')!==false) return 'xlsx';
		if ($this->opened->locateName('word/document.xml')!==false) return 'docx';
		else return false;
	}

	public function close() {
		$this->opened->close();
	}
/*
	function file_download() {
		$file = '../downloads/'.$this->target_name;
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($this->template_name));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			unlink($file);
		}
	}
*/
}
?>
