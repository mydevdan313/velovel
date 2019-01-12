<?php
    switch($data['status']){
        case 'success':
            echo '<div class="c-alert c-alert--green">'.$data['message'].'</div>';
            break;

        case 'fail':
            echo '<div class="c-alert c-alert--red">'.$data['message'].'</div>';
            break;

        case 'result':
            echo '<div class="c-alert c-alert--blue">'.$data['message'].'</div>';
    }
?>