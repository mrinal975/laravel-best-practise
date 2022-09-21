<?php

namespace App\Utility;

use \Datetime;

class Clock {
    public static function renderTimeStamp($dtime) {
        return strtotime($dtime);
    }

    public static function renderDateTime($time_stamp, $fmt = 'Y-m-d H:i:s') {
        return date($fmt, $time_stamp);
    }

    public static function timeDiff($star_time, $end_time) {
        $start_time = new DateTime($star_time);
        $end_time = new DateTime($end_time);
        $interval = $start_time->diff($end_time);
        $hours   = $interval->format('%h'); 
        $minutes = $interval->format('%i');
        $second = $interval->format('%s');
        return $hours.':'.$minutes.':'.$second;
    }

    public static function sumTime($times) {
        $seconds = 0;
        foreach($times as $t){
            $timeArr = array_reverse(explode(":", $t));
            foreach ($timeArr as $key => $value){
                if ($key > 2) break;
                $seconds += pow(60, $key) * $value;
            }
        }
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours*3600)) / 60);
        $secs = floor($seconds % 60);

        return $hours.':'.$mins.':'.$secs;
    }

    public static function numberOfTimeBetweenDate($start_date, $end_date) {
        $start_date = new DateTime($start_date);
        $end_date = new DateTime($end_date);
        $diff = $start_date->diff($end_date);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s
        ];
    }

    public static function convertDateTime($date_time) {
        return date("Y-m-d H:i:s", strtotime($date_time)) ;
    }
}