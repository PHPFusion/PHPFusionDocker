<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: email.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('MAIL');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/emails.php');

add_breadcrumb(['link' => ADMIN.'email.php'.fusion_get_aidlink(), 'title' => $locale['MAIL_000']]);

require_once INCLUDES."html_buttons_include.php";

if (check_post('save_template')) {
    $data = [
        'template_id'           => sanitizer('template_id', 0, 'template_id'),
        'template_key'          => sanitizer('template_key', '', 'template_key'),
        'template_format'       => sanitizer('template_format', '', 'template_format'),
        'template_subject'      => sanitizer('template_subject', '', 'template_subject'),
        'template_content'      => sanitizer('template_content', '', 'template_content'),
        'template_active'       => sanitizer('template_active', 0, 'template_active'),
        'template_sender_name'  => sanitizer('template_sender_name', '', 'template_sender_name'),
        'template_sender_email' => sanitizer('template_sender_email', '', 'template_sender_email'),
    ];
    if (fusion_safe()) {
        dbquery_insert(DB_EMAIL_TEMPLATES, $data, "update");
        addnotice('success', $locale['MAIL_001']);
        redirect(FUSION_SELF.fusion_get_aidlink()."&template_id=".$data['template_id']);
    }
} else if (check_post('test_template')) {
    $data = [
        'template_id'           => sanitizer('template_id', 0, 'template_id'),
        'template_key'          => sanitizer('template_key', '', 'template_key'),
        'template_format'       => sanitizer('template_format', '', 'template_format'),
        'template_subject'      => sanitizer('template_subject', '', 'template_subject'),
        'template_content'      => sanitizer('template_content', '', 'template_content'),
        'template_active'       => sanitizer('template_active', 0, 'template_active'),
        'template_sender_name'  => sanitizer('template_sender_name', '', 'template_sender_name'),
        'template_sender_email' => sanitizer('template_sender_email', '', 'template_sender_email'),
    ];
    if (fusion_safe()) {
        require_once INCLUDES.'sendmail_include.php';
        dbquery_insert(DB_EMAIL_TEMPLATES, $data, 'update');
        sendemail_template($data['template_key'], $locale['MAIL_002'], $locale['MAIL_003'], $locale['MAIL_004'], $locale['MAIL_005'], $locale['MAIL_006'], fusion_get_userdata('user_email'), $data['template_sender_name'], $data['template_sender_email']);
        addnotice('success', sprintf($locale['MAIL_007'], fusion_get_userdata('user_email')));
        redirect(FUSION_SELF.fusion_get_aidlink()."&template_id=".$data['template_id']);
    }
}
$result = dbquery("SELECT template_id, template_key, template_name, template_language
    FROM ".DB_EMAIL_TEMPLATES."
    ".(multilang_table("ET") ? "WHERE template_language='".LANGUAGE."'" : "")."
    ORDER BY template_id ASC
");
$template = [];
if (dbrows($result) != 0) {
    while ($data = dbarray($result)) {
        $template[$data['template_id']] = $data['template_name'];
    }
}

$tabs = [];
foreach ($template as $id => $tname) {
    $tabs['title'][$id] = $tname;
    $tabs['id'][$id] = $id;
    $tabs['icon'][$id] = '';
}

$tab_values = array_values($tabs['id']);
$sections = in_array(get('section'), $tab_values) ? get('section') : $tab_values[0];

opentable($locale['MAIL_000']);

echo opentab($tabs, $sections, 'email-templates-tab', TRUE);
echo opentabbody($tabs['title'][$sections], $tabs['id'][$sections], $sections, TRUE);
$result = dbquery("SELECT * FROM ".DB_EMAIL_TEMPLATES." WHERE template_id=:templateid LIMIT 1", [':templateid' => $sections]);
$html_text = "";
$html_helper = "";
$text_helper = "";

$data = [];

if (dbrows($result)) {
    $data = dbarray($result);

    if ($data['template_active']) {
        if ($data['template_format'] == "html") {
            $text_helper = " style='display:none'";
            $html_text = $locale['MAIL_008'];
        } else {
            $html_helper = " style='display:none'";
            $html_text = $locale['MAIL_009'];
        }
    } else {
        if ($data['template_format'] == "html") {
            $html_helper = " style='display:none'";
            $html_text = $locale['MAIL_008'];
        } else {
            $html_text = $locale['MAIL_009'];
        }
    }
}

echo openform('emailtemplateform', 'post', FUSION_SELF.fusion_get_aidlink());
echo form_hidden('template_id', '', $data['template_id']);
echo form_hidden('template_key', '', $data['template_key']);

openside('');
echo '<div class="row">';
echo '<div class="col-xs-12 col-sm-6">';
echo form_select('template_active', $locale['MAIL_010'], $data['template_active'], [
    'options'     => [$locale['disable'], $locale['enable']],
    'placeholder' => $locale['choose'],
    'inline'      => TRUE
]);
echo form_select('template_format', $locale['MAIL_013'], $data['template_format'], [
    'options' => [
        'html'  => $locale['MAIL_008'],
        'plain' => $locale['MAIL_009']
    ],
    'inline'  => TRUE
]);
echo form_text('template_sender_name', $data['template_key'] == "CONTACT" ? $locale['MAIL_015'] : $locale['MAIL_016'], $data['template_sender_name'], [
    'required'   => TRUE,
    'error_text' => $locale['MAIL_017'],
    'inline'     => TRUE,
    'ext_tip'    => ($data['template_key'] == "CONTACT" ? '' : $locale['MAIL_018'])
]);
echo form_text('template_sender_email', $data['template_key'] == "CONTACT" ? $locale['MAIL_019'] : $locale['MAIL_020'], $data['template_sender_email'], [
    'required'   => TRUE,
    'error_text' => $locale['MAIL_021'],
    'inline'     => TRUE
]);
echo '</div>';

echo '<div class="col-xs-12 col-sm-6">';
echo '<div class="m-b-15">';
echo "<div id='active_info'".$html_helper.">".sprintf($locale['MAIL_011'], $html_text)."</div>\n";
echo "<div id='inactive_info'".$text_helper.">".$locale['MAIL_012']."</div>\n";
echo "<div id='html_info' class='m-t-10'>".$locale['MAIL_014']."</div>\n";
echo '</div>';

echo form_button('save_template', $locale['save'], $locale['save'], ['class' => 'btn-primary']);
echo form_button('test_template', $locale['MAIL_023'], $locale['MAIL_023'], ['class' => 'btn-default']);
echo form_button('reset', $locale['MAIL_024'], $locale['MAIL_024'], ['class' => 'btn-default']);
echo '</div>';
echo '</div>';
closeside();

openside('');
echo form_text('template_subject', $locale['MAIL_025'], $data['template_subject'], [
    'required'   => TRUE,
    'error_text' => $locale['MAIL_026'],
    'autosize'   => TRUE
]);
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_subject', '[SITENAME]', 'emailtemplateform');\">".$locale['MAIL_027']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_subject', '[SITEURL]', 'emailtemplateform');\">".$locale['MAIL_028']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_subject', '[SUBJECT]', 'emailtemplateform');\">".$locale['MAIL_025']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_subject', '[USER]', 'emailtemplateform');\">".$locale['user']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_subject', '[SENDER]', 'emailtemplateform');\">".$locale['MAIL_029']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_subject', '[RECEIVER]', 'emailtemplateform');\">".$locale['MAIL_030']."</button>\n";
echo "</div>\n";

echo "<div class='m-t-20 m-b-20'>\n";
echo "<a class='pointer' data-target='#email_tutorial' data-toggle='collapse' aria-expanded='false' aria-controls='email_tutorial'>".$locale['MAIL_031']."</a>";
echo "</div>\n";
echo "<div id='email_tutorial' class='collapse'>\n";
echo "<div class='table-responsive'><table class='table'>\n";
echo "<tr>\n";
echo "<th>".$locale['MAIL_032']."</th>\n";
echo "<th>".$locale['MAIL_033']."</th>\n";
echo "</tr>\n";
echo "<tbody>\n";
echo "<tr>\n";
echo "<td>[SITENAME]</td>\n";
echo "<td>".fusion_get_settings('sitename')."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SITEURL]</td>\n";
echo "<td>".fusion_get_settings('siteurl')."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SUBJECT]</td>\n";
echo "<td>".$locale['MAIL_034']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[MESSAGE]</td>\n";
echo "<td>".$locale['MAIL_035']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[USER]</td>\n";
echo "<td>".$locale['MAIL_036']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[SENDER]</td>\n";
echo "<td>".$locale['MAIL_037']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[RECEIVER]</td>\n";
echo "<td>".$locale['MAIL_038']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td>[THREAD_URL]</td>\n";
echo "<td>".$locale['MAIL_039']."</td>\n";
echo "</tr>\n";
echo "</tbody>\n</table>\n</div>";
echo "</div>\n";

$content = $data['template_format'] == 'html' ? $data['template_content'] : strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", htmlspecialchars_decode($data['template_content'])));
echo form_textarea('template_content', $locale['MAIL_040'], $content, [
    'required'   => TRUE,
    'error_text' => $locale['MAIL_041'],
    'autosize'   => TRUE,
    'preview'    => $data['template_format'] == 'html',
    'html'       => $data['template_format'] == 'html',
    'inputform'  => 'emailtemplateform'
]);
echo "<div class='btn-group'>\n";
echo "<button type='button' class='btn btn-default button' value='[SUBJECT]' onclick=\"insertText('template_content', '[SUBJECT]', 'emailtemplateform');\">".$locale['MAIL_025']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[MESSAGE]' onclick=\"insertText('template_content', '[MESSAGE]', 'emailtemplateform');\">".$locale['MAIL_040']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SENDER]' onclick=\"insertText('template_content', '[SENDER]', 'emailtemplateform');\">".$locale['MAIL_029']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[RECEIVER]' onclick=\"insertText('template_content', '[RECEIVER]', 'emailtemplateform');\">".$locale['MAIL_030']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[USER]' onclick=\"insertText('template_content', '[USER]', 'emailtemplateform');\">".$locale['user']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITENAME]' onclick=\"insertText('template_content', '[SITENAME]', 'emailtemplateform');\">".$locale['MAIL_027']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[SITEURL]' onclick=\"insertText('template_content', '[SITEURL]', 'emailtemplateform');\">".$locale['MAIL_028']."</button>\n";
echo "<button type='button' class='btn btn-default button' value='[THREAD_URL]' onclick=\"insertText('template_content', '[THREAD_URL]', 'emailtemplateform');\">".$locale['MAIL_042']."</button>\n";

echo "<div id='html_buttons' class='m-l-5 display-inline-block'".($data['template_format'] == "plain" ? " style='display:none'" : '').">\n";
echo "<button type='button' class='btn btn-default button m-r-5' value='".$locale['MAIL_043']."' onMousedown=\"this.form.template_content.focus();this.form.template_content.select();\" onmouseup=\"addText('template_content', '&lt;body style=\'background-color:#D7F9D7;\'&gt;', '&lt;/body&gt;', 'emailtemplateform');\">\n".$locale['MAIL_043']."</button>\n";

$folder = BASEDIR."images/";
$image_files = makefilelist($folder, ".|..|index.php");
$opts = [];
foreach ($image_files as $image) {
    $opts[$image] = $image;
}
echo form_select('insertimage', '', '', [
    'options'     => $opts,
    'placeholder' => $locale['MAIL_044'],
    'allowclear'  => TRUE,
    'width'       => '200px',
    'class'       => 'm-b-0 display-inline-block'
]);
echo "</div>\n";

echo "</div>\n";

closeside();
echo form_button('save_template', $locale['save'], $locale['save'], ['class' => 'btn-primary', 'input_id' => 'save_template2']);
echo form_button('test_template', $locale['MAIL_023'], $locale['MAIL_023'], ['class' => 'btn-default', 'input_id' => 'test_template2']);
echo form_button('reset', $locale['MAIL_024'], $locale['MAIL_024'], ['class' => 'btn-default', 'input_id' => 'reset2']);

echo closeform();
echo closetabbody();
echo closetab();

closetable();

add_to_jquery("
$('#template_active').bind('change', function () {
    var value = $(this).select2().val();
    if (value == 1) {
        $('#active_info').show();
        $('#inactive_info').hide();
    } else {
        $('#active_info').hide();
        $('#inactive_info').show();
    }
});
$('#template_format').bind('change', function () {
    var value = $(this).select2().val();
    if (value == 'plain') {
        $('#html_info').show();
        $('#html_buttons').hide();
    } else {
        $('#html_info').hide();
        $('#html_buttons').show();
    }
});
$('#insertimage').on('change', function (e) {
    insertText('template_content', '<img src=\"".fusion_get_settings('siteurl')."images/' + this.options[this.selectedIndex].value + '\" alt=\"\" style=\"margin:5px;\" align=\"left\" />', 'emailtemplateform');
    this.selectedIndex = 0;
    $(this).select2({
        formatSelection: color,
        escapeMarkup: function (m) {return m;},
        formatResult: color,
        placeholder: '".$locale['MAIL_044']."',
        allowClear: true
    }).val('');
});
");
require_once THEMES.'templates/footer.php';
