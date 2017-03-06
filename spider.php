<?php
include "simple_html_dom.php" ;
function DateAdd($part, $number, $date)
{
$date_array = getdate(strtotime($date));
$hor = $date_array["hours"];
$min = $date_array["minutes"];
$sec = $date_array["seconds"];
$mon = $date_array["mon"];
$day = $date_array["mday"];
$yar = $date_array["year"];
switch($part)
{
case "y": $yar += $number; break;
case "q": $mon += ($number * 3); break;
case "m": $mon += $number; break;
case "w": $day += ($number * 7); break;
case "d": $day += $number; break;
case "h": $hor += $number; break;
case "n": $min += $number; break;
case "s": $sec += $number; break;
}
return date("Y/m/d", mktime($hor, $min, $sec, $mon, $day, $yar));
}
function send_post($url, $post_data)
{
	$postdata = http_build_query($post_data);
	$options = array(
		'http' => array(
			'method'=> 'POST',
			'header'=>'Content-type: application/x-www-form-urlencoded',
			'content'=> $postdata,
			'timeout'=> 15 * 60
		)	
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	return $result;
}
function handle($data,$date)
{
	$children = $data->children;
	for ($i=1; $i < count($children); $i++)
	{
		$row = $children[$i]->children;
		$startIndex=0;
		//echo count($row)."\n";
		if (count($row)==9)
	    {
			$startIndex=1;
		}
		$name=$row[$startIndex]->first_child()->innertext;
		$water=$row[$startIndex+1]->first_child()->innertext;
		$number=$row[$startIndex+4]->first_child()->innertext;
		//echo $name." ".$water." ".$number."\n";
        file_put_contents('data/'.$name.".txt", $date." ".$water." ".$number."\n", FILE_APPEND);
	}
}

$post_data = array(
	'date1'=>'2016/1/1',
	'h1'=> '8:00:00',
	'sj1'=>'2016/1/1 8:00:00',
	'sj2'=>'2015/12/31 8:00:00'
);
date_default_timezone_set('UTC');
//$today = date(‘y-m-d’,time());
//echo $today;
//echo DateAdd('d',2,$post_data['date1']);
while($post_data['date1'] != '2017/1/1' )
{
	$htmlData = send_post('http://219.140.162.169:8800/rw4/report/ma02.asp',$post_data);
	if (empty($htmlData))
	{
		sleep(300);
	    $htmlData = send_post('http://219.140.162.169:8800/rw4/report/ma02.asp',$post_data);
	}
    sleep(10);
    if (!empty($htmlData))
	{
	$htmlData = iconv("gb2312", "UTF-8", $htmlData);
	$html = new simple_html_dom($htmlData);
    $table=$html->find('table[id=table3]');
	//$data=$table->children;
	for ($i = 0;$i < count($table); $i++){
		handle($table[$i],$post_data['date1']);
	}

	//echo ($table->children(1)->children(1)->innertext);
	$html->clear();
	}
    echo $post_data['sj1'];
	$post_data['date1']=DateAdd('d',1, $post_data['date1']);
	$post_data['sj2'] = $post_data['sj1'];
	$post_data['sj1']=$post_data['date1']." ".'8:00:00';
}
//$res=send_post('http://219.140.162.169:8800/rw4/report/ma02.asp',$post_data);
//echo $res;

?>
