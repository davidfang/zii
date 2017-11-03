<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$modelClass = $generator->modelClass;
$model = new  $modelClass();
$columnOptions = $model->columnOptions();
$imageOptions = $model->imageOptions();
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Delete') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        if(!empty($columnOptions)){
            $columnOptionsArray = json_decode($columnOptions,true);
            foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
                if($key == $column->name){

                    $hasImageOption = false;//没有匹配的图片路径参数
                    if(!empty($imageOptions)) {
                        $imageOptionsArray = json_decode($imageOptions, true);
                        foreach ($imageOptionsArray as $item) {
                            if($item['pathAttribute'] == $column->name || $item['baseUrlAttribute'] == $column->name){
                                $hasImageOption = true;
                            }
                        }
                    }

                    if($hasImageOption == false){
                    switch ($columnOption['type']){
                        case 'radio':
                        case 'dropDown':
                        ?>
            [
                'attribute'=>'<?=$column->name?>',
                'format' => 'html',
                'value' => function($model){
                    $return = '';
                    if(!empty($model-><?=$column->name?>)){
                        $options = <?=$modelClass?>::<?=$column->name?>Options();
                            $return = Html::label($options[$model-><?=$column->name?>]);
                    }
                    return $return;
                }
            ],
<?php                            break;
                        case 'checkbox':
                            ?>
            [
                'attribute'=>'<?=$column->name?>',
                'format' => 'html',
                'value' => function($model){
                    $return = '';
                    if(!empty($model-><?=$column->name?>)){
                        $options = <?=$modelClass?>::<?=$column->name?>Options();
                        foreach ($model-><?=$column->name?> as $value){
                            $return .= ' '.Html::label($options[$value]);
                        }
                    }
                    return $return;
                }
            ],
<?php                           break;
                            case 'createdAt':
                            case 'updatedAt':
                                echo "            '" . $column->name .  ":datetime"  . "',\n";
                                break;
                            case 'createdBy':
                            case 'updatedBy':
                                $handleBy = json_decode($columnOption['params'],true);
                                ?>
            [
                'attribute'=>'<?=$column->name?>',
                'value' => function($model){
                        return $model-><?=Inflector::variablize($handleBy['attribute'])?>-><?=$handleBy['target']?>;
                    }
            ],
<?php
                                break;
                            case 'date':

                            default:
                                $format = $generator->generateColumnFormat($column);
                                echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                                break;
                    }}
                }
            }
        }

    }
}
?>
<?php
if(!empty($imageOptions)){
    $imageOptionsArray = json_decode($imageOptions,true);
    foreach ($imageOptionsArray as $item) {
        if(isset($item['multiple']) && $item['multiple']){//多图
            ?>
            [
                'attribute'=>'<?=$item['attribute']?>',
                //'thumbnail_path:image',
                'format' => 'html',
                'value'=> function($model) {
                    $return = '';
                    $foreignKeys = $model->get<?=\yii\helpers\Inflector::pluralize($item['uploadRelation'])?>();
                    if( $foreignKeys) {
                        foreach ($foreignKeys as $item) {
                            $return .= Html::img(Yii::$app->glide->createSignedUrl([
                                'glide/index',
                                'path' => $item-><?=$item['pathAttribute']?>,
                                'w' => 200
                            ], true)).'<br>';
                        }
                    }//var_dump($return);
                    return $return;
                }
            ],
<?php
        }else{//单图
    ?>
            [
                'attribute'=>'<?=$item['attribute']?>',
                //'thumbnail_path:image',
                'format' => ['image',['width'=>'100','height'=>'100','title'=>$model-><?=$item['pathAttribute'] ?>]],
                'value'=> Yii::$app->glide->createSignedUrl([
                    'glide/index',
                    'path' => $model-><?=$item['pathAttribute'] ?>,
                    'w' => 200
                ], true),
            ],
<?php      }
    }
}
?>
        ],
    ]) ?>

</div>
