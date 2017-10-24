<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $tableColumnImages array list of attribute (key => value) */
/* @var $tableColumnOptions array list of attribute options (name => label)  */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
<?php if (!empty($tableColumnImages)){?>
use trntv\filekit\behaviors\UploadBehavior;
<?php } ?>
<?php if(!empty($tableColumnOptions)) {
    $TimestampBehavior = false;
    $BlameableBehavior = false;
    foreach ($tableColumnOptions as $columnKey => $columnOption) {
        if($columnOption['type'] == 'createAt' || $columnOption['type'] == 'updateAt'){
            $TimestampBehavior = true;
        }
        if($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy'){
            $BlameableBehavior = true;
        }
    }
    echo $TimestampBehavior ? "use yii\behaviors\TimestampBehavior;\n":'';
    echo $BlameableBehavior ? "use yii\behaviors\BlameableBehavior;\n":'';
}?>

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($properties as $property => $data): ?>
 * @property <?= "{$data['type']} \${$property}"  . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
<?php if(!empty($tableColumnOptions)) {
    foreach ($tableColumnOptions as $columnKey => $columnOption) {
        if (in_array($columnOption['type'], ['radio', 'checkbox', 'dropDown'])) {
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params']);
            //var_dump($columnOption['params'],$params);
            foreach ($params as $paramKey => $paramValue) {
                ?>
    const <?= strtoupper($columnKey . '_' . $paramValue->key) ?> = '<?= $paramValue->key ?>';// <?= $paramValue->label."\n" ?>
<?php
            }
        }
    }
}
?>
<?php if(!empty($tableColumnOptions)) {
    foreach ($tableColumnOptions as $columnKey => $columnOption) {
        if (in_array($columnOption['type'], ['radio', 'checkbox', 'dropDown'])) {
            $params = empty($columnOption['params']) ? [] : json_decode($columnOption['params']);
            ?>
    /**
     * @inheritdoc
     */
    public static function <?=\yii\helpers\Inflector::variablize($columnKey)?>Options()
    {
        return [
<?php foreach ($params as $paramKey => $paramValue) {?>
                self::<?= strtoupper($columnKey. '_' . $paramValue->key) ?> => '<?= $paramValue->label ?>',
<?php }?>
            ];
    }
<?php
        }
    }
}
?>

    /**
     * This method is invoked before validation starts.
     * The default implementation raises a `beforeValidate` event.
     * You may override this method to do preliminary checks before validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     * @return bool whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    public function beforeValidate()
    {
<?php if(!empty($tableColumnOptions)) {
    foreach ($tableColumnOptions as $columnKey => $columnOption) {
        if($columnOption['type'] == 'checkbox'){
        ?>
        if($this-><?=$columnKey?> && is_array($this-><?=$columnKey?>)) {
             $this-><?=$columnKey?> = join(',', $this-><?=$columnKey?>);
        }
<?php }
    }
}
?>
        return parent::beforeValidate();
    }
    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
<?php if(!empty($tableColumnOptions)) {
        foreach ($tableColumnOptions as $columnKey => $columnOption) {
            if($columnOption['type'] == 'checkbox'){
?>
        $this-><?=$columnKey?> = explode(',',$this-><?=$columnKey?>);
<?php }
    }
}
?>
        $this->trigger(parent::EVENT_AFTER_FIND);
    }


<?php if (!empty($tableColumnImages)){
    $tableColumnImagesArray = json_decode($tableColumnImages,true);
    $tableColumnImageRule = '';
    if(!empty($tableColumnImagesArray)){
    foreach ($tableColumnImagesArray as $tableColumnImage){
        $tableColumnImageRule .= "'{$tableColumnImage['attribute']}',";
    ?>
    /**
    * @var array
    * 上传图像 <?= isset($tableColumnImage['multiple']) && $tableColumnImage['multiple'] ? '多张上传' : ''?>
    */
    public $<?=$tableColumnImage['attribute'] ?>;
<?php if(isset($tableColumnImage['multiple']) && $tableColumnImage['multiple'] && isset($relations[$tableColumnImage['uploadRelation']])){ ?>
    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?=\yii\helpers\Inflector::pluralize($tableColumnImage['uploadRelation'])?>()
    {
        return $this->hasMany(<?=$tableColumnImage['uploadRelation']?>::className(), ['<?=$tableColumnImage['foreignKey']?>' => 'id'])->all();
    }
<?php }}}
    $rules[] = '[['.$tableColumnImageRule ."], 'safe']";
} ?>
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
<?php if(!empty($tableColumnOptions)) {
    $TimestampBehavior = false;
    $BlameableBehavior = false;
    foreach ($tableColumnOptions as $columnKey => $columnOption) {
        if($columnOption['type'] == 'createAt' ){?>
            [
                 'class' => TimestampBehavior::className(),
                 //'createdAtAttribute' => '<?=$columnKey?>',
                 'attributes' => [
                    self::EVENT_BEFORE_INSERT => '<?=$columnKey?>',
                 ],
             ],
<?php        }
        if( $columnOption['type'] == 'updateAt'){?>
            [
                'class' => TimestampBehavior::className(),
                //'updatedAtAttribute' => '<?=$columnKey?>',
                'attributes' => [
                    self::EVENT_BEFORE_UPDATE => '<?=$columnKey?>',
                ],
            ],
<?php        }

        if($columnOption['type'] == 'createdBy'){?>
            [
                 'class' => BlameableBehavior::className(),
                 //'createdByAttribute' => '<?=$columnKey?>',
                 'attributes' => [
                    self::EVENT_BEFORE_INSERT => '<?=$columnKey?>',
                 ],
             ],
<?php        }
        if($columnOption['type'] == 'updatedBy'){?>
            [
                 'class' => BlameableBehavior::className(),
                 //'updatedByAttribute' => '<?=$columnKey?>',
                 'attributes' => [
                    self::EVENT_BEFORE_UPDATE => '<?=$columnKey?>',
                 ],
             ],
<?php        }
    }
}?>
<?php if (!empty($tableColumnImages)){
    $tableColumnImagesArray = json_decode($tableColumnImages,true);
    if(!empty($tableColumnImagesArray)){
    foreach ($tableColumnImagesArray as $tableColumnImage){
?>
            [
                'class' => UploadBehavior::className(),
                'attribute' => '<?=$tableColumnImage['attribute'] ?>',
                'pathAttribute' => '<?=$tableColumnImage['pathAttribute'] ?>',
                'baseUrlAttribute' => '<?=$tableColumnImage['baseUrlAttribute'] ?>',
<?php if ($tableColumnImage['multiple']){ ?>
                'multiple' => true,
                'uploadRelation' => '<?=\yii\helpers\Inflector::pluralize($tableColumnImage['uploadRelation']) ?>',
                'orderAttribute' => '<?=$tableColumnImage['orderAttribute'] ?>',
                'typeAttribute' => '<?=$tableColumnImage['typeAttribute'] ?>',
                'sizeAttribute' => '<?=$tableColumnImage['sizeAttribute'] ?>',
                'nameAttribute' => '<?=$tableColumnImage['nameAttribute'] ?>',
<?php } ?>
            ],
<?php }}} ?>
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
<?php if (!empty($tableColumnImages)){
    $tableColumnImagesArray = json_decode($tableColumnImages,true);
    if(!empty($tableColumnImagesArray)){
    foreach ($tableColumnImagesArray as $tableColumnImage){
        $imageLabel = isset($tableColumnImage['label']) ? $tableColumnImage['label'] : $generator->generateString($tableColumnImage['attribute']);
        ?>
            <?= "'{$tableColumnImage['attribute']}' => '" . $imageLabel . "',\n" ?>
<?php }}} ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>

    /**
     * 数据表字段属性
     * @return array
     */
    public function columnOptions(){
        return '<?php echo json_encode($tableColumnOptions) ?>';
    }

    /**
     * 图像上传字段属性
     * @return array
     */
    public function imageOptions(){
        return '<?php echo $tableColumnImages ?>';
    }
}