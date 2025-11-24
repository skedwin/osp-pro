<?php
$s = file_get_contents(__DIR__ . '/../app/Http/Controllers/PractitionerController.php');
$open = substr_count($s, '{');
$close = substr_count($s, '}');
echo "open=$open close=$close\n";
$lines = explode("\n", $s);
for ($i=0;$i<count($lines);$i++) {
    echo str_pad($i+1,4,' ',STR_PAD_LEFT)." ". $lines[$i] . "\n";
}
