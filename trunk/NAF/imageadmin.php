<?
  if (!pnUserLoggedIn() ||
      !pnSecAuthAction(0, 'NAF::', 'Editor::', ACCESS_ADMIN)
     ) {
    pnRedirect("/");
    exit;
  }

  function hsize($size) {
    if ($size < 1024)
      return $size."b";
    $size /= 1024;
    if ($size < 1024)
      return round($size)."kb";
    $size /= 1024;
    return round($size)."Mb";
  }

  $path = "NAF/ArticleImages";
  $fullPath = getEnv('DOCUMENT_ROOT')."/".$path;

  switch($op) {
    case 'upload':
      list($name, $file) = pnVarCleanFromInput('name', 'file');

      if (strlen($name)==0) {
        pnRedirect("naf.php?page=imageadmin&error=".urlencode("You must supply an image name."));
        exit;
      }

      $name =urldecode($name);

      $name = pnVarPrepForOS($name);
      if (!ereg("[a-zA-Z0-9._-]+", $name)) {
        pnRedirect("naf.php?page=imageadmin&error=".urlencode("Filename may only consist of alphanumerical characters, _ (underscore), - (dash) and . (dot)"));
        exit;
      }

      if (strlen($name) < 5 || substr($name, -4) == ".php") {
        pnRedirect("naf.php?page=imageadmin&error=".urlencode("Image name has to have an extension which must not be '.php'"));
        exit;
      }

      if (file_exists($fullPath."/".$name)) {
        pnRedirect("naf.php?page=imageadmin&error=".urlencode("File already exists."));
        exit;
      }

      if ($file != "" && $file != "none") {
       if (!file_exists($_FILES['file']['tmp_name']) || filesize($_FILES['file']['tmp_name']) < 1) {
         pnRedirect("naf.php?page=imageadmin&error=".urlencode("You can't upload an empty file."));
         exit;
       }
       if (move_uploaded_file($_FILES['file']['tmp_name'], $fullPath."/".$name))
         pnRedirect("naf.php?page=imageadmin");
       else
         pnRedirect("naf.php?page=imageadmin&error=".urlencode("Error uploading file."));
      }
      break;
    case 'del':
      $confirm = pnVarCleanFromInput('confirm');
      $image = pnVarPrepForOS(urldecode(pnVarCleanFromInput('image')));

      if (strpos($image, "/") !== false) {
        exit;
      }
      if ($confirm == 1) {
        unlink(getEnv('DOCUMENT_ROOT')."/".$path."/".$image);
        pnRedirect("naf.php?page=imageadmin");
      }
      else {
        include 'header.php';
        OpenTable();
        echo "<div style=\"font-size: 2em; text-align: center;\">"
            ."Are you sure you want to Delete $image?<br />"
            ."<a href=\"naf.php?page=imageadmin&op=del&image=".urlencode($image)."&confirm=1\">Yes</a> &nbsp; "
            ."<a href=\"naf.php?page=imageadmin\">No</a></div>"
            ."<br /><img src=\"NAF/ArticleImages/$image\" />";
        CloseTable();
        include 'footer.php';
      }
      break;
    case 'ren':
      $newname = pnVarPrepForOS(urldecode(pnVarCleanFromInput('newname')));
      $image = pnVarPrepForOS(urldecode(pnVarCleanFromInput('image')));
      if (strlen($newname) == 0) {
        include 'header.php';
        OpenTable();
        echo "<form method=\"post\" action=\"naf.php\">";
        echo "Rename '$image' to <input type=\"text\" name=\"newname\" />";
        echo "<input type=\"hidden\" name=\"image\" value=\"$image\" />"
            ."<input type=\"hidden\" name=\"page\" value=\"imageadmin\" />"
            ."<input type=\"hidden\" name=\"op\" value=\"ren\" />"
            ."<input type=\"submit\" value=\"Rename\" />";
        echo "</form>";
        CloseTable();
        include 'footer.php';
      }
      else {
        if (!ereg("[a-zA-Z0-9._-]+", $image)) {
          pnRedirect("naf.php?page=imageadmin&error=".urlencode("Filename may only consist of alphanumerical characters, _ (underscore), - (dash) and . (dot)"));
          exit;
        }
        if (!ereg("[a-zA-Z0-9._-]+", $newname)) {
          pnRedirect("naf.php?page=imageadmin&error=".urlencode("Filename may only consist of alphanumerical characters, _ (underscore), - (dash) and . (dot)"));
          exit;
        }

        if (file_exists($newname)) {
          echo "File already exists.";
          exit;
        }
        rename($fullPath."/".$image, $fullPath."/".$newname);
        pnRedirect("naf.php?page=imageadmin");
      }
      break;
    default:
      include 'header.php';
      OpenTable();

      $error = pnVarCleanFromInput('error');

      echo '<div style="font-size: 2em; text-align: center; margin-bottom: 5px;">Image Admin</div>';

      if (strlen($error)>0) {
        echo "<div style=\"color: red; font-weight: bold\">".urldecode($error)."</div>";
      }

      echo '<form action="naf.php" enctype="multipart/form-data" method="POST">';
      echo '<input type="hidden" name="page" value="imageadmin">'
           .'<input type="hidden" name="op" value="upload">';
      echo 'Image Name: <input type="text" name="name" /> '
          .'File: <input type="file" name="file" /> '
          .'<input type="submit" value="Upload">';
      echo '</form>';
      echo 'It is strongly recommended that you prefix the image name with something that identifies the '
          .'article it belongs to.<br /><br />The images reside in '.$path.'/.';

      echo '<br /><table border="0" cellspacing="1" cellpadding="2"  bgcolor="#858390">';

      $d = dir($path);
      echo "<tr bgcolor=\"#D9D8D0\"><th>&nbsp;</th><th>Image</th><th>Size</th></tr>";
      while (false !== ($entry = $d->read())) {
        if (!is_dir("$path/$entry") && $entry != "index.php")
          echo "<tr bgcolor=\"#f8f7ee\"><td>(<a href=\"naf.php?page=imageadmin&op=del&image=".urlencode($entry)."\">Del</a>) "
              ."(<a href=\"naf.php?page=imageadmin&op=ren&image=".urlencode($entry)."\">Ren</a>)</td>"
              ."<td><a href=\"$path/$entry\">$entry</a></td>"
              ."<td>".hsize(filesize($fullPath."/".$entry))."</td></tr>\n";
      }
      $d->close();  

      echo '</table>';

      CloseTable();
      include 'footer.php';
      break;
  }
?>
