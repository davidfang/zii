<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$modelClass = $generator->modelClass;
$model = new  $modelClass();
$columnOptions = $model->columnOptions();
$imageOptions = $model->imageOptions();
echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {

        if (++$count < 6) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            //'" . $name . "',\n";
        }

    }
} else {
    foreach ($tableSchema->columns as $column) {
        if(!empty($columnOptions)) {
            $columnOptionsArray = json_decode($columnOptions, true);
            foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
                if ($key == $column->name) {

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
                        case 'dropDown':?>
            [
                'attribute'=>'<?=$column->name?>',
                'format' => 'html',
                'filter' => <?=$modelClass?>::<?=$column->name?>Options(),
                'value' => function($model){
                    $return = '';
                    if(!empty($model-><?=$column->name?>)){
                        $options = <?=$modelClass?>::<?=$column->name?>Options();
                        $return = Html::label($options[$model-><?=$column->name?>]);
                    }
                    return $return;
                }
            ],
<?php                       break;
                        case 'checkbox':?>
            [
                'attribute'=>'<?=$column->name?>',
                'format' => 'html',
                'filter' => <?=$modelClass?>::<?=$column->name?>Options(),
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
<?php                       break;
                        //case 'date':
                        case 'createdAt':

                        case 'updatedAt':?>
            [
                'attribute' => '<?=$column->name?>',
                'format' => ['date', "php:Y-m-d H:i:s"],
                'headerOptions' => ['width' => '12%'],
                'filter' => kartik\daterange\DateRangePicker::widget([
                    'name' => '<?=!empty($generator->searchModelClass) ? Inflector::id2camel(StringHelper::basename($generator->searchModelClass)) : '_search'?>[<?=Inflector::variablize($column->name)?>]',
                    'value' => Yii::$app->request->get('<?=!empty($generator->searchModelClass) ? Inflector::id2camel(StringHelper::basename($generator->searchModelClass)) : '_search'?>')['<?=Inflector::variablize($column->name)?>'],
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => '/',
                        ]
                    ]
                ])
            ],
<?php                            //echo "            '" . $column->name .  ":datetime"  . "',\n";
                            break;
                        case 'createdBy':
                        case 'updatedBy':
                        $handleBy = json_decode($columnOption['params'],true);
                            ?>
            [
                'attribute' => '<?=$column->name?>_<?=$handleBy['target']?>',
                 'label' => '<?=$column->comment?>',
                'value' => '<?=Inflector::variablize($handleBy['attribute'])?>.<?=$handleBy['target']?>'
            ],
<?php                        break;
                        default:
                            $format = $generator->generateColumnFormat($column);
                            if (++$count < 6) {
                                echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                            } else {
                                echo "            //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                            }
                            break;
                    }
                }
            }}
        }else {

            $format = $generator->generateColumnFormat($column);
            if (++$count < 6) {
                echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
            } else {
                echo "            //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
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
                                                'w' => 50
                                                ], true)).'<br>';
                            }
                     }
                    return $return;
                }
            ],
<?php
            }else{//单图
                ?>
            [
                'attribute'=>'<?=$item['attribute']?>',
                //'thumbnail_path:image',
                'format' => ['image',['width'=>'50','height'=>'50','title'=>$model-><?=$item['pathAttribute'] ?>]],
                'value'=> function($model){
                        return Yii::$app->glide->createSignedUrl([
                                    'glide/index',
                                    'path' => $model-><?=$item['pathAttribute'] ?>,
                                    'w' => 50
                                    ], true);
                    }
            ],
<?php      }
        }
    }
    ?>
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>
<?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>
</div>
