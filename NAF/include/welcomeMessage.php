<?

function generateWelcomeMessage($username, $activationcode, $password) {

  $message = "Welcome to the NAF!\n"
            ."\n"
            ."You should log on to the site (http://www.bloodbowl.net) as soon "
            ."as possible to fill out your shipping address so we can send you "
            ."your new member pack.\n"
            ."\n"
            ."Your username is: $username\n";

  if (strlen($activationcode) > 0) {
    // User payed through PayPal
    $message .= "Your activation code is: $activationcode\n"
               ."\n"
               ."Use the password you specified when signing up to log in and supply "
               ."the above activation code when requested to activate your account.\n";
  }
  else if (strlen($password) > 0) {
    // User got manually added. Either NAF staff or tourney attendant.
    $message .= "Your password is: $password\n"
               ."\n"
               ."For security reasons you should change this as soon as you log in.\n";
  }
  else {
    $message .= "\nUnfortunately, there seems to be a problem with your account. "
               ."Please contact nuffle@bloodbowl.net for further instructions.\n";
  }

  $message .= "\n"
             ."Once again, welcome to the NAF,\n"
             ."  The NAF staff";

  return $message;
}

function generateRenewalMessage($username) {
  $message = "Thank you for your continued faith in the NAF!\n"
            ."\n"
            ."This message is a confirmation that your payment has gone through "
            ."properly and that your membership has been extended.\n"
            ."\n"
            ."Please take a moment and log on to the site (http://www.bloodbowl.net) and "
            ."confirm your shipping address (My Account -> Change your info) "
            ."so that we can send you your membership pack for this year.\n"
            ."\n"
            ."\n"
            ."Best regards,\n"
            ."  The NAF staff";
  return $message;
}

?>
