<?php
/* @var $this PatientMergeRequestController */
/* @var $dataProvider CActiveDataProvider */

?>

<h1 class="badge">Patient Merge Request List</h1>

<?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'merge-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    //'focus' => array($model,'firstName'),
    
)); ?>


<div id="patientMergeWrapper" class="container content">
    
    <?php $this->renderPartial('//base/_messages')?>

    <form id="patientMergeList">
        <div class="row">
            <div class="large-8 column large-centered">
                <section class="box generic requestList js-toggle-container">
                    <h2>Merge Requests</h2>
                    <div class="filter panel">
                        <div class="row">
                            <div class="large-10 column">
                                <label>
                                    <input type="hidden" id="PatientMergeRequest_show_merged_hidden" name="PatientMergeRequestFilter[show_merged]" value="0">
                                    <input type="checkbox" id="PatientMergeRequest_show_merged" name="PatientMergeRequestFilter[show_merged]" value="1" <?php echo ($filters['show_merged'] && $filters['show_merged'] == 1 ? 'checked' : '') ?> > Show merged
                                </label>
                            </div>
                            <div class="large-2 column text-right">
                                <img class="loader filter" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif') ?>" alt="loading..." style="margin-right: 10px; display: none;"/>
                                
                                <button class="secondary small filter" type="button">Filter</button></div>
                        </div>
                    </div>
                    <div class="grid-view" id="inbox-table">
                        <?php $this->renderPartial('//patientmergerequest/_list',array('dataProvider' => $dataProvider))?>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
        
<?php $this->endWidget(); ?>


