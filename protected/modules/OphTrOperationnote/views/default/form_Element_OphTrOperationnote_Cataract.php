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

<?php
$layoutColumns = $form->layoutColumns;
$form->layoutColumns = array('label' => 3, 'field' => 9);
?>
<div class="element-fields js-side" data-side="<?=$element->eye?>">
    <div class="row eyedraw-row cataract" data-is-new="<?= $element->isNewRecord ? 'true' : 'false' ?>">
        <div class="fixed column">
            <?php $this->renderPartial($element->form_view . '_OEEyeDraw', array(
                'element' => $element,
                'form' => $form,
            )); ?>
        </div>
        <div class="fluid column">
            <?php $this->renderPartial($element->form_view . '_OEEyeDraw_fields', array(
                'form' => $form,
                'element' => $element,
            )); ?>
        </div>
    </div>
    <span id="ophCiExaminationPCRRiskEyeLabel">
        <a href="javascript:showhidePCR('ophTrOperationnotePCRRiskDiv')">PCR Risk
        <span class="pcr-span1"></span>%</a>
    </span>

    <div id="ophTrOperationnotePCRRiskDiv">
        <div id="ophCiExaminationPCRRiskLeftEye" class="pcr-exam-link-opnote js-pcr-left">
            <?php
            $this->renderPartial('application.views.default._pcr_risk_form',
                array('form' => $form, 'element' => $element, 'side' => 'left'));
            ?>
        </div>
        <div id="ophCiExaminationPCRRiskRightEye" class="pcr-exam-link-opnote js-pcr-right">
            <?php
            $this->renderPartial('application.views.default._pcr_risk_form',
                array('form' => $form, 'element' => $element, 'side' => 'right'));
            ?>
        </div>
    </div>
</div>


<?php $form->layoutColumns = $layoutColumns; ?>
