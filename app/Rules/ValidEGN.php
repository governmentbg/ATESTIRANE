<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEGN implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $egn = $value;
        $EGN_WEIGHTS = array(2,4,8,5,10,9,7,3,6);
        if (strlen($egn) != 10){
            $fail('Полето ЕГН не съдържа валидно ЕГН.');
        }
        $year = intval(substr($egn,0,2));
        $mon  = intval(substr($egn,2,2));
        $day  = intval(substr($egn,4,2));
        if ($mon > 40) {
            if (!checkdate($mon-40, $day, $year+2000)){
                $fail('Полето ЕГН не съдържа валидно ЕГН.');
            }
        } else
        if ($mon > 20) {
            if (!checkdate($mon-20, $day, $year+1800)){
                $fail('Полето ЕГН не съдържа валидно ЕГН.');
            }
        } else {
            if (!checkdate($mon, $day, $year+1900)){
                $fail('Полето ЕГН не съдържа валидно ЕГН.');
            }
        }
        $checksum = intval(substr($egn,9,1));
        $egnsum = 0;
        for ($i=0;$i<9;$i++)
            $egnsum += intval(substr($egn,$i,1)) * $EGN_WEIGHTS[$i];
        $valid_checksum = $egnsum % 11;
        if ($valid_checksum == 10)
            $valid_checksum = 0;
        if ($checksum != $valid_checksum){
            $fail('Полето ЕГН не съдържа валидно ЕГН.');
        }
    }
}
