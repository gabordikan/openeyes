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
<div class="element-fields element-eyes data-group">
    <?php echo $form->hiddenInput($element, 'eye_id', false, array('class' => 'sideField')); ?>


    <?php foreach (array('left' => 'right', 'right' => 'left') as $page_side => $eye_side) : ?>
        <div class="<?=$eye_side?>-eye <?=!$element->hasEye($eye_side) ? 'inactive':''; ?>"
             data-side="<?= $eye_side ?>">
            <div class="active-form field-row flex-layout">
                <?php $this->renderPartial('form_Element_OphInBiometry_BiometryData_fields', array(
                    'side' => $eye_side,
                    'element' => $element,
                    'form' => $form,
                    'data' => $data,
                )) ?>
            </div>
            <div class="inactive-form">
                <div class="add-side">
                    Set <?=$eye_side?> side lens type
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // Needs refactoring after R2
        if ($('section.Element_OphInBiometry_Measurement').find('.element-eye.right-eye').hasClass('inactive')) {
            $('section.Element_OphInBiometry_BiometryData').find('.element-eye.right-eye').find('.active-form').hide();
            $('section.Element_OphInBiometry_BiometryData').find('.element-eye.right-eye').find('.inactive-form').show();
            $('section.Element_OphInBiometry_BiometryData').find('.sideField').val(1);
        } else if ($('section.Element_OphInBiometry_Measurement').find('.element-eye.left-eye').hasClass('inactive')) {
            $('section.Element_OphInBiometry_BiometryData').find('.element-eye.left-eye').find('.active-form').hide();
            $('section.Element_OphInBiometry_BiometryData').find('.element-eye.left-eye').find('.inactive-form').show();
            $('section.Element_OphInBiometry_BiometryData').find('.sideField').val(2);
        } else {
            $('section.Element_OphInBiometry_BiometryData').find('.sideField').val(3);
        }
    });
</script>
