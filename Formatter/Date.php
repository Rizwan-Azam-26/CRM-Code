<?php

namespace App\Libraries\Formatter;

class Date {
    // The constants correspond to units of time in seconds
    const MINUTE = 60;
    const HOUR   = 3600;
    const DAY    = 86400;
    const WEEK   = 604800;
    const MONTH  = 2628000;
    const YEAR   = 31536000;
    /**
     * A helper used by parse() to create the human readable strings. Given a
     * positive difference, corresponding to a date in the past, it appends the
     * word 'ago'. And given a negative difference, corresponding to a date in
     * the future, it prepends the word 'In'. Also makes the unit of time plural
     * if necessary.
     *
     * @param  integer $difference The difference between dates in any unit
     * @param  string  $unit       The unit of time
     * @return string  The date in human readable format
     */

    static function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    static function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        $errors = \DateTime::getLastErrors();
        if (isset($errors['warning_count']) && !empty($errors['warning_count'])){
           return false;
        }
        if($d){
            return $d->format($format);
        }
        return false;
    }

    static function returnFormatorNull($date, $input_format = 'Y-m-d' , $output_format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($input_format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        $errors = \DateTime::getLastErrors();
        if (isset($errors['warning_count']) && !empty($errors['warning_count'])){
            return null;
        }
        if($d){
            return $d->format($output_format);
        }
        return null;
    }

    static function getPayPeriodDates(){
        if(date('d') <= 25 && date('d') >= 11){
            // 11th - 25th
            $start_date = date('Y-m-11 00:00:00');
            $end_date = date('Y-m-d 23:59:59');
        }else{
            // 26th - 10th
            if(date('d') >= 1 && date('d') <= 10){
                $start_date = date('Y-m-26 00:00:00',strtotime('-1 month'));
                $end_date = date('Y-m-d 23:59:59');
            }else{
                $start_date = date('Y-m-26 00:00:00');
                $end_date = date('Y-m-d 23:59:59');
            }
        }
        return array($start_date, $end_date);
    }


    static function number_of_working_days($from, $to) {
        $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
        //$holidayDays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays
        $holidayDays = ['*-12-25', '*-01-01']; # variable and fixed holidays

        $from = new \DateTime($from);
        $to = new \DateTime($to);
        //$to->modify('-1 day');

        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);

        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) continue;
            if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($period->format('*-m-d'), $holidayDays)) continue;
            $days++;
        }
        return $days;
    }


    private static function prettyFormat($difference, $unit)
    {
        // $prepend is added to the start of the string if the supplied
        // difference is greater than 0, and $append if less than
        $prepend = ($difference < 0) ? 'In ' : '';
        $append = ($difference > 0) ? ' ago' : '';
        $difference = floor(abs($difference));
        // If difference is plural, add an 's' to $unit
        if ($difference > 1) {
            $unit = $unit . 's';
        }
        return sprintf('%s%d %s%s', $prepend, $difference, $unit, $append);
    }
    /**
     * Returns a pretty, or human readable string corresponding to the supplied
     * $dateTime. If an optional secondary DateTime object is provided, it is
     * used as the reference - otherwise the current time and date is used.
     *
     * Examples: 'Moments ago', 'Yesterday', 'In 2 years'
     *
     * @param  DateTime $dateTime  The DateTime to parse
     * @param  DateTime $reference (Optional) Defaults to the DateTime('now')
     * @return string   The date in human readable format
     */
    public static function parse(\DateTime $dateTime, \DateTime $reference = null)
    {
        // If not provided, set $reference to the current DateTime
        if (!$reference) {
            $reference = new \DateTime(NULL, new \DateTimeZone($dateTime->getTimezone()->getName()));
        }
        // Get the difference between the current date and the supplied $dateTime
        $difference = $reference->format('U') - $dateTime->format('U');
        $absDiff = abs($difference);
        // Get the date corresponding to the $dateTime
        $date = $dateTime->format('Y/m/d');
        // Throw exception if the difference is NaN
        if (is_nan($difference)) {
            throw new Exception('The difference between the DateTimes is NaN.');
        }
        // Today
        if ($reference->format('Y/m/d') == $date) {
            if (0 <= $difference && $absDiff < self::MINUTE) {
                return 'Moments ago';
            } elseif ($difference < 0 && $absDiff < self::MINUTE) {
                return 'Seconds from now';
            } elseif ($absDiff < self::HOUR) {
                return self::prettyFormat($difference / self::MINUTE, 'minute');
            } else {
                return self::prettyFormat($difference / self::HOUR, 'hour');
            }
        }
        $yesterday = clone $reference;
        $yesterday->modify('- 1 day');
        $tomorrow = clone $reference;
        $tomorrow->modify('+ 1 day');
        if ($yesterday->format('Y/m/d') == $date) {
            return 'Yesterday';
        } else if ($tomorrow->format('Y/m/d') == $date) {
            return 'Tomorrow';
        } else if ($absDiff / self::DAY <= 7) {
            return self::prettyFormat($difference / self::DAY, 'day');
        } else if ($absDiff / self::WEEK <= 5) {
            return self::prettyFormat($difference / self::WEEK, 'week');
        } else if ($absDiff / self::MONTH < 12) {
            return self::prettyFormat($difference / self::MONTH, 'month');
        }
        // Over a year ago
        return self::prettyFormat($difference / self::YEAR, 'year');
    }

    static function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }


    static function today(){
        return array(new \DateTime('midnight'), new \DateTime('tomorrow midnight'));
    }

    static function yesterday(){
        return array(new \DateTime('midnight yesterday'), new \DateTime('midnight'));
    }

    static function tomorrow(){
        return array(new \DateTime('midnight tomorrow'), new \DateTime('midnight + 2 days'));
    }

    static function thisWeek(){

        $timestamp = time();
        if(date('w', $timestamp) === '1'){
            // Today is Monday
            return array(new \DateTime('midnight yesterday'), new \DateTime('midnight'));

        }else{
            return array(new \DateTime('midnight last monday'), new \DateTime('midnight monday'));
        }

    }

    static function lastWeek(){
        return array(new \DateTime('midnight -2 monday'), new \DateTime('midnight last monday'));
    }

    static function nextWeek(){
        return array(new \DateTime('midnight next monday'), new \DateTime('midnight +2 monday'));
    }

    static function thisMonth(){
        return array(new \DateTime('midnight first day of'), new \DateTime('midnight first day of next month'));
    }

    static function lastMonth(){
        return array(new \DateTime('midnight first day of last month'), new \DateTime('midnight first day of'));
    }

    static function nextMonth(){
        return array(new \DateTime('midnight first day of next month'), new \DateTime('midnight first day of +2 month'));
    }

    static function getRangeArray($field, $start=null, $end=null){

        if ($field == 'today') {
            return array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
        } elseif ($field == 'yesterday') {
            $yest = date('Y-m-d', strtotime('-1 day'));
            return array($yest . ' 00:00:00', $yest . ' 23:59:59');
        } elseif ($field == 'last7') {
            return array(date('Y-m-d 00:00:00', strtotime('-7 days')),date('Y-m-d 23:59:59'));
        } elseif ($field == 'last30') {
            return array(date('Y-m-d 00:00:00', strtotime('-30 days')),date('Y-m-d 23:59:59'));
        } elseif ($field == 'this_month') {
            return array(date('Y-m-') . '01', date('Y-m-d H:59:59'));
        } elseif ($field == 'last_month') {
            return array(date('Y-m-', strtotime('-1 month')) . '01', date('Y-m-t 23:59:59', strtotime('-1 month')));
        } elseif ($field == 'custom') {
            return array(date('Y-m-d H:i:s', strtotime($start)), date('Y-m-d H:i:s', strtotime($end)));
        }
    }



}
