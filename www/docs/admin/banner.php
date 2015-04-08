<?php

include_once '../../includes/easyparliament/init.php';

$this_page = 'admin_banner';

$db = new ParlDB;

$scriptpath = '../../../scripts';

$PAGE->page_start();
$PAGE->stripe_start();

$out = '';
if (get_http_var('action') === 'Save') {
    $out = update_banner();
}

$out .= edit_banner_form();

print '<div id="adminbody">';
print $out;
?>
<script language="javascript" type="text/javascript">
    $('#preview').on('click', function bannerPreview() {
        var text = $('#banner_text').val();
        if ( text ) {
            var banner_el = $('#surveyPromoBanner');
            var fadeDelay = 1000;
            if ( !banner_el.length ) {
                fadeDelay = 0;
                banner_el = $('<div id="surveyPromoBanner" style="clear:both;padding:1em;margin-top:24px;background:#DDD;">&nbsp;</div>');
                $('body').prepend(banner_el);
            }
            banner_el.fadeOut(fadeDelay, function updateBannerText() {
                banner_el.html($('#banner_text').val());
                banner_el.fadeIn(1000);
            });
        } else {
            banner_el = $('#surveyPromoBanner').remove();
        }
    });
</script>
<?php
print '</div>';

function edit_banner_form() {
    global $db;
    $query = "SELECT value FROM editorial WHERE item = 'banner'";
    $q = $db->query($query);

    $out = '';

    for ($row = 0; $row < $q->rows(); $row++) {

        $out .= '<form action="banner.php" method="post">';
        $out .= '<input name="action" type="hidden" value="Save">';
        $out .= '<p><label for="banner">Contents (HTML permitted)</label><br>';
        $out .= '<textarea id="banner_text" name="banner" rows="5" cols="80">' . htmlentities($q->field($row, 'value')) . "</textarea></p>\n";
        $out .= '<span class="formw"><input type="button" id="preview" value="Preview"> <input name="btnaction" type="submit" value="Save"></span>';
        $out .= '</form>';
    }

    return $out;
}

function update_banner() {
    global $db;

    $out = '';
    $banner_text = get_http_var('banner');

    $q = $db->query("UPDATE editorial set value = :banner_text WHERE item = 'banner'",
        array(
            ':banner_text' => $banner_text
        )
    );

    if ( $q->success() ) {
        $out = "<h4>update successful</h4>";

        $out = "<p>Banner text is now:</p><p>$banner_text</p>";

        global $memcache;
        if (!$memcache) {
            $memcache = new \Memcache;
            $memcache->connect('localhost', 11211);

        }
        if ( trim($banner_text) == '' ) {
            $banner_text = NULL;
        }
        $memcache->set(OPTION_TWFY_DB_NAME . ':banner', $banner_text, MEMCACHE_COMPRESSED, 86400);
    } else {
        $out = "<h4>Failed to update banner text</h4>";
    }

    return $out;
}

$menu = $PAGE->admin_menu();

$PAGE->stripe_end(array(
    array(
        'type'    => 'html',
        'content' => $menu
    )
));

$PAGE->page_end();
