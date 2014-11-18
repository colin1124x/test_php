<?php


$base32table = [
    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
    'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
    'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
    'Y', 'Z', '2', '3', '4', '5', '6', '7',
    '='
];

$base32charsFlipped = array_flip($base32table);

//foreach ($base32charsFlipped as $char => $n) {
//    echo $char, ' => ', $b = base_convert($n, 10, 2), ' => ', str_pad($b, 5, '0', 0),'<br>';
//}
//die;

$secret = str_split('NIWILYKKFULNVUQ3');

$binary_string = '';
for ($i = 0; $i < count($secret); $i+=8) {
//    var_dump($i);
    $x = '';
    for ($j = 0; $j < 8; ++$j) {
        $bytes = base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2);
        $x .= str_pad($bytes, 5, '0', STR_PAD_LEFT);
//        var_dump($bytes, $x);
    }

    $eightBits = str_split($x, 8);
    var_dump($eightBits);
    for ($z = 0; $z < count($eightBits); $z++) {
        $binary_string .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
    }
}

$timeSlice = floor(time()/30);
var_dump($binary_string, chr(48));
$time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
var_dump($timeSlice, pack('N*', $timeSlice));

// Hash it with users secret key
$hm = hash_hmac('SHA1', $time, $binary_string, true);
var_dump($hm);

// Use last nipple of result as index/offset
var_dump(substr($hm, -1));
$offset = ord(substr($hm, -1)) & 0x0F;
var_dump($offset);

// grab 4 bytes of the result
$hashpart = substr($hm, $offset, 4);
var_dump($hashpart);

$value = unpack('N', $hashpart);
var_dump($value);

$value = $value[1];
// Only 32 bits
$value = $value & 0x7FFFFFFF;
var_dump($value);

$code_length = 6;
$modulo = pow(10, $code_length);
var_dump($modulo);

var_dump(str_pad($value % $modulo, $code_length, '0', STR_PAD_LEFT));