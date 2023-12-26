<?php
function matrix_addition(array $a, array $b): array {
    for($i=0;$i<count($a);$i++){
        for($j=0;$j<count($a[0]);$j++){
            $a[$i][$j]+=$b[$i][$j];
        }
    }

    return $a;
}