<?php
/**
 * Formats phone number
 *
 * @string $number The number to format
 * @return string A formatted phone number
 */
function clir_format_phone($number)
{
    $string = "$number is not a valid number.";

    $number = preg_replace("/[^0-9]/", "", $number);

    if(strlen($number) == 10) {
      $string = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1.$2.$3", $number);
    }

    return $string;
}
