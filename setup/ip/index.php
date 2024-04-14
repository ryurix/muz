<?

$q = db_query('SELECT ip,count(*) cnt FROM session GROUP BY ip HAVING cnt>3 ORDER BY cnt DESC');

$rows = [];
while ($i = db_fetch($q)) {
	$rows[$i['ip']] = $i['cnt'];
}
db_close($q);
