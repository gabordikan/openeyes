<?php

class m190528_105000_change_CXL_slit_element_type_to_the_right_element_group extends CDbMigration
{
    public function up()
    {
        $examination_event_type = $this->dbConnection->createCommand('SELECT id FROM event_type WHERE name = :name')
            ->bindValues(array(':name' => 'Examination'))
            ->queryScalar();

        $element_group = $this->dbConnection->createCommand('SELECT id FROM element_group WHERE name = :name AND event_type_id = :event_type')
            ->bindValues(array(':name' => 'Anterior Segment', ':event_type' => $examination_event_type));
        $this->update(
            'element_type',
            ['element_group_id' => $element_group],
            'class_name = :class_name',
            [':class_name' => 'OEModule\OphCiExamination\models\Element_OphCiExamination_Slit_Lamp']
        );
    }

    public function down()
    {
        echo "does not support migration down.\n";
        return false;
    }
}
