<?php

$thename = "NAF";
$postnuke_theme = true;

themes_get_language();

$bgcolor1 = "#f8f7ee";
$bgcolor2 = "#B3B2AB";
$bgcolor3 = "#f8f7ee";
$bgcolor4 = "#CCCBC4";
$textcolor1 = "#000000";
$textcolor2 = "#000000";
$textBeige = "#F8F7EE";

function OpenTable() {
    global $bgcolor1, $bgcolor2;
    echo "<table width=\"99%\" border=\"0\" cellspacing=\"3\" cellpadding=\"0\" bgcolor=\"#000000\"><tr><td>\n";
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\" bgcolor=\"$bgcolor1\"><tr><td>\n";
}

function CloseTable() {
    echo "</td></tr></table></td></tr></table><br />\n";
}

function OpenTable2() {
    global $bgcolor1, $bgcolor2;
    echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"$bgcolor2\" align=\"center\"><tr><td>\n";
    echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"8\" bgcolor=\"$bgcolor1\"><tr><td>\n";
}

function CloseTable2() {
    echo "</td></tr></table></td></tr></table><br />\n";
}

function themeheader() {
    global  $thename, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4,  $index;

    $slogan = pnConfigGetVar('slogan');
    $sitename = pnConfigGetVar('sitename');
    $banners = pnConfigGetVar('banners');
    $type = pnVarCleanFromInput('type');

    echo "</head>\n";
    echo "<body bgcolor=\"#990000\" text=\"#000000\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onload=\"preloadImages();\">\n\n\n";
    //Begin Header
        include("themes/$thename/header.html");

    echo "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n"
        ."<tr valign=\"top\">\n"
        ."<td bgcolor=\"#000000\"><img src=\"themes/$thename/images/pixel.gif\" width=\"2\" height=\"1\" border=\"0\" alt=\"\"></td>\n"
        ."<td bgcolor=\"#f8f7ee\"><img src=\"themes/$thename/images/pixel.gif\" width=\"10\" height=\"1\" border=\"0\" alt=\"\"></td>\n"
        ."<td bgcolor=\"#f8f7ee\" background=\"themes/$thename/images/blockleft_bg.gif\" width=\"149\" valign=\"top\">\n";
    blocks('left');
    echo "</td>\n"
        ."<td bgcolor=\"#990000\"><img src=\"themes/$thename/images/pixel.gif\" width=\"5\" height=\"1\" border=\"0\" alt=\"\"></td>\n"
        ."<td width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"#990000\">\n";
            if ($index == 1) {
            blocks('centre');
        }
}

function themefooter() {
    global $thename, $index, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4;
    $slogan = pnConfigGetVar('slogan');
            if ($index == 1) {
    echo "</td>\n"
        ."<td bgcolor=\"#990000\"><img src=\"themes/$thename/images/pixel.gif\" width=\"5\" height=\"1\" border=\"0\" alt=\"\"></td>\n"
        ."<td bgcolor=\"#990000\" valign=\"top\" width=\"140\">\n";
            blocks('right');
}
    echo "</td>\n"
        ."<td bgcolor=\"#990000\"><img src=\"themes/$thename/images/pixel.gif\" width=1 height=1 border=0 alt=\"\">\n"
        ."<td bgcolor=\"#000000\"><img src=\"themes/$thename/images/pixel.gif\" width=\"2\" height=\"1\" border=\"0\" alt=\"\"></td>\n"
        ."</td>\n"
        ."</tr>\n"
        ."</table>\n\n\n";
//Begin Foot Slogan
        include("themes/$thename/footnav.html");
}

function themeindex ($_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $info, $links, $preformat) {
    global $thename, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4,  $sepcolor;
    $anonymous = pnConfigGetVar('anonymous');
    $tipath = pnConfigGetVar('tipath');
//Begin Story Box
        include("themes/$thename/storybox.html");
}

function themearticle ($_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $_deprecated, $info, $links, $preformat) {
    global $thename, $bgcolor1, $bgcolor2, $bgcolor3, $bgcolor4, $sepcolor;
//Begin Article Box
        include("themes/$thename/articlebox.html");
}

function themesidebox($block) {
    global $thename;
if (empty($block['position'])) {

$block['position'] = "a"; }

//Begin Left Block
    if ($block['position'] == 'l') {
        include("themes/$thename/leftblock.html");
}

//Begin Right Block
    if ($block['position'] == 'r') {
        include("themes/$thename/rightblock.html");
}

//Begin Center Block
    if ($block['position'] == 'c') {
        include("themes/$thename/centerblock.html");
        }

}
?>
