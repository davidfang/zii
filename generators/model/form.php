<?php

use yii\gii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\model\Generator */
//echo '<pre>';
//var_dump($generator);
//echo '</pre>';
echo $form->field($generator, 'tableName')->textInput(['table_prefix' => $generator->getTablePrefix()]);
echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'ns');
echo $form->field($generator, 'baseClass');
echo $form->field($generator, 'db');
echo $form->field($generator, 'useTablePrefix')->checkbox();

echo $form->field($generator, 'generateRelations')->dropDownList([
    Generator::RELATIONS_NONE => 'No relations',
    Generator::RELATIONS_ALL => 'All relations',
    Generator::RELATIONS_ALL_INVERSE => 'All relations with inverse',
]);

echo $form->field($generator, 'generateRelationsFromCurrentSchema')->checkbox();
echo $form->field($generator, 'generateLabelsFromComments')->checkbox();
echo $form->field($generator, 'generateQuery')->checkbox();
echo $form->field($generator, 'queryNs');
echo $form->field($generator, 'queryClass');
echo $form->field($generator, 'queryBaseClass');
echo $form->field($generator, 'enableI18N')->checkbox();
echo $form->field($generator, 'messageCategory');
echo $form->field($generator, 'useSchemaName')->checkbox();

echo $form->field($generator, 'tableColumnSelect')->checkbox();
if($generator->tableName != '') {
    echo '<div class="form-group">';
    $columnItems = [
        ''=>'请选择',
        'radio'=>'单选按钮',
        'checkbox'=>'复选框',
        'dropDown'=>'下拉框',
        'date'=>'日期',
    ];
    foreach ($generator->generateColumnOptions() as $table => $columnOption) {
        //var_dump($columnOption);
        //echo $form->field($generator, "tableColumnOptions[$table]")->checkboxList($columnOption);
        //echo $columnOption->comment .'----'.join('==',$columnItems);
        echo '<div class="row">表 '.$table .'参数配置</div>';
        foreach ($columnOption as $key => $item) {
            echo $form->field($generator,"tableColumnOptions[$table][$key][type]")->dropDownList($columnItems)->label('字段 '.$key.' 形式');
            echo $form->field($generator, "tableColumnOptions[$table][$key][params]")->textarea()->label('字段 '.$key.' 配置信息');
        }

    }

    echo '</div>';
}