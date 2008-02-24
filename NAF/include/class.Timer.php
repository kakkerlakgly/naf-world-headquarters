<?
class Timer {
        var $start = 0;
        var $end = 0;
        
        function start() {
                list($usec, $sec) = explode(" ", microtime());
                $this->start = ((float)$usec + (float)$sec);
        }
        
        function end() {
                list($usec, $sec) = explode(" ", microtime());
                $this->end = ((float)$usec + (float)$sec);
        }
        
        function getElapsedTime() {
                return $this->end - $this->start;
        }
}

?>