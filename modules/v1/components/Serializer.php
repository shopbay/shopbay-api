<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace app\modules\v1\components;

use Sii;
/**
 * Description of Serializer
 *
 * @author kwlok
 */
class Serializer extends \yii\rest\Serializer 
{
    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        if ($data instanceof \CActiveRecord) 
            return $this->serializeApiModel($data);
        elseif ($data instanceof \CDataProvider) {
            return $this->serializeApiDataProvider($data);
        } elseif ($data instanceof \Exception){
            return $this->serializeApiErrors($data);
        } else {
            return parent::serialize($data);
        }
    }
    /**
     * Serializes a model object.
     * @param CActiveRecord $model
     * @return array the array representation of the model
     */
    protected function serializeApiModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
//            list ($fields, $expand) = $this->getRequestedFields();
//            return $model->toArray($fields, $expand);
            return $model->toArray();
        }
    }
    /**
     * Serializes a data provider.
     * @param CDataProvider $dataProvider
     * @return array the array representation of the model
     */
    protected function serializeApiDataProvider($dataProvider)
    {
        $models = $dataProvider->data;
        
        if (($_pagination_ = $dataProvider->getPagination()) !== false) {
            $pagination = new \yii\data\Pagination();
            $pagination->setPageSize($_pagination_->pageSize);
            $pagination->setPage($_pagination_->currentPage);
            $pagination->totalCount = (int)$_pagination_->itemCount;
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                return array_merge($result, $this->serializePagination($pagination));
            } else {
                return $result;
            }
        }
    }    
    /**
     * Serializes an api error object.
     * @param Exception $ex
     * @return array the array representation of the error
     */
    protected function serializeApiErrors($ex)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
            $errorModel = [
                'name' => ($ex instanceof \ServiceException)?$ex->getName():'Error',
                'code' => $ex->getCode(), 
            ];
            if ($ex instanceof \ServiceValidationException){
                $errorModel['message'] = Sii::t('sii','Validation Error');
                $errorModel['details'] = json_decode($ex->getMessage(),true);
                $errorModel['status'] = 422;
                $this->response->setStatusCode(422, 'Data Validation Failed');
            }
            else { //set generic bad request response
                $errorModel['message'] = $ex->getMessage();
                $errorModel['status'] = 400;
                $this->response->setStatusCode(400);
            }
            if (YII_DEBUG)
                $errorModel['type'] = get_class($ex);
                
            return $errorModel;
        }
    }        
}
