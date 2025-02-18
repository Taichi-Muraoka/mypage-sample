<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Exception;
use App\Database\AppBuilder;
use App\Http\Controllers\Traits\CtrlModelTrait;
use Illuminate\Support\Facades\Log;

/**
 * モデルの共通処理
 */
trait ModelTrait
{

    // モデル共通処理を追加
    // コントローラでも呼べるがモデルでも使用できるようにした
    use CtrlModelTrait;

    /**
     * 項目を取得する関数
     */
    abstract protected static function getFieldRules();

    /**
     * テーブルの項目の定義(型、サイズなど)
     * 
     * @param string $name 項目名
     * @param array $rules ルール
     * @param string 接尾語 (ルールの名称の取得)
     */
    public static function fieldRules($name, $rules = [], $suffix = "")
    {
        // ルールを返却
        return [$name => array_merge(
            $rules,
            // 各モデルに定義しているルールを取得
            // suffixはCSV用のルールを取得するなど、同じ項目でもルールが違う場合に使用する
            self::getFieldRules()[$name . $suffix]
        )];
    }

    /**
     * テーブルの項目の定義(型、サイズなど)を取得
     * 
     * @param string $name 項目名
     */
    public static function getFieldRule($name)
    {
        // ルールを返却
        return self::getFieldRules()[$name];
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        // joinを追加するためビルダーを置き換える
        return new AppBuilder($query);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        // model->save()に対応
        // テーブルの複合キーに対応するため、Modelの関数をオーバーライド
        // ここでは複合キーに対応
        if (is_array($this->primaryKey)) {

            foreach ($this->getKeyName() as $key) {
                if (isset($this->$key))
                    $query->where($key, '=', $this->$key);
                else
                    throw new Exception(__METHOD__ . ' Missing part of the primary key: ' . $key);
            }

            return $query;
        } else {
            // 複合キーではない場合は通常通りの処理
            return parent::setKeysForSaveQuery($query);
        }
    }

    /**
     * 更新時、空白をnullに変換する処理
     */
    protected static function whenSaveEmptyToNull()
    {
        // update時
        static::updating(function ($model) {
            foreach ($model->attributes as $key => $value) {
                $model->{$key} = filled($value) ? $value : null;
            }
        });

        // save時
        static::saving(function ($model) {
            foreach ($model->attributes as $key => $value) {
                $model->{$key} = filled($value) ? $value : null;
            }
        });
    }

    /**
     * ログを保存するかどうかのフラグ
     * デフォルトは有効
     */
    protected $saveToLogFlg = true;

    /**
     * ログを保存するかどうかのフラグをセットする
     * 
     * @param bool $flg フラグ
     */
    public function setSaveToLogFlg($flg)
    {
        $this->saveToLogFlg = $flg;
    }

    /**
     * テーブル操作をログに記録する
     * 
     * どういうデータが保存されたかまでは保持しない
     * updatingなどのイベントは、複数回定義しても問題なかった(定義された分呼ばれる)
     * 必要ならwhenSaveEmptyToNullと同じイベントを定義しても良い
     */
    protected static function saveToLog()
    {

        // ログを出力する関数
        $toLog = function ($model, $operation) {

            // 保存するかどうかのチェック
            if (!$model->saveToLogFlg) {
                // 何もしない
                return;
            }

            // テーブル名
            $tableName = $model->getTable();

            // キー項目の取得(値と項目名)
            // getKeyだとうまく取得できないので直接取得
            $keyCond = "";
            if (is_array($model->primaryKey)) {
                // 複合キーの場合
                foreach ($model->getKeyName() as $key) {
                    if (isset($model->$key))
                        $keyCond .= "{" . $key . '=' . $model->$key . "}";
                    else
                        throw new Exception(__METHOD__ . ' Missing part of the primary key: ' . $key);
                }
            } else {
                // 複合キー以外の場合
                $keyCond .= "{" . $model->getKeyName() . '=' . $model->getKey() . "}";
            }

            // ログに出力
            Log::info("[$operation $tableName] $keyCond" . $model->table1);
        };

        // retrievedはモデル読み込み時なので不要とした
        // savedはcreatedとupdatedの両方を含んでいるので不要とした

        // created
        static::created(function ($model) use ($toLog) {
            $toLog($model, "created");
        });

        // updated
        static::updated(function ($model) use ($toLog) {
            $toLog($model, "updated");
        });

        // deleted
        static::deleted(function ($model) use ($toLog) {
            $toLog($model, "deleted");
        });

        // restored
        static::restored(function ($model) use ($toLog) {
            // 会員復活時など、論理削除されたものを戻す場合
            $toLog($model, "restored");
        });
    }
}
