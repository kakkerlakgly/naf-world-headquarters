<?
  // This file belongs to the user registration process. Don't mess with it.

  include 'includes/pnAPI.php';
  pnInit();
  include 'header.php';

  OpenTable();

  echo "<font style=\"font: bold 24px Verdana, Helvetica, Arial;\">Congratulations!</font><br><br>"   
      ."You are now a <strong>NAF</strong> member.  You should receive your password in an e-mail shortly."
      ."<br><br>"
      ."In addition, you should recieve a new member pack in the mail which will include "
      ."a membership card with your membership ID and other NAF promotional "   
      ."items.  Expect four to six weeks for delivery, perhaps longer in some parts of the world."
      ."<br><br>"
      ."I would like to thank you again for joining NAF.<br>"
      ."<img src='/images/signature.gif' alt='signature' border='0'><br>"
      ."<strong>John K. Lewis</strong><br>"
      ."<strong>NAF President</strong>";

  CloseTable();

  include 'footer.php';	
	
?>
