<?php
function smarty_function_quote($params, &$smarty) 
{
  extract($params); 
  unset($params);

  $dbconn=& pnDBGetConn(true);
  $seed = microtime()*1000000;
  $quote = $dbconn->Execute("SELECT quote FROM naf_quote ORDER BY rand($seed) LIMIT 1");
  echo $quote->fields[0];
  $quote->Close();
}
?>