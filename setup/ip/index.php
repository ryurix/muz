<?

$q = db_query('SELECT ip,count(*) cnt FROM session WHERE usr=0 GROUP BY ip HAVING cnt>10 ORDER BY cnt DESC');

$rows = [];
while ($i = db_fetch($q)) {
	$rows[$i['ip']] = $i['cnt'];
}
db_close($q);
