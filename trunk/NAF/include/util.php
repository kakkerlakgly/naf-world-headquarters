<?

function makeLink($text, $link) {
  global $page;
  return "<a href=\"naf.php?page=$page&amp;op=$link\">$text</a>";
}

function makeFormHeader($page, $op) {
  return "<form action=\"naf.php\" method=\"post\">"
        ."<input type=\"hidden\" name=\"page\" value=\"$page\" />"
        ."<input type=\"hidden\" name=\"op\" value=\"$op\" />"
        ."<input type=\"hidden\" name=\"action\" value=\"submit\" />";
}

function makeFormInput($name, $value='', $type='text', $size='', $extra='') {
  return "<input type=\"$type\" name=\"$name\"".(strlen($size)>0?" size=\"$size\"":"").
         (strlen($value)>0?" value=\"$value\"":"").(strlen($extra)>0?$extra:"")." />";
}

function makeFormFooter() {
  return "</form>";
}

?>
