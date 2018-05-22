<?php
    require_once("pdf2text.php");
    $result = pdf2text('BangKiSu_UIT_14520394_2018.pdf');
    $check='';
    for($i = 0; $result[$i] != ';'; $i++) 
        $check .= $result[$i];
    echo $check;
