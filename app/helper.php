<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

if ( ! function_exists('formatEventDateTimeSchedule')) {
    /**
     * Format event date and time schedule.
     *
     * @param  string  $start_time  The start time of the event
     * @param  string  $end_time  The end time of the event
     * @return string Formatted date and time schedule
     *
     * @throws InvalidFormatException
     */
    function formatEventDateTimeSchedule(string $start_time, string $end_time): string
    {
        $start = Carbon::parse($start_time);
        $end = Carbon::parse($end_time);

        $format_time = 'H:i';
        $format_date = 'd M Y';

        $start_date = $start->format($format_date);
        $end_date = $end->format($format_date);
        $start_time = $start->format($format_time);
        $end_time = $end->format($format_time);

        if ($start_date === $end_date) {
            return sprintf('%s : %s - %s', $start_date, $start_time, $end_time);
        }

        return sprintf('Start: %s %s / End: %s %s', $start_date, $start_time, $end_date, $end_time);
    }
}
