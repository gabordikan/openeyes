<img src="/file/view/<?php echo $element->{$index}->id ?>/image<?php echo strrchr($element->{$index}->name, '.') ?>"
     border="0" style="max-width: 100%; <?=!empty($element->{$index}->rotate) ? 'transform: rotate('.$element->{$index}->rotate.'deg)' : ''?>">