<?php
/*
Plugin Name: Cbr Currency
Plugin URI: http://www.f4ber.net/
Description: Виджет курса валют ЦБ РФ на текущий день c динамикой.
Version: 2.3.5
Author: f4ber
Author URI: http://www.f4ber.net/
License: GPL2
*/

# based on File: usd2.php, v.1.0/20010803 and finmarket.ru resource
# Скрипт для вывода информера по поводу курса доллара, установленного ЦБР
# (c) 2001, Mikhail Turenko, http://www.turenko.net, <mikhail@turenko.net>

function CbrCurrencyWidget($args){
extract($args);
$title = 'Курс ЦБ ';

function getcurdate(){

# URL для запроса данных
$requrldate = "http://www.cbr.ru/scripts/XML_daily.asp";

$docdate = file($requrldate);
$docdate = implode($docdate, '');

# инициализируем массив
$dt = array();

# ищем <Date="xx.xx.xxxx">
if(preg_match("/Date=\"(\d{2})\.(\d{2})\.(\d{4})\"/is", $docdate, $dt))
	$curdate = "{$dt[1]}/{$dt[2]}/{$dt[3]}";

 return array ($curdate);
}

function getcurrency($code){

# Базовый URL скрипта на cbr.ru
$scripturl = 'http://www.cbr.ru/scripts/XML_dynamic.asp';

# Начальная дата для запроса  (сегодня - 2 дня 259200)
$date_1=date('d/m/Y', time()-259200);

# Конечная дата (чтобы учитывать завтра добавьте параметр time()+172800)
$date_2=date('d/m/Y', time()+86400);

# Таким образом, мы получим данные либо за 2, либо за 3 последних дня.
# За 2 - если на "сегодня" курс еще не выставили, иначе - за 3

# Код валюты в архиве данных cbr.ru
//$currency_code='R01235';
$currency_code=$code;

# URL для запроса данных
$requrl = "{$scripturl}?date_req1={$date_1}&date_req2={$date_2}&VAL_NM_RQ={$currency_code}";

$doc = file($requrl);
$doc = implode($doc, '');

# инициализируем массив
$r = array();

# ищем <ValCurs>...</ValCurs>
if(preg_match("/<ValCurs.*?>(.*?)<\/ValCurs>/is", $doc, $m))
	# а потом ищем все вхождения <Record>...</Record>
	preg_match_all("/<Record(.*?)>(.*?)<\/Record>/is", $m[1], $r, PREG_SET_ORDER);

$m = array();	# его уже использовали, реинициализируем
$d = array();	# этот тоже проинициализируем

# Сканируем на предмет самых нужных цифр
for($i=0; $i<count($r); $i++) {
	if(preg_match("/Date=\"(\d{2})\.(\d{2})\.(\d{4})\"/is", $r[$i][1],$m)) {
		$dv = "{$m[1]}/{$m[2]}/{$m[3]}"; # Приводим дату в норм. вид
		if(preg_match("/<Nominal>(.*?)<\/Nominal>.*?<Value>(.*?)<\/Value>/is", $r[$i][2], $m)) {
			$m[2] = preg_replace("/,/",".",$m[2]);
			$d[] = array($dv, $m[1], $m[2]);
			}
		}
	}

$last = array_pop($d);					# последний известный день
$prev = array_pop($d);					# предпосл. известный день
$date = $last[0];						# отображаемая дата
$rate = sprintf("%.2f",$last[2]);		# отображаемый курс
# отображаемое изменение курса, например, "+0.02"
$delta = (($last[2]>$prev[2])?"+":"").sprintf("%.2f",$last[2]-$prev[2]);
$znak = "up";
if($last[2]<$prev[2]) { $znak = "dn"; }
$zcolor = "green";
if($last[2]<$prev[2]) { $zcolor = "red"; }

//echo("{$date}: 1USD={$rate}RUR ({$delta})<BR>");
 return array ($rate, $delta, $znak, $zcolor, $date);
}

list ($rate_d, $delta_d, $znak_d, $zcolor_d, $date_d) = getcurrency('R01235');
list ($rate_e, $delta_e, $znak_e, $zcolor_e, $date_e) = getcurrency('R01239');
list ($date) = getcurdate();

echo $before_widget . $before_title . $title . $date . $after_title;

echo '
<style type="text/css">

#currency {margin: 10px auto;}
#currency td {border-right: none;padding:0;background:transparent;vertical-align:middle;}
#lf {margin: 0 0 0;}

td.currate {
    border-bottom: 1px solid #B6C3D3;
    font-size: 16px;
    text-align: center;
}
</style>
<table id="currency" cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
<td>
<table id="lf" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td rowspan="2">
<a href="http://finmarket.ru/z/vlk/valdetails.asp?val=840">
<img width="25" height="30" border="0" alt="USD" src="' . WP_PLUGIN_URL . '/cbrcurrency/img/dollar.png">
</a>
</td>
<td width="8" rowspan="2">&nbsp;</td>
<td class="currate">'.$rate_d.'</td>
</tr>
<tr>
<td>
<img width="9" height="9" src="' . WP_PLUGIN_URL . '/cbrcurrency/img/'.$znak_d.'.gif">
<span style="font-size:12px;color:'.$zcolor_d.'">'.$delta_d.'</span>
</td>
</tr>
</tbody>
</table>
</td>
<td style="padding-left:10px;">
<table id="lf" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td rowspan="2">
<a href="http://finmarket.ru/z/vlk/valdetails.asp?val=978">
<img width="25" height="32" border="0" alt="EUR" src="' . WP_PLUGIN_URL . '/cbrcurrency/img/euro.png">
</a>
</td>
<td width="10" rowspan="2">&nbsp;</td>
<td class="currate">'.$rate_e.'</td>
</tr>
<tr>
<td>
<img width="9" height="9" src="' . WP_PLUGIN_URL . '/cbrcurrency/img/'.$znak_e.'.gif">
<span style="font-size:12px;color:'.$zcolor_e.'">'.$delta_e.'</span>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
';
}
register_sidebar_widget('Cbr Currency', 'CbrCurrencyWidget');
?>