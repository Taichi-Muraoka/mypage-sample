<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Builder;
use Closure;

/**
 * SQLビルダーの拡張
 */
class AppBuilder extends Builder
{

    /**
     * Add a join clause to the query.
     * 論理削除対応。引数にモデルを渡す
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @param  bool  $where
     * @param  string  $as テーブル名に別名を指定したい場合
     * @return $this
     */
    public function sdJoin($model, $first, $operator = null, $second = null, $type = 'inner', $where = false, $as = null)
    {
        // モデル取得
        $modelObj = new $model();

        // テーブル名取得
        $table = $modelObj->getTable();
        $tableName = null;

        // as(テーブルの別名)を取得
        // 同じテーブルを複数回JOINする場合がある(例：コードマスタ)
        if ($first instanceof Closure) {
            // クロージャの場合は$operatorから取得する。
            if (filled($operator)) {
                // 別名を負荷
                $table =  $table . ' as ' . $operator;
                $tableName = $operator;
            }
        } else {
            // クロージャではない場合は、最後の引数から取得する
            if (filled($as)) {
                // 別名を負荷
                $table =  $table . ' as ' . $as;
                $tableName = $as;
            }
        }

        // テーブル名デフォルト
        if (!filled($tableName)) {
            $tableName = $modelObj->getTable();
        }

        // joinを呼ぶ
        return parent::join($table, function ($join) use ($modelObj, $first, $operator, $second, $tableName) {

            if ($first instanceof Closure) {
                // クロージャの場合、そのまま呼ぶ
                $first($join);
            } else {
                // ON(キー項目でJOINする)
                $join->on($first, $operator, $second);
            }

            // SoftDeletesを持っているかどうか。forceDeletingプロパティの有無にした
            $softDelete = property_exists($modelObj, 'forceDeleting');

            // 論理削除の絞り込み。必ず追加する。
            if ($softDelete) {
                $join->whereNull($tableName . '.' . 'deleted_at');
            }
        }, null, null, $type, $where);
    }

    /**
     * Add a left join to the query.
     * 論理削除対応。引数にモデルを渡す
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $as テーブル名に別名を指定したい場合
     * @return $this
     */
    public function sdLeftJoin($model, $first, $operator = null, $second = null, $as = null)
    {
        // モデル取得
        $modelObj = new $model();

        // テーブル名取得
        $table = $modelObj->getTable();
        $tableName = null;

        // as(テーブルの別名)を取得
        // 同じテーブルを複数回JOINする場合がある(例：コードマスタ)
        if ($first instanceof Closure) {
            // クロージャの場合は$operatorから取得する。
            if (filled($operator)) {
                // 別名を負荷
                $table =  $table . ' as ' . $operator;
                $tableName = $operator;
            }
        } else {
            // クロージャではない場合は、最後の引数から取得する
            if (filled($as)) {
                // 別名を負荷
                $table =  $table . ' as ' . $as;
                $tableName = $as;
            }
        }

        // テーブル名デフォルト
        if (!filled($tableName)) {
            $tableName = $modelObj->getTable();
        }

        // joinを呼ぶ
        return parent::leftJoin($table, function ($join) use ($modelObj, $first, $operator, $second, $tableName) {

            if ($first instanceof Closure) {
                // クロージャの場合、そのまま呼ぶ
                $first($join);
            } else {
                // ON(キー項目でJOINする)
                $join->on($first, $operator, $second);
            }

            // SoftDeletesを持っているかどうか。forceDeletingプロパティの有無にした
            $softDelete = property_exists($modelObj, 'forceDeleting');

            // 論理削除の絞り込み。必ず追加する。
            if ($softDelete) {
                $join->whereNull($tableName . '.' . 'deleted_at');
            }
        });
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        // このupdateはモデルのupdateではなく、ビルダーのupdateを使用した場合こちらが呼ばれる
        // 更新時、空白をnullに変換する処理
        foreach ($values as &$value) {
            $value = filled($value) ? $value : null;
        }

        // 親の処理を呼ぶ
        return parent::update($values);
    }
}
