<?php
function getArgs($args)
{
	//function getArgs($args) by: B Crawford @php.net
    $out      = array();
    $last_arg = null;
    for ($i = 1, $il = sizeof($args); $i < $il; $i++) {
        if ((bool) preg_match("/^--(.+)/", $args[$i], $match)) {
            $parts = explode("=", $match[1]);
            $key   = preg_replace("/[^a-z0-9]+/", "", $parts[0]);
            if (isset($parts[1])) {
                $out[$key] = $parts[1];
            } else {
                $out[$key] = true;
            }
            $last_arg = $key;
        } else if ((bool) preg_match("/^-([a-zA-Z0-9]+)/", $args[$i], $match)) {
            for ($j = 0, $jl = strlen($match[1]); $j < $jl; $j++) {
                $key       = $match[1]{$j};
                $out[$key] = true;
            }
            $last_arg = $key;
        } else if ($last_arg !== null) {
            $out[$last_arg] = $args[$i];
        }
    }
    return $out;
}

$urls  = array('http://almamater.xkcd.com/?edu=umbc.edu');

$data = array(
    'hashable' => 'val'
);

$options = array(
    'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    )
);

$start   = "" . rand(100,99999);
$args    = getArgs($_SERVER['argv']);

if (isset($args['n'])) {
    $start = $args['n'];
}

$iter          = gmp_init($start, 10);
$lowestWord    = 'x';
$lowestBitDiff = 430;
$step          = gmp_init('100000', 10);
$precalmod     = gmp_add(gmp_mul($step, gmp_init('2000', 10)), $iter);
$target        = gmp_init('5b4da95f5fa08280fc9879df44f418c8f9f12ba424b7757de02bbdfbae0d4c4fdf9317c80cc5fe04c6429073466cf29706b8c25999ddd2f6540d4475cc977b87f4757be023f19b8f4035d7722886b78869826de916a79cf9c94cc79cd4347d24b567aa3e2390a573a373a48a5e676640c79cc70197e1c5e7f902fb53ca1858b6', 16);

while (true) {
    $tempWord = gmp_strval($iter, 62);
	
    if (gmp_cmp(gmp_mod($iter, $precalmod), "0") == 0) {
        echo '.....searching.....' . $tempWord . "\n";
    }
	
    $iter        = gmp_add($iter, $step);
	
    $tempBitDiff = gmp_hamdist(gmp_init(skein_hash_hex($tempWord, 1024), 16), $target); // memory leak.... why!?
	
    if ($tempBitDiff < $lowestBitDiff) {
        $lowestBitDiff = $tempBitDiff;
        $lowestWord    = $tempWord;
		
        echo $lowestBitDiff . ' ' . $lowestWord . "\n";
        
        $data['hashable']           = $lowestWord;
        $options['http']['content'] = http_build_query($data);
        $context                    = stream_context_create($options);
		
		foreach ($urls as &$url){
			$result                 = file_get_contents($url, false, $context);
		}
		
        var_dump($result);
    }
}
?>