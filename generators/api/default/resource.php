<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\api\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$resourcesClass = StringHelper::basename($generator->resourcesClass);
if ($modelClass === $resourcesClass) {
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

$tableSchema = $generator->getTableSchema();
echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->resourcesClass, '\\')) ?>;

use yii\helpers\Url;
<?php
if($generator->enableLinkable){
?>
use yii\web\Linkable;
use yii\web\Link;
<?php } ?>

/**
 * <?= $resourcesClass ?> resources the model behind  form of `<?= $generator->modelClass ?>`.
 */
class <?= $resourcesClass ?> extends \<?= $generator->modelClass ?> <?= $generator->enableLinkable ? "implements Linkable \n":''?>
{

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }

    public function extraFields()
    {
        return [<?php
if($tableSchema != false) {
    foreach ($tableSchema->columns as $column) {
        if (!empty($columnOptions)) {
            $columnOptionsArray = json_decode($columnOptions, true);
            foreach ($columnOptionsArray as $key => $columnOption) {//自定义数据列循环
                if ($key == $column->name) {
                    if($columnOption['type'] == 'createdBy' || $columnOption['type'] == 'updatedBy' ){
                        $handleBy = json_decode($columnOption['params'],true);
                        echo "'".Inflector::variablize($handleBy['attribute'])."',";
                    }
                }
            }
        }
    }
}
?>];
    }
<?php
if($generator->enableLinkable){
?>
    /**
     * Returns a list of links.
     *
     * @return array the links
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['<?=Inflector::camel2id(rtrim(StringHelper::basename($generator->controllerClass),'Controller'))?>/view', 'id' => $this->id], true)
        ];
    }
<?php } ?>
}