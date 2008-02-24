<?
  class DB {
    var $dbConn;
    var $rows;
    var $res;
    var $error;
    var $errorMessage;
    var $fields;

    function DB($db) {
      $this->error = 0;
      $this->dbConn = mysql_connect("localhost", "bloodbo_admin", "db_poutine");
      if ($this->dbConn == null) {
        $this->error = 1;
        $this->errorString = "Unable to connect to database";
      }
      else if (!mysql_select_db($db)) {
        $this->error = 1;
        $this->errorMessage = "Unable to select database";
      }

      $this->errorMessage="";
    }

    function getLastError() {
      return $this->error;
    }

    function getResult() {
      return $this->res;
    }

    function query($qry) {
      $result = mysql_query($qry);

      $errno = mysql_errno();
      if ($result == false || $errno != 0) {
        $error = mysql_error();
        $this->error = $errno;
        $this->errorMessage = "Database error $errno: ".$error."<br />Query: " . $qry . "<br />";
        $this->rows = 0;
        $this->fields = 0;
        $this->res = 0;
        return 0;
      }
      else {
        $this->error = 0;
        $this->res = $result;
        error_reporting(0);
        $this->rows = mysql_num_rows($this->res);
        $this->fields = mysql_num_fields($this->res);
        error_reporting(E_ALL & ~E_NOTICE);
        return 1;
      }
    }

    function numRows() {
      return $this->rows;
    }

    function getNextObject() {
      return mysql_fetch_object($this->res);
    }

    function getErrorMessage() {
      return $this->errorMessage;
    }

    function displayResultTable() {

      echo "<table border=\"1\"><tr>";

      $fields = mysql_num_fields($this->res);
//echo $fields;
      $i = 0;
      while ($i < $fields) {
        $meta = mysql_fetch_field($this->res, $i);
        if (!$meta) {
          echo "No information available<br />\n";
        }
        echo "<th>$meta->name</th>";
        $i++;
      }
      echo  "</tr>\n";

      for ($i=0; $i<$this->rows; $i++) {
        $arr = mysql_fetch_row($this->res);
        echo "<tr>";
        for ($j=0; $j<$fields; $j++) {
          echo "<td>".$arr[$j]."</td>";
        }
        echo "</tr>\n";
      }

      echo "</table>";

    }
  }
?>
