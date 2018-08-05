<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\controllers;

use Yii;
use app\controllers\ResourceController;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

/**
 * Description of ActiveController
 *
 * @author kwlok
 */
class ActiveController extends ResourceController
{
    public function actionView($id)
    {
        $this->checkAccess($this->id);
        
        $model = $this->findModel($id);
        
        return $model;
    }    
    
    public function actionIndex()
    {        
        $this->checkAccess($this->id);
        
        $modelClass = $this->modelClass;
        
        return new ActiveDataProvider([
            'query' => $modelClass::find(),
            'pagination' => [
                 'pageSize' => isset($_GET['per-page'])?$_GET['per-page']:5,
            ],
        ]);
    }
    /**
     * Creates a new model.
     * @return \yii\db\ActiveRecordInterface the model newly created
     * @throws ServerErrorHttpException if there is any error when creating the model
     */
    public function actionCreate()
    {
        $this->checkAccess($this->id);
        
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);
        
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
            
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }
    /**
     * Updates an existing model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface the model being updated
     * @throws ServerErrorHttpException if there is any error when updating the model
     */
    public function actionUpdate($id)
    {
        $this->checkAccess($this->id);
                
        $model = $this->findModel($id);

        $model->scenario = $this->scenario;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return $model;
    } 
    /**
     * Deletes a model.
     * @param mixed $id id of the model to be deleted.
     * @throws ServerErrorHttpException on failure.
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->id);

        $model = $this->findModel($id);

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
    /**
     * Returns the data model (Yii2) based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded. If the model has a composite primary key,
     * the ID must be a string of the primary key values separated by commas.
     * The order of the primary key values should follow that returned by the `primaryKey()` method
     * of the model.
     * @return ActiveRecordInterface the model found
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }

        if (isset($model)) {
            return $model;
        } else {
            throw new \CException("Object not found: $id",404);//http not found
        }
    }   
    /**
     * @inheritdoc
     */
    protected function permissions() 
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    protected function subscriptions()
    {
        return [];
    }
}
