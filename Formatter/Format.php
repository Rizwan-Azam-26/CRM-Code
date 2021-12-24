<?php


namespace App\Libraries\Formatter;


class Format
{
    public static function digits10($phone){
        $number = preg_replace('/[^0-9]/', '', $phone); // keep it to numbers
        $number = preg_replace('/^1|\D/', '', $number); // strip 1
        return  $number;
    }

    public static function phone($phone){

        $clean = preg_replace('/[^0-9]/','',$phone);

        if(strlen($clean) == 11){
            return '('.substr($clean, 1, 3).') '.substr($clean, 4, 3).'-'.substr($clean, 7);
        }elseif(strlen($clean) == 10){
            return '('.substr($clean, 0, 3).') '.substr($clean, 3, 3).'-'.substr($clean, 6);
        }elseif(strlen($clean) == 7){
            return substr($clean, 3, 3).'-'.substr($clean, 6);
        }else{
            return $phone;
        }

    }

    public static function relative_date($date, $format = 'D m/d/Y g:ia', $relative = true){

        if(!strtotime($date)){
            return '';
        }

        $day = date('Y-m-d', strtotime($date));

        if($relative) {
            if ($day == date('Y-m-d')) {
                return 'Today, ' . date($format, strtotime($date));
            } elseif ($day == date('Y-m-d', strtotime('-1 day'))) {
                return 'Yesterday, ' . date($format, strtotime($date));
            } elseif ($day == date('Y-m-d', strtotime('+1 day'))) {
                return 'Tomorrow, ' . date($format, strtotime($date));
            } else {
                return date($format, strtotime($date));
            }
        }else{
            return date($format, strtotime($date));
        }
    }

    public static function time_elapsed_string($datetime, $full = false) {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public static function phoneDashes($phone){

        if(  preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $phone,  $matches ) )
        {
            $result = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
            return $result;
        }
        return $phone;
    }

    public static function asList($key, $value, $data){
        $list = array();
       
        foreach($data as $d){
            if(is_array($value)){
                $value_group = array();
                foreach($value as $v){
                    $value_group[] = $d[$v];
                }
                $list[$d[$key]] = implode(' ',$value_group);
            }else {
                $list[$d[$key]] = $d[$value];
            }
        }
        return $list;
    }



    /** This returns a clean digit only phone number */
    public static function validatePhone($phone){
        $regex = "/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i";
        $number = preg_replace('/[^0-9]/','', $phone);
        return  (preg_match( $regex, $number ) ? $number : null);
    }

    public static function ssn($ssn){
        $clean = preg_replace('/[^0-9]/','',$ssn);
        return substr($clean, 0, 3).'-'.substr($clean, 3, 2).'-'.substr($clean, 5);
    }

    public static function short_datetime($date){
        if(!strtotime($date)){
            return '';
        }

        if(date('Y', strtotime($date)) == date("Y")){
            // Same Year
            $short_date = date('M j, g:i a', strtotime($date));
        }else{
            $short_date = date('M j, Y', strtotime($date));

        }

        return $short_date;
    }

    public static function time2str($ts)
    {
        if(!ctype_digit($ts))
            $ts = strtotime($ts);

        $diff = time() - $ts;
        if($diff == 0)
            return 'now';
        elseif($diff > 0)
        {
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 60) return 'just now';
                if($diff < 120) return '1 minute ago';
                if($diff < 3600) return floor($diff / 60) . ' minutes ago';
                if($diff < 7200) return '1 hour ago';
                if($diff < 86400) return floor($diff / 3600) . ' hours ago';
            }
            if($day_diff == 1) return 'Yesterday';
            if($day_diff < 7) return $day_diff . ' days ago';
            if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
            if($day_diff < 60) return 'last month';
            return date('F Y', $ts);
        }
        else
        {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 120) return 'in a minute';
                if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
                if($diff < 7200) return 'in an hour';
                if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
            }
            if($day_diff == 1) return 'Tomorrow';
            if($day_diff < 4) return date('l', $ts);
            if($day_diff < 7 + (7 - date('w'))) return 'next week';
            if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
            if(date('n', $ts) == date('n') + 1) return 'next month';
            return date('F Y', $ts);
        }
    }

    public static function time2strdays($ts)
    {
        $now = new \DateTime(date("Y-m-d"));
        $set_date = new \DateTime($ts);
        $interval = date_diff($now, $set_date);
        $days = $interval->format('%a');

        if($days == 0)
            return 'Today';
        elseif($days == 1){
            return 'Tomorrow';
        }
        elseif($now < $set_date)
        {
            return $interval->format('in %a days');
        }
        else
        {
            return $interval->format('%a days ago');
        }
    }

    public static function humanizeDate($now,$otherDate=null,$offset=null){

        if($otherDate != null){

            $offset = $now - $otherDate;
        }

        if($offset != null){
            $deltaS = $offset%60;
            $offset /= 60;
            $deltaM = $offset%60;
            $offset /= 60;
            $deltaH = $offset%24;
            $offset /= 24;
            $deltaD = ($offset > 1)?ceil($offset):$offset;
        } else{
            throw new \Exception("Must supply otherdate or offset (from now)");
        }
        if($deltaD > 1){
            if($deltaD > 365){
                $years = ceil($deltaD/365);
                if($years ==1){
                    return "last year";
                } else{
                    return "<br>$years years ago";
                }
            }
            if($deltaD > 6){
                return date('M d',strtotime("$deltaD days ago"));
            }
            return "$deltaD days ago";
        }
        if($deltaD == 1){
            return "Yesterday";
        }
        if($deltaH == 1){
            return "last hour";
        }
        if($deltaM == 1){
            return "last minute";
        }
        if($deltaH > 0){
            return $deltaH." hours ago";
        }
        if($deltaM > 0){
            return $deltaM." minutes ago";
        }
        else{
            return "few seconds ago";
        }
    }


    public static function seconds_to_minutes($seconds){

        $ms = '';

        $hours = intval((($seconds / 60) / 60) % 60);
        if($hours > 0){
            $ms = str_pad($hours, 2, "0", STR_PAD_LEFT). ":";
        }

        $minutes = intval(($seconds / 60) % 60);
        $ms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

        $secs = intval($seconds % 60);
        $ms .= str_pad($secs, 2, "0", STR_PAD_LEFT);

        return $ms;

    }

    public static function number_string($string) {
        $string = preg_replace('/[^0-9]/','', (string)$string);
        return $string;
    }

    public static function priority_level($level){

        switch($level){
            case 1:
                return '<span class="label label-important">Level 1</span>';
            case 2:
                return '<span class="label label-warning">Level 2</span>';
            case 3:
                return '<span class="label label-success">Level 3</span>';
            case 4:
                return '<span class="label label-notice">Level 4</span>';
            case 5:
                return '<span class="label">Level 5</span>';
        }

    }

    public static function payment_status($id){

        switch($id){
            case 1:
                return '<span class="label label-success">Current</span>';
            case 2:
                return '<span class="label label-important">NSF</span>';
        }

    }

    /** Clean Filename for Linux Path file saving */
    public static function sanitizeString($string){
        $string = preg_replace(array('/\s+/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $string);
        return strtolower($string);
    }

    /* FILESIZE in readable format */
    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public static function get_ip() {

        //Just get the headers if we can or else use the SERVER global
        if ( function_exists( 'apache_request_headers' ) ) {

            $headers = apache_request_headers();

        } else {

            $headers = $_SERVER;

        }

        //Get the forwarded IP if it exists
        if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {

            $the_ip = $headers['X-Forwarded-For'];

        } elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
        ) {

            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];

        } else {

            $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );

        }

        return $the_ip;
    }

    public static function getCurrentWeekDateRange(){

        $monday = date( 'Y-m-d', strtotime( 'monday this week' ) );
        $friday = date( 'Y-m-d', strtotime( 'friday this week' ) );

        return array($monday, $friday);
    }

    public static function build_table($array)
    {
        // start table
        $html = '<table>';
        // header row
        $html .= '<tr>';
        foreach (current($array) as $key => $value) {
            $html .= '<th>' . $key . '</th>';
        }
        $html .= '</tr>';

        // data rows
        foreach ($array as $key => $value) {
            $html .= '<tr>';
            foreach ($value as $key2 => $value2) {
                $html .= '<td>' . $value2 . '</td>';
            }
            $html .= '</tr>';
        }

        // finish table and return it

        $html .= '</table>';
        return $html;
    }

    public static function hoursRange($lower = 0, $upper = 23, $step = 0.25, $format = NULL) {

        if ($format === NULL) {
            $format = 'g:ia'; // 9:30pm
        }
        $times = array();
        foreach(range($lower, $upper, $step) as $increment) {
            $increment = number_format($increment, 2);
            list($hour, $minutes) = explode('.', $increment);
            $date = new \DateTime($hour . ':' . $minutes * .6);

            $times[(string)$date->format('H:i:s')] = $date->format($format);
        }
        return $times;
    }

    public static function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
