<?

include_once __DIR__.'/phpqrcode/qrlib.php';

$qrfile = $config['root'].'files/qrstore.png';

$info = QRcode::png('https://muzmart.com/store/'.$config['row']['i'], $qrfile, QR_ECLEVEL_L, 4, 0);

$brand = cache_load('brand');
$name = $config['row']['name'];
$model = trim($brand[$config['row']['brand']].' '.$config['row']['model']);

// * * *


w('fpdf');

class PDF extends tFPDF {
	function HexColor($color) {
		if (strlen($color) < 6) {
			return array(
				hexdec(substr($color, 0, 1).substr($color, 0, 1)),
				hexdec(substr($color, 1, 1).substr($color, 1, 1)),
				hexdec(substr($color, 2, 2).substr($color, 2, 2)),
			);
		}
		return array(
			hexdec(substr($color, 0, 2)),
			hexdec(substr($color, 2, 2)),
			hexdec(substr($color, 4, 2)),
		);
	}

	function SetColor($color) {
		list($red, $green, $blue) = $this->HexColor($color);
		$this->SetTextColor($red, $green, $blue);
	}

	function SetBgColor($color) {
		list($red, $green, $blue) = $this->HexColor($color);
		$this->SetFillColor($red, $green, $blue);
	}
}

$left = 2;
$border = 0.25;
$textwidth = 21.2;
$fontsize = 12;

$info = getimagesize($qrfile);
$width = $info[0]*25.4/72 + $border*2 + $textwidth;
$height = $info[1]*25.4/72 + $border - 8.5;

$p = new PDF('L', 'mm', array($width, $height));
//$p->AliasNbPages();
$p->AddPage();
$p->SetAutoPageBreak(false);
$p->AddFont('Segoe', '', 'Segoe-Regular.ttf', true);
$p->AddFont('Segoe', 'B', 'Segoe-Bold.ttf', true);

//$x = $p->GetX();
//$y = $p->GetY();
$x = 0;
$y = 0;

$p->SetXY($x+$border+$left, $y+$border);
$p->Image($qrfile);
$y2 = $p->GetY();

$info = getimagesize($qrfile);
$x2 = $x + $info[0]*25.4/72 - 10 + $border*2 + 0.5 + $left;

$p->SetXY($x2, $y + max(0, $border - $fontsize/20)); // $info[0]*25.4/72
//$p->Cell(95, 10, $x.' '.$x2, 1, 1);

$p->SetColor('000');
$p->SetFont('Segoe','', $fontsize);
$p->MultiCell($width-$x2, $fontsize / 2.6, 'М'.$config['row']['i'], 0, 'R');

$p->SetX($x2);
$p->SetFont('Segoe','B', $fontsize);
$p->MultiCell($width-$x2, $fontsize / 2.6, $model, 0, 'L');

$p->SetX($x2);
$p->SetFont('Segoe','', $fontsize * 0.8);
$p->MultiCell($width-$x2, $fontsize / 2.6, $name, 0, 'L');


//$p->SetXY($x, $y);
//$p->Multicell($x2 - $x + $textwidth + $border, $y2-$y+$border, '', 1);

/*
$x = $p->GetX();
$p->SetX(50); // $info[0]*25.4/72
//$p->SetY($y + 10);
$p->Cell(95, 20, 'Проверка', 1, 1);
*/

$p->Output('M'.$config['row']['i'].'.pdf', 'I');
unlink($qrfile);

exit();

?>
