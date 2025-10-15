<?

function email2($mail, $from, $name, $theme, $rows, $copy = 1) {

$body = '<body>

<table width="600" align="center" bgcolor="#ffffff" cellpadding="15" cellspacing="0" style="border-collapse:collapse; border:1px solid #D1D8D9">
<tr>
<td>

<a href="https://muzmart.com"><img src="https://muzmart.com/design/images/muzmart-sm.png" alt="Музмарт" title="Музмарт"></a>

</td>
<td align="right">

<span style="font-size:12px">Звоните бесплатно</span>
<br>
<span style="font-size:24px">8 800 200 26 78</span>

</td>
</tr>
<tr><td bgcolor="#515254" >

<span style="color:#BFBFBF">Звуковое, световое и видео оборудование</span>

</td>
<td bgcolor="#515254">


</td></tr>
<tr><td bgcolor="#F2F2F2" colspan=2>

<h2>Здравствуйте, <span style="color:#C00000">'.$name.'<span></h2>
<h1 style="color:#C00000">'.$theme.'</h1>

</td></tr>
<tr><td colspan=2>

'.(is_array($rows) ? implode("\n</td></tr><tr><td colspan=2>\n", $rows) : $rows).'

</td></tr>
<tr><td colspan=2>

Контакты для связи: тел: 8(3452) 589-564; 8 800 200 26 78; info@muzmart.com<br>
<span style="color:#838383;font-style:italic">С наилучшими пожеланиями, команда Muzmart</span

</td></tr>
<tr><td colspan=2 bgcolor="#515254">
<span style="color:#FFFFFF">Более 30 000 товаров в наличии!</span>
</td></tr>
<tr><td>
	Мы в социальных сетях<br><br>
	<a href="http://vk.com/muzmart"><img src="https://muzmart.com/design/vk-33.jpg" alt="ВКонтакте" title="ВКонтакте"></a>
</td><td align="right">

<span style="font-size:12px">Звоните бесплатно</span>
<br>
<span style="font-size:24px">8 800 200 26 78</span>

</td></tr>
</table>

</body>';

if ($from == 'news@muzmart.com') {
	w('email-yandex');
	email_news(
		$mail,
		$name,
		$theme,
		$body,
		$files = array()
	);
} else {
//	global $config;
	w('email');
	email(
//'=?UTF-8?B?'.base64_encode($name).'?='.' <'.$mail.'>',
//'=?UTF-8?B?'.base64_encode($config['title']).'?='.' <'.$from.'>',
		$mail,
		$name,
		$theme,
		$body,
		$files = array(),
		$copy
	);
}

}

?>