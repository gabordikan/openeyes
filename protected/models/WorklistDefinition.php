<?php

/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2016
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2016, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

use \RRule\RRule;

/**
 * Class WorklistDefinition
 *
 * The followings are the available columns in table:
 * @property integer $id
 * @property string $name
 * @property string $rrule
 * @property string $description
 * @property string $worklist_name
 * @property string $start_time
 * @property string $end_time
 * @property DateTime $active_from
 * @property DateTime $active_until
 *
 * @property Worklist[] $worklists
 * @property WorklistDefinitionMapping[] $mappings
 */
class WorklistDefinition extends BaseActiveRecordVersionedSoftDelete
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'worklist_definition';
    }

    /**
     * Default to ordering by the display order property
     *
     * @return array
     */
    public function defaultScope()
    {
        return array('order' => $this->getTableAlias(true, false) . '.display_order');
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, rrule, worklist_name, start_time, end_time, description', 'safe'),
            array('rrule', 'validateRrule'),
            array('name, rrule', 'required'),
            array('name', 'length', 'max'=>100),
            array('description', 'length', 'max' => 1000),
            array('start_time, end_time', 'OETimeValidator'),
            array('active_from, active_until', 'OEDateValidator'),
            array('active_from', 'OEDateCompareValidator', 'compareAttribute' => 'active_until', 'allowEmpty' => true,
                'allowCompareEmpty' => true, 'operator' => '<=',
                'message' => '{attribute} must be on or before {compareAttribute}'),
            array('active_from', 'default', 'setOnEmpty' => true, 'value' => date("Y-m-d H:i:s", time())),
            array('active_until', 'default', 'setOnEmpty' => true, 'value' => null),
            array('scheduled', 'boolean', 'allowEmpty' => false),
            array('display_order', 'numerical', 'integerOnly' => true),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, rrule, worklist_name, start_time, end_time, description, scheduled', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'worklists' => array(self::HAS_MANY, 'Worklist', 'worklist_definition_id'),
            'mappings' => array(self::HAS_MANY, 'WorklistDefinitionMapping', 'worklist_definition_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'rrule' => 'Frequency'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('worklist_name',$this->worklist_name,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('scheduled', $this->scheduled, true);

        // TODO: proper support for date/time search

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
        ));
    }

    public function afterFind()
    {
        // strip out the seconds from the time field
        foreach (array('start_time', 'end_time') as $time_attr) {
            $this->$time_attr = substr($this->$time_attr,0, 5);
        }
        parent::afterFind();
    }

    /**
     * Simple wrapper around RRule construction to validate the string definition
     *
     * @param $attribute
     */
    public function validateRrule($attribute)
    {
        if (empty($this->$attribute))
            return;

        $valid = true;
        try {
            if (strpos($this->$attribute, '=') === false) {
                // rrule instantiation falls over if no equals is found during parsing
                // so this is a bit of a dirty hack to deal with that.
                $valid = false;
            }
            else {
                $rrule = new RRule($this->$attribute);
            }
        }
        catch (Exception $e)
        {
            $valid = false;
        }
        if (!$valid)
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' is not valid');
    }
}