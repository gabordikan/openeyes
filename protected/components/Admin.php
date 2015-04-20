<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * A class for generic admin actions on a model
 */
class Admin
{
	/**
	 * @var BaseActiveRecord
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $modelName;

	/**
	 * @var string
	 */
	protected $listTemplate = '//admin/generic/list';

	/**
	 * @var string
	 */
	protected $editTemplate = '//admin/generic/edit';

	/**
	 * @var array
	 */
	protected $listFields = array();

	/**
	 * @var array
	 */
	protected $editFields = array();

	/**
	 * @var BaseAdminController
	 */
	protected $controller;

	/**
	 * @var CPagination
	 */
	protected $pagination;

	/**
	 * @var ModelSearch
	 */
	protected $search;

	/**
	 * @var int
	 */
	protected $modelId;

	/**
	 * @var string
	 */
	protected $customSaveURL;

	/**
	 * @var string
	 */
	protected $customCancelURL;

	/**
	 * @return BaseActiveRecord
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @param BaseActiveRecord $model
	 */
	public function setModel(BaseActiveRecord $model)
	{
		$this->model = $model;
		if(!$this->modelName) {
			$this->modelName = get_class($model);
		}
	}

	/**
	 * @return string
	 */
	public function getModelName()
	{
		return $this->modelName;
	}

	/**
	 * @param string $modelName
	 */
	public function setModelName($modelName)
	{
		$this->modelName = $modelName;
	}

	/**
	 * @return ModelSearch
	 */
	public function getSearch()
	{
		return $this->search;
	}

	/**
	 * @param ModelSearch $search
	 */
	public function setSearch($search)
	{
		$this->search = $search;
	}

	/**
	 * @return string
	 */
	public function getListTemplate()
	{
		return $this->listTemplate;
	}

	/**
	 * @param string $listTemplate
	 */
	public function setListTemplate($listTemplate)
	{
		$this->listTemplate = $listTemplate;
	}

	/**
	 * @return array
	 */
	public function getListFields()
	{
		return $this->listFields;
	}

	/**
	 * @param array $listFields
	 */
	public function setListFields($listFields)
	{
		$this->listFields = $listFields;
	}

	/**
	 * @return mixed
	 */
	public function getPagination()
	{
		return $this->pagination;
	}

	/**
	 * @param mixed $pagination
	 */
	public function setPagination($pagination)
	{
		$this->pagination = $pagination;
	}

	/**
	 * @return int
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * @param int $modelId
	 */
	public function setModelId($modelId)
	{
		$this->modelId = $modelId;
		$this->model = $this->model->findByPk($modelId);
	}

	/**
	 * @return array
	 */
	public function getEditFields()
	{
		return $this->editFields;
	}

	/**
	 * @param array $editFields
	 */
	public function setEditFields($editFields)
	{
		$this->editFields = $editFields;
	}

	/**
	 * @return string
	 */
	public function getEditTemplate()
	{
		return $this->editTemplate;
	}

	/**
	 * @param string $editTemplate
	 */
	public function setEditTemplate($editTemplate)
	{
		$this->editTemplate = $editTemplate;
	}

	/**
	 * @param BaseActiveRecord $model
	 * @param BaseAdminController $controller
	 */
	public function __construct(BaseActiveRecord $model, BaseAdminController $controller)
	{
		$this->setModel($model);
		$this->controller = $controller;
		$this->search = new ModelSearch($this->model);

	}

	/**
	 * Lists all the rows returned from the search in a table
	 *
	 * @throws CHttpException
	 */
	public function listModel()
	{
		if(!$this->model){
			throw new CHttpException(500, 'Nothing to list');
		}

		$this->audit('list');
		$this->pagination = $this->getSearch()->initPagination();
		$this->render($this->listTemplate, array('admin' => $this));
	}

	/**
	 * Edits the model, runs validation and renders the edit form.
	 *
	 * @throws CHttpException
	 * @throws Exception
	 */
	public function editModel()
	{
		$errors = array();
		if(Yii::app()->request->isPostRequest){
			$post = Yii::app()->request->getPost($this->modelName);
			if(array_key_exists('id', $post) && $post['id']){
				$this->model->attributes = $post;
			} else {
				$this->model = new $this->modelName;
				$this->model->attributes = $post;
			}

			if (!$this->model->validate()) {
				$errors = $this->model->getErrors();
			} else {

				if (!$this->model->save()) {
					throw new CHttpException(500, 'Unable to save '.$this->modelName.': ' . print_r($this->model->getErrors(), true));
				}

				$this->audit('edit', $this->model->id);
				$this->controller->redirect('/'.$this->controller->uniqueid.'/list');
			}
		}
		$this->render($this->editTemplate, array('admin' => $this, 'errors' => $errors));
	}

	/**
	 * Deletes the models for which an array of IDs has been posted
	 */
	public function deleteModel()
	{
		$response = 1;
		if(Yii::app()->request->isPostRequest){
			$ids = Yii::app()->request->getPost($this->modelName);
			foreach($ids as $id){
				$model = $this->model->findByPk($id);
				if($model){
					if(!$model->delete()){
						$response = 0;
					}
				}
			}
		}
		echo $response;
	}

	/**
	 * Sets up search on all listed elements
	 */
	public function searchAll()
	{
		$searchArray = array('type' => 'compare', 'compare_to' => array());
		$searchFirst = '';
		foreach($this->listFields as $field){
			if(method_exists($this->model, 'get_'.$field)){
				//we don't currently support searching on magic attributes not from the DB so continue
				continue;
			}
			if($searchFirst === ''){
				$searchFirst = $field;
			} else {
				$searchArray['compare_to'][] = $field;
			}
		}
		$this->search->addSearchItem($searchFirst, $searchArray);
	}

	/**
	 * @param $row
	 * @param $attribute
	 * @return string
	 */
	public function attributeValue($row, $attribute)
	{
		if(isset($row->$attribute)){
			return $row->$attribute;
		}

		if(strpos($attribute, '.')){
			$splitAttribute = explode('.', $attribute);
			$relationTable = $splitAttribute[0];
			if(isset($row->$relationTable->$splitAttribute[1])){
				return $row->$relationTable->$splitAttribute[1];
			}

			if(is_array($row->$relationTable)){
				$manyResult = array();
				foreach($row->$relationTable as $relationResult){
					if(isset($relationResult->$splitAttribute[1])){
						$manyResult[] =  $relationResult->$splitAttribute[1];
					}
				}

				return implode(',', $manyResult);
			}
		}

		return '';
	}

	/**
	 * @param $saveURL
	 */
	public function setCustomSaveURL($saveURL)
	{
		$this->customSaveURL = $saveURL;
	}

	/**
	 * @return string
	 */
	public function getCustomSaveURL()
	{
		return $this->customSaveURL;
	}

	/**
	 * @param $cancelURL
	 */
	public function setCustomCancelURL($cancelURL)
	{
		$this->customCancelURL = $cancelURL;
	}

	/**
	 * @return string
	 */
	public function getCustomCancelURL()
	{
		return $this->customCancelURL;
	}

	/**
	 * @param $template
	 * @param array $data
	 */
	protected function render($template, $data = array())
	{
		$this->controller->render($template, $data);
	}

	/**
	 * @param $type
	 * @throws Exception
	 */
	protected function audit($type, $data = null)
	{
		Audit::add('admin-'.$this->modelName, $type, $data);
	}
}