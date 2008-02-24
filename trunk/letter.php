<?

include 'NAF/include/welcomeMessage.php';

echo "----------- PayPal Signup --------------<br />\n";

echo nl2br(generateWelcomeMessage("USERNAME", "ACTIVATIONCODE", ""));

echo "<br /><br />----------- Tournament Signup --------------<br />\n";

echo nl2br(generateWelcomeMessage("USERNAME", "", "PASSWORD"));



?>
