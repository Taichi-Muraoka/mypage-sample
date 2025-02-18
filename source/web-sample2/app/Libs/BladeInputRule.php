<?php

namespace App\Libs;

use Closure;

/**
 * Bladeの共通処理
 * コントローラから渡されたバリデートルールを解析する
 */
class BladeInputRule
{

    /**
     * 数値型の入力項目の場合、数値キーボードにする。
     */
    public function setNumKeyboard($rules, $id)
    {

        // input type="number"はブラウザ依存が激しいので、pattern="\d*"を使ってみる。
        // rulesにキーがあるか取得
        if (isset($rules[$id])) {
            $key = array_search("integer", $rules[$id]);

            if ($key !== false) {
                return "pattern='\d*'";
            }
            return "";
        }
    }

    /**
     * maxlengthを取得
     */
    public function getMaxLength($rules, $id)
    {
        // rulesにキーがあるか取得
        if (isset($rules[$id])) {

            // stringでmaxlenghを取得
            // stringがあるかチェック
            $key = array_search("string", $rules[$id]);

            if ($key !== false) {

                // maxを探す
                foreach ($rules[$id] as $key => $value) {

                    if ($value instanceof Closure) {
                        // クロージャーは無視
                        continue;
                    }

                    if (strpos($value, 'max:') !== false) {
                        // 数値部分を取得
                        $len = str_replace("max:", "", $value);
                        return "maxlength=" . $len;
                    }
                }
            }

            // integerの場合。100の場合、100以下であることがバリデートされるが、
            // maxlengthとしては3桁にしたい
            $key = array_search("integer", $rules[$id]);

            if ($key !== false) {

                // max:の数値部分の文字数を取得
                $numlength = $this->getMaxStrlen($rules[$id]);
                if (isset($numlength)) {
                    return "maxlength=" . $numlength;
                }
            }

            // numericの場合。999.9の場合、999.9以下であることがバリデートされるが、
            // maxlengthとしては5桁にしたい
            $key = array_search("numeric", $rules[$id]);

            if ($key !== false) {

                // max:の数値部分の文字数を取得
                $numlength = $this->getMaxStrlen($rules[$id]);
                if (isset($numlength)) {
                    return "maxlength=" . $numlength;
                }
            }
        }
        return "";
    }

    /**
     * max:の数値部分の文字数を取得
     */
    private function getMaxStrlen($rules)
    {
        // maxを探す
        foreach ($rules as $key => $value) {

            if ($value instanceof Closure) {
                // クロージャーは無視
                continue;
            }

            if (strpos($value, 'max:') !== false) {
                // 数値部分を取得
                $len = str_replace("max:", "", $value);
                $numlength = strlen((string)$len);
                return $numlength;
            }
        }
        return;
    }
}
