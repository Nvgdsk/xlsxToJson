<?php

require_once 'vendor/autoload.php';

global $currency;

function removeComma($val)
{  //числа  формата 250,000.00 в xlsx нужно преобразить
    return str_replace(",", "", $val);
}

function getRightFloat($val)
{
    return floatval(removeComma($val));
}

function getCurrencyUSD()
{
    $link = 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5';
    $answer = json_decode(file_get_contents($link), 1);
    foreach ($answer as $row) {
        if ($row['ccy'] == "USD") {
            return $row['sale'];
        }
    }
}

$currency = getCurrencyUSD();

if ($xlsx = SimpleXLSX::parse('exel/ope.xlsx')) {

    $arrayObject = [];
    $trigger = true;

    foreach ($xlsx->rows() as $key => $val) {

        if ($trigger) {
            $trigger = false;
            continue;
        }
        $arrayObject[] = array(
            'id' => intval($key),
            'title' => $val[0],
            'type' => $val[2],
            'fields' =>
                array(
                    'house' => intval($val[3]),
                    'section' => intval($val[4]),
                    'floor' => intval($val[5]),
                    'rooms' => intval($val[1]),
                    'square' => floatval($val[6]),
                    'pricePerSquare' =>
                        array(
                            'usd' => getRightFloat($val[8]),
                            'hrn' => getRightFloat($val[8]) * $currency
                        ) //если нужно, можно использовать round

                ),
            'priceTotal' =>
                array(
                    'usd' => getRightFloat($val[9]),
                    'hrn' => getRightFloat($val[9]) * $currency
                ),


        );

    }
    
    $fp = fopen('json/results.json', 'w');
    fwrite($fp, json_encode($arrayObject, 1));
    fclose($fp);

} else {
    echo SimpleXLSX::parseError();
}