<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: auth.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

use PHPFusion\Panels;

function display_loginform($info) {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    $userdata = fusion_get_userdata();
    $settings = fusion_get_settings();

    Panels::getInstance(TRUE)->hidePanel('RIGHT');
    Panels::getInstance(TRUE)->hidePanel('LEFT');
    Panels::getInstance(TRUE)->hidePanel('AU_CENTER');
    Panels::getInstance(TRUE)->hidePanel('U_CENTER');
    Panels::getInstance(TRUE)->hidePanel('L_CENTER');
    Panels::getInstance(TRUE)->hidePanel('BL_CENTER');

    echo '<div class="panel panel-default" style="max-width: 500px; margin: 30px auto;">';
        echo '<div class="panel-heading" style="background: #fff;">';
            echo '<h3 class="panel-title"><strong>'.$locale['global_100'].'</strong></h3>';

            if (!iMEMBER) {
                echo '<div class="pull-right" style="margin-top: -20px;">'.$info['forgot_password_link'].'</div>';
            }
        echo '</div>';

        echo '<div class="panel-body">';

            if (iMEMBER) {
                $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");
                echo '<h3 class="text-center">'.$userdata['user_name'].'</h3>';
                echo '<div class="text-center"><br/>';
                echo '<a href="'.BASEDIR.'edit_profile.php" class="side">'.$locale['global_120'].'</a><br/>';
                echo '<a href="'.BASEDIR.'messages.php" class="side">'.$locale['global_121'].'</a><br/>';
                echo '<a href="'.BASEDIR.'members.php" class="side">'.$locale['global_122'].'</a><br/>';

                if (iADMIN && (iUSER_RIGHTS != '' || iUSER_RIGHTS != 'C')) {
                    echo '<a href="'.ADMIN.'index.php'.$aidlink.'" class="side">'.$locale['global_123'].'</a><br/>';
                }

                echo '<a href="'.BASEDIR.'index.php?logout=yes" class="side">'.$locale['global_124'].'</a><br/>';
                if ($msg_count) {
                    echo '<br/><br/>';
                    echo '<strong><a href="'.BASEDIR.'messages.php" class="side">'.sprintf($locale['global_125'], $msg_count);
                    echo ($msg_count == 1 ? $locale['global_126'] : $locale['global_127']).'</a></strong><br/>';
                }

                echo '<a href="'.BASEDIR.$settings['opening_page'].'">'.$locale['home'].'</a>';
                echo '</div>';
            } else {
                echo $info['open_form'];
                echo $info['user_name'];
                echo $info['user_pass'];
                echo $info['remember_me'];
                echo $info['login_button'];
                echo '<div class="display-block text-center m-t-10">'.$info['registration_link'].'</div>';
                echo $info['close_form'];

                if (!empty($info['connect_buttons'])) {
                    echo '<hr/>';

                    foreach ($info['connect_buttons'] as $mhtml) {
                        echo $mhtml;
                    }
                }
            }

        echo '</div>';
    echo '</div>';
}

function display_register_form($info) {
    $locale = fusion_get_locale();

    Panels::getInstance(TRUE)->hidePanel('RIGHT');
    Panels::getInstance(TRUE)->hidePanel('LEFT');
    Panels::getInstance(TRUE)->hidePanel('AU_CENTER');
    Panels::getInstance(TRUE)->hidePanel('U_CENTER');
    Panels::getInstance(TRUE)->hidePanel('L_CENTER');
    Panels::getInstance(TRUE)->hidePanel('BL_CENTER');

    echo '<div class="panel panel-default" style="max-width: 650px; margin: 30px auto;">';
        echo '<div class="panel-heading" style="background: #fff;">';
            echo '<h3 class="panel-title"><strong>'.$locale['global_107'].'</strong></h3>';
        echo '</div>';

        echo '<div class="panel-body">';

            if (empty($info['user_name']) && empty($info['user_field'])) {
                echo '<div class="text-white text-center">'.$locale['uf_108'].'</div>';
            } else {
                echo !empty($info['openform']) ? $info['openform'] : '';
                echo !empty($info['user_name']) ? $info['user_name'] : '';
                echo !empty($info['user_email']) ? $info['user_email'] : '';
                echo !empty($info['user_avatar']) ? $info['user_avatar'] : '';
                echo !empty($info['user_password']) ? $info['user_password'] : '';
                echo !empty($info['user_admin_password']) && iADMIN ? $info['user_admin_password'] : '';

                if (!empty($info['user_field'])) {
                    foreach ($info['user_field'] as $fieldData) {
                        echo !empty($fieldData['title']) ? $fieldData['title'] : '';
                        if (!empty($fieldData['fields']) && is_array($fieldData['fields'])) {
                            foreach ($fieldData['fields'] as $cFieldData) {
                                echo !empty($cFieldData) ? $cFieldData : '';
                            }
                        }
                    }
                }

                echo !empty($info['validate']) ? $info['validate'] : '';
                echo !empty($info['terms']) ? $info['terms'] : '';
                echo !empty($info['button']) ? $info['button'] : '';
                echo !empty($info['closeform']) ? $info['closeform'] : '';
            }

        echo '</div>';
    echo '</div>';
}

function display_lostpassword($content) {
    $locale = fusion_get_locale();

    Panels::getInstance(TRUE)->hidePanel('RIGHT');
    Panels::getInstance(TRUE)->hidePanel('LEFT');
    Panels::getInstance(TRUE)->hidePanel('AU_CENTER');
    Panels::getInstance(TRUE)->hidePanel('U_CENTER');
    Panels::getInstance(TRUE)->hidePanel('L_CENTER');
    Panels::getInstance(TRUE)->hidePanel('BL_CENTER');

    echo '<div class="panel panel-default" style="max-width: 500px; margin: 30px auto;">';
        echo '<div class="panel-heading" style="background: #fff;">';
            echo '<h3 class="panel-title"><strong>'.$locale['400'].'</strong></h3>';
        echo '</div>';

        echo '<div class="panel-body">';
            echo $content;
        echo '</div>';
    echo '</div>';
}

function display_gateway($info) {
    global $locale;

    Panels::getInstance(TRUE)->hidePanel('RIGHT');
    Panels::getInstance(TRUE)->hidePanel('LEFT');
    Panels::getInstance(TRUE)->hidePanel('AU_CENTER');
    Panels::getInstance(TRUE)->hidePanel('U_CENTER');
    Panels::getInstance(TRUE)->hidePanel('L_CENTER');
    Panels::getInstance(TRUE)->hidePanel('BL_CENTER');

    echo '<div style="max-width: 500px; margin: 30px auto;">';
    if ($info['showform'] == TRUE) {
        opentable($locale['gateway_069']);
        echo $info['openform'];
        echo $info['hiddeninput'];
        echo '<h3>'.$info['gateway_question'].'</h3>';
        echo $info['textinput'];
        echo $info['button'];
        echo $info['closeform'];
        closetable();
    } else if (!isset($_SESSION['validated'])) {
        echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_068'].'</h3></div>';
    }

    if (isset($info['incorrect_answer']) && $info['incorrect_answer'] == TRUE) {
        opentable($locale['gateway_069']);
        echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_066'].'</h3></div>';
        echo '<input type="button" value="'.$locale['gateway_067'].'" class="text-center btn btn-info spacer-xs" onclick="location=\''.BASEDIR.'register.php\'"/>';
        closetable();
    }
    echo '</div>';
}
