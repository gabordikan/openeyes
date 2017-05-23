<?php
/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2017
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2017, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

?>

<?php
if (!isset($values)) {
    $values = array(
        'id' => $op->id,
        'previous_operation_id' => $op->previous_operation_id,
        'operation' => $op->operation,
        'side_id' => $op->side_id,
        'side_display' => $op->side ? $op->side->adjective : '',
        'date' => $op->date,
        'date_display' => $op->getDisplayDate()
    );
}

?>
<tr>
    <td>
        <input type="hidden" name="<?= $model_name ?>[id][]" value="<?=$values['id'] ?>" />
        <input type="hidden" name="<?= $model_name ?>[previous_operation_id][]" value="<?=$values['previous_operation_id'] ?>" />
        <input type="hidden" name="<?= $model_name ?>[operation][]" value="<?=$values['operation'] ?>" />
        <?= $values['operation'] ?>
    </td>
    <td>
        <input type="hidden" name="<?= $model_name ?>[side_id][]" value="<?=$values['side_id'] ?>" />
        <?= $values['side_display'] ?>
    </td>
    <td>
        <input type="hidden" name="<?= $model_name ?>[date][]" value="<?=$values['date'] ?>" />
        <?= $values['date_display'] ?>
    </td>
    <td>
        <button class="button small warning remove">remove</button>
    </td>
</tr>
