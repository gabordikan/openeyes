<?php

class m190411_153415_add_system_setting_for_default_iris_colour extends CDbMigration
{
	public function up()
	{
    $this->insert('setting_metadata', [
      'field_type_id' => 2,
      'key' => 'OphCiExamination_default_iris_colour',
      'name' => 'Examination Default Iris Colour',
      'default_value' => 'Blue',
    ]);
    $this->insert('setting_installation', [
      'key' => 'OphCiExamination_default_iris_colour',
      'value' => 'Blue',
      ]);
	}

	public function down()
	{
    $this->delete('setting_installation', '`key`="OphCiExamination_default_iris_colour"');
    $this->delete('setting_metadata', '`key`="OphCiExamination_default_iris_colour"');
	}
}