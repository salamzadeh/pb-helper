<?php

namespace PBHelper;

use Illuminate\Validation\Validator;

class PBHelperValidator extends Validator
{
    public $validationMessages = [
        'jdate'                  => ':attribute'.' تاریخ شمسی معتبر نمی باشد.',
        'jdate_equal'            => ':attribute'.' تاریخ شمسی برابر '.':fa-date'.' نمی باشد.',
        'jdate_not_equal'        => ':attribute'.' تاریخ شمسی نامساوی '.':fa-date'.' نمی باشد.',
        'jdatetime'              => ':attribute'.' تاریخ و زمان شمسی معتبر نمی باشد.',
        'jdatetime_equal'        => ':attribute'.' تاریخ و زمان شمسی مساوی '.':fa-date'.' نمی باشد.',
        'jdatetime_not_equal'    => ':attribute'.' تاریخ و زمان شمسی نامساوی '.':fa-date'.' نمی باشد.',
        'jdate_after'            => ':attribute'.' تاریخ شمسی باید بعد از '.':fa-date'.' باشد.',
        'jdate_after_equal'      => ':attribute'.' تاریخ شمسی باید بعد یا برابر از '.':fa-date'.' باشد.',
        'jdatetime_after'        => ':attribute'.' تاریخ و زمان شمسی باید بعد از '.':fa-date'.' باشد.',
        'jdatetime_after_equal'  => ':attribute'.' تاریخ و زمان شمسی باید بعد یا برابر از '.':fa-date'.' باشد.',
        'jdate_before'           => ':attribute'.' تاریخ شمسی باید قبل از '.':fa-date'.' باشد.',
        'jdate_before_equal'     => ':attribute'.' تاریخ شمسی باید قبل یا برابر از '.':fa-date'.' باشد.',
        'jdatetime_before'       => ':attribute'.' تاریخ و زمان شمسی باید قبل از '.':fa-date'.' باشد.',
        'jdatetime_before_equal' => ':attribute'.' تاریخ و زمان شمسی باید قبل یا برابر از '.':fa-date'.' باشد.',
        'iran_iban'              => ':attribute یک شماره شبای معتبر نیست.',
        'iran_debit_card'        => ':attribute یک شماره کارت بانکی معتبر نیست.',
        'iran_phone'             => ':attribute یک شماره تلفن معتبر نیست.',
        'iran_mobile'            => ':attribute یک شماره همراه معتبر نیست.',
        'iran_national_code'     => ':attribute کد ملی معتبر نیست.',
        'iran_zip_code'          => ':attribute کد پستی معتبر نیست.',
    ];

    public function __construct($translator, $data, $rules, $messages = [], $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        $this->setCustomMessages($this->validationMessages);
    }

    /**
     *
     * Validate IBAN (Sheba) account number
     *
     * @param $attribute
     * @param $account
     * @param $parameters
     * @return bool
     */
    public function validateIranIban($attribute, $account, $parameters,$validator)
    {
        $account_number = $account;

        // The codes of IBAN standard characters
        $character_map = [
            10 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ];

        // Check for account country code and add if there is no exists
        if (!empty($parameters[0]) && $parameters[0] == 'false') {
            if (isset($parameters[1]) && strlen($parameters[1]) == 2) {
                $account_number = $parameters[1] . $account;
            } else {
                $account_number = 'IR' . $account;
            }
        }

        // Validate length of IBAN digits
        $iban_digits = substr($account_number, 2);
        if (!is_numeric($iban_digits) || strlen($iban_digits) != 24) {
            return false;
        }

        // Convert country characters to digit
        $country_chracters = substr($account_number, 0, 2);
        $characters_code = array_map(function($chr) use ($character_map) {
            return array_search(strtoupper($chr), $character_map);
        }, str_split($country_chracters));
        $country_code = implode('', $characters_code);

        // Place country digits to end of account number
        $new_iban_number = substr($iban_digits, 2) . $country_code . substr($iban_digits, 0, 2);

        // Check the mod
        $check_digits = bcmod($new_iban_number, 97);

        // Finally, return the validation result
        return (int)$check_digits === 1 ? true : false;
    }

    /**
     *
     * Validate Iranian debit card numbers
     *
     * @param $attribute
     * @param $account
     * @param $parameters
     * @return bool
     */
    public function validateIranDebitCard($attribute, $card_number, $parameters,$validator)
    {
        $card_length = strlen($card_number);
        if ($card_length < 16 || substr($card_number, 1, 10) == 0 || substr($card_number, 10, 6) == 0) {
            return false;
        }

        $banks_names = [
            'bmi'           => '603799',
            'banksepah'     => '589210',
            'edbi'          => '627648',
            'bim'           => '627961',
            'bki'           => '603770',
            'bank-maskan'   => '628023',
            'postbank'      => '627760',
            'ttbank'        => '502908',
            'enbank'        => '627412',
            'parsian-bank'  => '622106',
            'bpi'           => '502229',
            'karafarinbank' => '627488',
            'sb24'          => '621986',
            'sinabank'      => '639346',
            'sbank'         => '639607',
            'shahr-bank'    => '502806',
            'bank-day'      => '502938',
            'bsi'           => '603769',
            'bankmellat'    => '610433',
            'tejaratbank'   => '627353',
            'refah-bank'    => '589463',
            'ansarbank'     => '627381',
            'mebank'        => '639370',
        ];

        if (isset($parameters[0]) && (!isset($banks_names[$parameters[0]]) || substr($card_number, 0, 6) != $banks_names[$parameters[0]])) {
            return false;
        }

        $c = (int) substr($card_number, 15, 1);
        $s = 0;
        $k = null;
        $d = null;
        for ($i = 0; $i < 16; $i++) {
            $k = ($i % 2 == 0) ? 2 : 1;
            $d = (int) substr($card_number, $i, 1) * $k;
            $s += ($d > 9) ? $d - 9 : $d;
        }

        return (($s % 10) == 0);
    }

    /**
     * Validate iran mobile.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     * @return bool
     */
    public function validateIranMobile($attribute, $value, $parameters, $validator)
    {
        if (isset($parameters[0]) and $parameters[0] == 'true') {
            return preg_match("/^0?9[0-1-2-3-9]\d{8}$/", $value);
        }

        return preg_match("/^09[0-1-2-3-9]\d{8}$/", $value);
    }

    /**
     * Validate iran phone.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     * @return bool
     */
    public function validateIranPhone($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^0[1-8]\d{9}$/", $value);
    }

    /**
     * Validate iran zip code.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function validateIranZipCode($attribute, $value, $parameters, $validator)
    {
        if (!preg_match('/^([13456789]{10})$/', $value)) {
            return false;
        }
        return true;
    }

    /**
     * Validate iran national code.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function validateIranNationalCode($attribute, $value, $parameters, $validator)
    {
        if (! preg_match('/^[0-9]{10}$/', $value)) {
            return false;
        }
        for ($i = 0; $i < 10; $i++) {
            if ($value == str_repeat($i, 10)) {
                return false;
            }
        }
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((10 - $i) * intval(substr($value, $i, 1)));
        }
        $ret = $sum % 11;
        $parity = intval(substr($value, 9, 1));
        if (($ret < 2 && $ret == $parity) || ($ret >= 2 && $ret == 11 - $parity)) {
            return true;
        }

        return false;
    }
}
