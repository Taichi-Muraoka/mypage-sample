<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\CtrlDateTrait;

/**
 * アプリケーション用の独自バリデーションの追加 - Provider
 *
 * 独自バリデーションと区別がつくように、「vdXXXX」のようなルール名にする
 */
class ValidatorServiceProvider extends ServiceProvider
{
    use CtrlDateTrait;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //-----------------------
        // 半角英数字チェック
        //-----------------------
        // 半角英数字のみ
        Validator::extend('vdAlphaNum', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[a-zA-Z0-9]+$/', $value);
        });

        //-----------------------
        // 時刻の形式チェック
        //-----------------------
        // デフォルトのチェックの場合、'date_format:H:i'でできるが、以下のメッセージになるため
        // H:i形式で指定してください。
        Validator::extend('vdTime', function ($attribute, $value, $parameters, $validator) {
            // 時刻形式のチェックを外出しにする
            return $this->dtCheckTimeFormat($value);
        });

        //-----------------------
        // パスワードの形式チェック
        //-----------------------
        // 半角英数字混合（少なくとも各1文字以上の英字と数字。英字は大文字でも可） 8文字以上20文字以下
        Validator::extend('vdPassword', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]{8,20}$/', $value);
        });

        //-----------------------
        // 電話番号の形式チェック
        //-----------------------
        // ハイフンありなしどちらも許容する
        Validator::extend('vdTelephone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(0{1}\d{1,4}-{0,1}\d{1,4}-{0,1}\d{4})$/', $value);
        });

        //-----------------------
        // 金額の形式チェック
        //-----------------------
        // 取込時に金額にはカンマが含まれる場合があるため、カンマを許可する。ただし最初・最後・連続は不許可。
        Validator::extend('vdPrice', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^-?(?!^[,])(?!.*[,]$)(?!.*[,]{2})[0-9,]+\s?$/', $value);
        });

        //-----------------------
        // 金額の桁数チェック
        //-----------------------
        // 金額の形式のみチェックしたい場合を考慮し、金額形式と分離した。
        Validator::extend('vdPriceDigits', function ($attribute, $value, $parameters, $validator) {

            $price = (string) $value;
            // 半角スペースを除外する
            $price = str_replace(' ', '', $price);
            // 文字数カウントのためカンマをとる
            if (preg_match('/,/', $price)) {
                $price = str_replace(',', '', $price);
            }

            $maxDigits = 8;
            // マイナスがある場合は最大文字数を9にする
            if (preg_match('/-/', $price)) {
                $maxDigits = 9;
            }

            // 最大文字数以下ならOK
            return strlen($price) <= $maxDigits;
        });

        //-----------------------
        // 時刻のFromToチェック（after）
        // ※vdTimeと併用すること
        //-----------------------
        // $parametersより後の時間かどうか
        Validator::extend('vdAfterTime', function ($attribute, $value, $parameters, $validator) {
            if (!$this->dtCheckTimeFormat($value)) {
                // 時刻の形式エラーとなる場合、ここでは検出せずスキップする
                return true;
            }
            return strtotime($validator->getData()[$parameters[0]]) < strtotime($value);
        });

        //-----------------------
        // 小数許可項目の形式チェック
        // ※numeric・maxと併用すること
        //-----------------------
        // 整数または小数点以下指定桁数までの小数
        // マイナス値許可とする（制限する場合はminで設定）
        Validator::extend('vdDecimal', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^-?\d+(\.\d{1,' . $parameters[0] . '})?$/', $value);
        });

        Validator::replacer(
            'vdDecimal',
            function ($message, $attribute, $rule, $parameters) {
                return str_replace(':vdDecimal', $parameters[0], $message);
            }
        );
    }
}
