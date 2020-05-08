<?php
/**
 * OpenEyes.
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.openeyes.org.uk
 *
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/agpl-3.0.html The GNU Affero General Public License V3.0
 */
?>
<div class="element-fields full-width">
    <?php echo $form->dropDownList(
        $element,
        'gas_type_id',
        'OphTrOperationnote_GasType',
        array('empty' => 'Select'),
        false,
        array('field' => 2)
    ) ?>
    <?php echo $form->dropDownList(
        $element,
        'gas_percentage_id',
        CHtml::listData(
            OphTrOperationnote_GasPercentage::model()->findAll(array('order' => 'display_order')),
            'id',
            'value'
        ),
        array('empty' => 'Select'),
        false,
        array('field' => 2)
    ) ?>
    <?php echo $form->dropDownList(
        $element,
        'gas_volume_id',
        CHtml::listData(
            OphTrOperationnote_GasVolume::model()->activeOrPk($element->gas_volume_id)->findAll(array('order' => 'display_order')),
            'id',
            'value'
        ),
        array('empty' => 'Select'),
        false,
        array('field' => 2)
    ) ?>
</div>

