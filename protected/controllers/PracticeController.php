<?php

class PracticeController extends BaseController
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array(
                'allow',
                // allow users with either the TaskViewPractice or TaskCreatePractice roles to view Practice data
                'actions' => array('index', 'view'),
                'roles' => array('TaskViewPractice', 'TaskCreatePractice'),
            ),
            array(
                'allow',
                // allow users with either the TaskCreatePractice or TaskAddPatient roles to perform 'create' actions
                'actions' => array('create','createAssociate'),
                'roles' => array('TaskCreatePractice', 'TaskAddPatient'),
            ),
            array(
                'allow', // allow users with the TaskCreatePractice role to perform 'update' actions
                'actions' => array('update'),
                'roles' => array('TaskCreatePractice'),
            ),
            array(
                'deny',  // deny all other users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $context string The context through which create action is invoked. Is either 'AJAX' or null.
     */
    public function actionCreate($context = null)
    {
        $duplicateCheckOutput = null;
        $contact = new Contact('manage_practice');
        $address = new Address('manage_practice');
        $practice = new Practice('manage_practice');

        $gp = new Gp('manage_practice');

        $gpIdList = array();
        if (isset($_POST['Gp'])) {
            foreach ($_POST['Gp'] as $gpId) {
                $gpIdList = $gpId;
            }
        }

        $this->performAjaxValidation(array($practice, $contact, $address, $gp));
        if (isset($_POST['Contact'])) {
            $contact->first_name = $_POST['Contact']['first_name'];
            $address->attributes = $_POST['Address'];
            $practice->attributes = $_POST['Practice'];

            if ( $contact->validate(array('first_name')) and $address->validate(array('address1', 'city', 'postcode', 'country'))) {

                // If there is no validation error, check for the duplicate practice based on practice name, address1, city, postcode and country.
                $duplicateCheckOutput = Yii::app()->db->createCommand()
                    ->select('c1.first_name, a.address1, a.city, a.postcode, a.country_id')
                    ->from('practice p')
                    ->join('contact c1', 'c1.id = p.contact_id')
                    ->join('address a', 'a.contact_id = c1.id')
                    ->where('LOWER(c1.first_name) = LOWER(:first_name) and LOWER(a.address1) = LOWER(:address1) and LOWER(a.city) = LOWER(:city) and a.postcode = :postcode and a.country_id = :country_id',
                        array(':first_name'=> $contact->first_name, ':address1'=>$address->address1,
                            ':city'=>$address->city, ':postcode'=>$address->postcode, ':country_id'=>$address->country_id))
                    ->queryAll();

                $isDuplicate = count($duplicateCheckOutput);

                if($isDuplicate === 0) {
                    list($contact, $practice, $address) = $this->performPracticeSave($contact, $practice, $address, $gpIdList,
                    false);
                }
            } else {
                $contact->validate(array('first_name'));
                $address->validate(array('address1', 'city', 'postcode', 'country'));
            }
        }

        $this->render('create', array(
            'model' => $practice,
            'address' => $address,
            'contact' => $contact,
            'gp' => $gp,
            'gpIdList' => $gpIdList,
            'duplicateCheckOutput' => $duplicateCheckOutput,
        ));
    }

    /**
     * This function is called at the final step of Add New Contact/Referring Practitioner when user wants to add new
     * practice (It uses Contact, Gp, Practice, Address and Contact Practice Associate models)
     * @throws CException
     * @throws Exception
     */
    public function actionCreateAssociate(){
        if (isset($_POST['Contact'], $_POST['gp_data_retrieved'])) {

            $contactPractice = new Contact('manage_practice');
            $address = new Address('manage_practice');
            $practice = new Practice('manage_practice');

            $contactPractice->first_name = $_POST['Contact']['first_name'];
            $contactPractice->primary_phone = $_POST['Contact']['primary_phone'];
            $address->attributes = $_POST['Address'];
            $practice->attributes = $_POST['Practice'];

            if ($contactPractice->validate(array('first_name')) and $address->validate(array('address1', 'city', 'postcode', 'country'))) {

                $practice_contact_associate = new ContactPracticeAssociate();
                $practice_contact_associate->provider_no = !empty($_POST['ContactPracticeAssociate']['provider_no']) ? $_POST['ContactPracticeAssociate']['provider_no'] : null;

                $gp = new Gp();
                $contact = new Contact('manage_gp');

                $contact->title = $_POST['Contact']['contact_title'];
                $contact->first_name = $_POST['Contact']['contact_first_name'];
                $contact->last_name = $_POST['Contact']['contact_last_name'];
                $contact->primary_phone = $_POST['Contact']['contact_primary_phone'];
                $contact->contact_label_id = $_POST['Contact']['contact_label_id'];

                // If there is no validation error, check for the duplicate practice based on practice name, address1, city, postcode and country.
                $dataProvider = Yii::app()->db->createCommand()
                    ->select('c1.first_name, a.address1, a.city, a.postcode, a.country_id')
                    ->from('practice p')
                    ->join('contact c1', 'c1.id = p.contact_id')
                    ->join('address a', 'a.contact_id = c1.id')
                    ->where('LOWER(c1.first_name) = LOWER(:first_name) and LOWER(a.address1) = LOWER(:address1) and LOWER(a.city) = LOWER(:city) and LOWER(a.postcode) = LOWER(:postcode) and LOWER(a.country_id) = LOWER(:country_id)',
                        array(':first_name'=> $contactPractice->first_name, ':address1'=>$address->address1,
                            ':city'=>$address->city, ':postcode'=>$address->postcode, ':country_id'=>$address->country_id))
                    ->queryAll();

                $isDuplicate = count($dataProvider);

                if($isDuplicate === 0) {
                    // This variable stores the gp details that were entered in the pop-up (in first step)
                    $gpDetails = json_decode($_POST['gp_data_retrieved']);
                    if ($gpDetails->gpId != "-1") {
                        $practice_contact_associate->gp_id = $gpDetails->gpId;
                    } else {
                        // User has selected one of the search results but modified the text in one of the fields.
                        list($contact, $gp) = $this->performGpSave($contact, $gp, true);
                        $practice_contact_associate->gp_id = $gp->getPrimaryKey();
                    }
                } else {
                    echo CJSON::encode(array('error' => 'Duplicate Practice detected. <br/> A practice with the same practice name and address already exists. Please enter another practice or exit.'));
                    Yii::app()->end();
                }

                list($contactPractice, $practice, $address) = $this->performPracticeSave($contactPractice, $practice, $address,
                    true);

                $practice_contact_associate->practice_id = $practice->id;

                if ($practice_contact_associate->save()) {
                    echo CJSON::encode(array(
                        'gp_id' => $practice_contact_associate->gp->getPrimaryKey(),
                        'practice_id' => $practice_contact_associate->practice_id,
                    ));
                }
            } else {
                echo CJSON::encode(array('error' =>  CHtml::errorSummary(array($contactPractice, $address) ) ));
            }
        }
    }

    public function performGpSave(Contact $contact, Gp $gp, $isAjax = false)
    {
        $action = $gp->isNewRecord ? 'add' : 'edit';
        $transaction = Yii::app()->db->beginTransaction();

        try {
            if ($contact->save()) {
                // No need to re-set these values if they already exist.
                if ($gp->contact_id === null) {
                    $gp->contact_id = $contact->getPrimaryKey();
                }

                if ($gp->nat_id === null) {
                    $gp->nat_id = 0;
                }

                if ($gp->obj_prof === null) {
                    $gp->obj_prof = 0;
                }

                if ($gp->save()) {
                    $transaction->commit();
                    Audit::add('Gp', $action . '-gp', "Practitioner manually [id: $gp->id] {$action}ed.");
                    if (!$isAjax) {
                        $this->redirect(array('view','id'=>$gp->id));
                    }
                } else {
                    if ($isAjax) {
                        throw new CHttpException(400,"Unable to save Practitioner contact");
                    }
                    $transaction->rollback();
                }
            } else {
                if ($isAjax) {
                    throw new CHttpException(400,CHtml::errorSummary($contact));
                }
                $transaction->rollback();
            }
        } catch (Exception $ex) {
            OELog::logException($ex);
            $transaction->rollback();
            if ($isAjax) {
                if (strpos($ex->getMessage(),'errorSummary')){
                    echo $ex->getMessage();
                }else{
                    echo "<div class=\"errorSummary\"><p>Unable to save Practitioner information, please contact your support.</p></div>";
                }
            }
        }
        return array($contact, $gp);
    }

    /**
     * @param Contact $contact
     * @param Practice $practice
     * @param Address $address
     * @param bool $isAjax
     * @return array
     * @throws CException
     */
    public function performPracticeSave(Contact $contact, Practice $practice, Address $address, $gpIdList,$isAjax = false)
    {
        $action = $practice->isNewRecord ? 'add' : 'edit';
        $transaction = Yii::app()->db->beginTransaction();
        try {
            if ($contact->save()) {
                $practice->contact_id = $contact->getPrimaryKey();
                $address->contact_id = $contact->id;
                if ($practice->save()) {
                    if (isset($address)) {
                        if ($address->save()) {
                            $transaction->commit();
                            Audit::add('Practice', $action . '-practice',
                                "Practice manually [id: $practice->id] {$action}ed.");
                            if (!$isAjax) {
                                $isLastAssociate = false;
                                for ($i = 0; $i < count($gpIdList); $i++) {
                                    $practice_contact_associate = new ContactPracticeAssociate();
                                    $practice_contact_associate->gp_id = $gpIdList[$i];
                                    $practice_contact_associate->practice_id = $practice->id;
                                    $practice_contact_associate->save();
                                    if ($i == (count($gpIdList)-1)) {
                                        $isLastAssociate = true;
                                    }
                                    if($isLastAssociate) {
                                        $this->redirect(array('view', 'id' => $practice->id));
                                    }

                                }
                            }
                        } else {
                            $address->validate();
                            $address->clearErrors('contact_id');
                            if ($isAjax) {
                                throw new CHttpException(400, CHtml::errorSummary($address));
                            }
                            $transaction->rollback();
                        }
                    } else {
                        $transaction->commit();
                        Audit::add('Practice', $action . '-practice',
                            "Practice manually [id: $practice->id] {$action}ed.");
                        if (!$isAjax) {
                            $this->redirect(array('view', 'id' => $practice->id));
                        }
                    }
                } else {
                    $address->validate();
                    $address->clearErrors('contact_id');
                    $practice->clearErrors('contact_id');
                    if ($isAjax) {
                        throw new CHttpException(400,CHtml::errorSummary(array($practice,$address)) );
                    }
                    $transaction->rollback();
                }
            } else {
                $address->validate();
                $address->clearErrors('contact_id');
                if ($isAjax) {
                    throw new CHttpException(400,CHtml::errorSummary(array($contact,$address)));
                }
                $transaction->rollback();
            }
        } catch (Exception $ex) {
            OELog::logException($ex);
            $transaction->rollback();
            if ($isAjax) {
                echo $ex->getMessage();
            }
        }

        return array($contact, $practice, $address);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $duplicateCheckOutput = null;
        $model = $this->loadModel($id);
        $contact = $model->contact;
        $address = isset($contact->address) ? $contact->address : new Address();
        $contact->setScenario('manage_practice');
        $address->setScenario('manage_practice');
        $model->setScenario('manage_practice');

        $this->performAjaxValidation($contact);

        if (isset($_POST['Address']) || isset($_POST['Contact'])) {

            $contact->attributes = $_POST['Contact'];
            $address->attributes = $_POST['Address'];
            $model->attributes = $_POST['Practice'];

            // If there is no validation error, check for the duplicate practice based on practice name, address1, city, postcode and country.
            $duplicateCheckOutput = Yii::app()->db->createCommand()
                ->select('c1.first_name, a.address1, a.city, a.postcode, a.country_id')
                ->from('practice p')
                ->join('contact c1', 'c1.id = p.contact_id')
                ->join('address a', 'a.contact_id = c1.id')
                ->where('LOWER(c1.first_name) = LOWER(:first_name) and LOWER(a.address1) = LOWER(:address1) and LOWER(a.city) = LOWER(:city) and a.postcode = :postcode and a.country_id = :country_id and p.id != :id',
                    array(':first_name'=> $contact->first_name, ':address1'=>$address->address1,
                        ':city'=>$address->city, ':postcode'=>$address->postcode, ':country_id'=>$address->country_id, ':id'=>$id ))
                ->queryAll();

            $isDuplicate = count($duplicateCheckOutput);

            if($isDuplicate === 0) {
                list($contact, $model, $address) = $this->performPracticeSave($contact, $model, $address);
            }

        }

        $this->render('update', array(
            'model' => $model,
            'address' => $address,
            'contact' => $contact,
            'duplicateCheckOutput' => $duplicateCheckOutput,
        ));
    }

    /**
     * Lists all models.
     *
     * @param string $search_term
     */
    public function actionIndex($search_term = null)
    {
        $criteria = new CDbCriteria();
        $criteria->together = true;
        $criteria->with = array('contact');
        $criteria->order = 'first_name';

        if ($search_term !== null) {
            $search_term = strtolower($search_term);
            $criteria->addSearchCondition('LOWER(last_name)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(first_name)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(phone)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(code)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(address1)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(address2)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(city)', $search_term, true, 'OR');
            $criteria->addSearchCondition('LOWER(postcode)', $search_term, true, 'OR');

        }
        $dataProvider = new CActiveDataProvider('Practice', array(
            'criteria' => $criteria,
        ));
        $this->render('index', array(
            'dataProvider' => $dataProvider,
            'search_term' => $search_term,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Practice|CActiveRecord the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Practice::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }

    /**
     * Performs the AJAX validation.
     *
     * @param CModel|array $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'practice-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
