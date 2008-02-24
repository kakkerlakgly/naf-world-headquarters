<?

  if (!pnUserLoggedIn()) {
    pnRedirect('/');
    exit;
  }

  include 'header.php';
  OpenTable();

  echo '<div style="font-size: 2em;">Membership Renewal</div>';

  $uid = pnUserGetVar('uid');

  $query = "SELECT unix_timestamp(coachexpirydate), unix_timestamp(date_sub(coachexpirydate, interval 1 year)) "
          ."FROM naf_coach WHERE coachid=$uid";
  $res = $dbconn->execute($query);
  $expdate = $res->fields[0];
  $startdate = $res->fields[1];

  if ($expdate == 0) {
    $query = "SELECT unix_timestamp(date_add(admin_date, interval 1 year)), unix_timestamp(admin_date) "
            ."FROM naf_administration "
            ."WHERE coachid=$uid "
            ."  AND action='PAYMENT' "
            ."ORDER BY admin_date DESC LIMIT 1";
    $res = $dbconn->execute($query);
    $expdate = $res->fields[0];
    $startdate = $res->fields[1];
  }

  $query = "SELECT unix_timestamp(max(start)) FROM naf_gift";
  $latestGift = $dbconn->getOne($query);
  if ($startdate < $latestGift)
    $allowRenew = true;
  else
    $allowRenew = false;

  $active = time() < $expdate;
  
  echo "<div style=\"font-size: 1.5em;\">";

  if ($active) {
    echo "Your membership expires: ".date('F jS Y', $expdate)."<br /><br />";
    if ($allowRenew) {
      echo "Even though your membership has not expired yet, "
          ."you may renew it in advance. By doing so "
          ."you will get the new membership card and premium as soon "
          ."as we can process and ship them.<br />"
          ."<br />"
          ."The button below will take you to PayPal and allow you to pay "
          ."the membership fee for another year. This will, of course, extend "
          ."your membership by a year and you will not \"lose\" any time.<br />"
          ."<br />"
          ."If you are unable to pay through PayPal, you might be able to  pay "
          ."the fee to a NAF representative at a NAF-sanctioned tournament. "
          ."Please contact your National Tournament Organizer for further information "
          ."on the availability of this option.<br /><br />";
    }
  }
  else {
    echo "Your membership has expired.<br />"
        ."<br />"
        ."To continue using the NAF site, you must renew your membership. "
        ."The button below will take you to PayPal and allow you to pay "
        ."the membership fee for another year.<br />"
        ."<br />"
        ."If you are unable to pay through PayPal, you might be able to  pay "
        ."the fee to a NAF representative at a NAF-sanctioned tournament. "
        ."Please contact your National Tournament Organizer for further information "
        ."on the availability of this option.<br /><br />";
    if (!$allowRenew) {
      echo "<span style=\"color: red; font-weight: bold;\">Note:</span> "
          ."The premium for this year's membership is not ready to be shipped "
          ."at this point. You will be unable to renew your membership "
          ."until that point. We apologize for the inconvenience.<br /><br />";
    }
  }

  /*
  echo "Membership status: ".($expdate<time()?"Expired":"Active")."<br />";
  echo "Start date: ".date('F jS Y', $startdate)."<br />";
  echo "Latest gift: ".date('F jS Y', $latestGift)."<br />";

  echo "Allow renewal: ".($allowRenew?"yes":"no")."<br />";

  if (!$allowRenew && $expdate < time())
    echo "<span style=\"color: red; font-weight: bold;\">Warning:</span> Expiry date has passed and there is no new gift!<br />";
  */

/*  // This is a temporary hack to set up the initial expiry dates.
$query = "SELECT coachid, date_add(max(admin_date), interval 1 year) FROM naf_administration WHERE action='PAYMENT' group by coachid";
$res = $dbconn->execute($query);
for ( ; !$res->EOF; $res->moveNext() ) {
  $query = "UPDATE naf_coach SET coachexpirydate='".$res->fields[1]."' WHERE coachid=".$res->fields[0]." AND coachexpirydate IS NULL";
  $dbconn->execute($query);
}
*/

  if ($allowRenew) {
  $base = pnGetBaseURL();
    echo '<form method="POST" action="https://www.paypal.com/cgi-bin/webscr"><input type="submit" value="Renew Membership" />'
        .'<input type="hidden" name="cmd" value="_xclick" />'
        .'<input type="hidden" name="amount" value="10.00" />'
        .'<input type="hidden" name="business" value="nuffle@bloodbowl.net" />'
        .'<input type="hidden" name="item_name" value="NAF Membership Renewal" />'
        .'<input type="hidden" name="item_number" value="'.$uid.'" />'
        .'<input type="hidden" name="no_shipping" value="1" />'
        .'<input type="hidden" name="return" value="'.$base.'transactioncomplete.php" />'
        .'<input type="hidden" name="cancel_return" value="'.$base.'" />'
        .'<input type="hidden" name="no_note" value="1" />';
  }

  echo "</div>";

  CloseTable();
  include 'footer.php';

?>
