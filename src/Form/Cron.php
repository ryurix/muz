<?php

namespace Form;

class Cron
{
	const EVERY = [
		0=>'Не запускать автоматически',
		1=>'Ежедневно по расписанию',
		600=>'10 минут',
		900=>'15 минут',
		1200=>'20 минут',
		1500=>'25 минут',
		1800=>'30 минут',
		2100=>'35 минут',
		3600=>'1 час',
		28800=>'8 часов',
		64800=>'18 часов',
		86400=>'1 день',
		259200=>'3 дня'
	];
}