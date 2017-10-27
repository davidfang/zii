<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$modelClass = $generator->modelClass;
$model = new  $modelClass();
$columnOptions = $model->columnOptions();

$modelClass = StringHelper::basename($generator->modelClass);
echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

/**
 * <?= $searchModelClass ?> represents the model behind the search form of `<?= $generator->modelClass ?>`.
 */
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
<?php
if(!empty($columnOptions)) {
    $columnOptionsArray = json_decode($columnOptions, true);
    $tableColumnOptionRule = '';
    foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
        if($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy'){
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params'],true);
            echo "    public \${$key}_{$params['target']};\n";
            $tableColumnOptionRule .= "'{$key}_{$params['target']}',";
        }
        if($columnOption['type'] == 'createdAt' || $columnOption['type'] == 'updatedAt'){
            echo "    public \$".\yii\helpers\Inflector::variablize($key).";\n";
            $tableColumnOptionRule .= "'".\yii\helpers\Inflector::variablize($key)."',";
        }
    }
    if($tableColumnOptionRule != ''){
        $rules[] = '[['.$tableColumnOptionRule ."], 'safe']";
    }
}
?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            <?= implode(",\n            ", $rules) ?>,
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();
<?php
if(!empty($columnOptions)) {
    $columnOptionsArray = json_decode($columnOptions, true);
    $tableColumnOptionRule = '';
    foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
        if ($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy') {
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params'],true);
            ?>
        $query->joinWith(['<?=\yii\helpers\Inflector::variablize($key)?> <?=\yii\helpers\Inflector::variablize($key.'_'.$params['table'])?>']);
<?php
        }
    }
}
?>
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
<?php
if(!empty($columnOptions)) {
    $columnOptionsArray = json_decode($columnOptions, true);
    $tableColumnOptionRule = '';
    foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
        if ($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy') {
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params'],true);
            ?>
        $dataProvider->sort->attributes['<?=$key.'_'.$params['target']?>'] = [
            'asc' => ['<?=\yii\helpers\Inflector::variablize($key.'_'.$params['table'])?>.<?=$params['target']?>' => SORT_ASC],
            'desc' => ['<?=\yii\helpers\Inflector::variablize($key.'_'.$params['table'])?>.<?=$params['target']?>' => SORT_DESC],
            'label' => '<?= $labels[$key]?>'
        ];
            <?php
        }
    }
}
?>
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        <?= implode("\n        ", $searchConditions) ?>
<?php
if(!empty($columnOptions)) {
    $columnOptionsArray = json_decode($columnOptions, true);
    $tableColumnOptionRule = '';
    foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
        if ($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy') {
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params'],true);
            ?>
            $query->andFilterWhere(['like', '<?=\yii\helpers\Inflector::variablize($key.'_'.$params['table'])?>.<?=$params['target']?>', $this-><?=$key.'_'.$params['target']?>]) ;//<=====加入这句
<?php
        }
        if ($columnOption['type'] == 'createdAt' || $columnOption['type'] == 'updatedAt') {?>

        if (!empty($this-><?=\yii\helpers\Inflector::variablize($key)?>)) {
            $query->andFilterCompare(static::tableName().'.<?=$key?>', strtotime(explode('/', $this-><?=\yii\helpers\Inflector::variablize($key)?>)[0]), '>=');//起始时间
            $query->andFilterCompare(static::tableName().'.<?=$key?>', (strtotime(explode('/', $this-><?=\yii\helpers\Inflector::variablize($key)?>)[1]) + 86400), '<');//结束时间
        }

<?php   }

    }
}
?>
        return $dataProvider;
    }
}
