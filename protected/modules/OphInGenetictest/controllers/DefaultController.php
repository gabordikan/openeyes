<?php

class DefaultController extends BaseEventTypeController
{
	protected function beforeAction($action)
	{
		return parent::beforeAction($action);
	}

	public function actionCreate()
	{
		parent::actionCreate();
	}

	public function actionUpdate($id)
	{
		parent::actionUpdate($id);
	}

	public function actionView($id)
	{
		parent::actionView($id);
	}

	public function actionPrint($id)
	{
		parent::actionPrint($id);
	}
}
