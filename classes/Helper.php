<?php

/**
 * Created by PhpStorm.
 * User: xitroff
 * Date: 05.08.16
 * Time: 21:47
 */
class Helper
{
    const ONE_WEEK = 604800;
    const ONE_MINUTE = 60;
    const TELEGRAM_BOT_TOKEN = '5023f24a7acd655f947e64577a2b7cafd72c2ee848e558ff323fdf3d8bf1353c969a62beeebc8cabefd38aef746f4b1a6f317a0cdc0ed29cf38ed7356ddb5e2f';
    
    public static function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    public static function generateTokenValue()
    {
        return self::generatePassword(160);
    }

    public static function requestApi($method, array $parameters)
    {
        $url = $_SERVER['HTTP_HOST'].'/api/?action='.$method;
        $ch = curl_init( $url );

        $curlOptions = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        );

        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
    }

    public static function sendTelegramBotMessage($telegramUserId, $telegramChatId, $listName, $noteText)
    {
        $token = self::TELEGRAM_BOT_TOKEN;
        $url = 'https://telegram.hitrov.com/todo_hitrov_com_bot/?token='.$token;
        $ch = curl_init( $url );

        $text = 'TODO.hitrov.com notification: List: '.$listName.', Text: '.$noteText;

        $parameters = array(
            'toDoHitrovComRequest' => 1,
            'telegramUserId' => $telegramUserId,
            'telegramChatId' => $telegramChatId,
            'text' => $text,
        );

        $curlOptions = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    
    public static function getFormattedDate($date)
    {
        $formattedDate = '';
        $datePattern = '/^([\d]{4})-([\d]{2})-([\d]{2})$/';
        $matches = array();
        if (!empty($date) && preg_match($datePattern, $date, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $formattedDate = $month . '/' . $day . '/' . $year;
        }
        return $formattedDate;
    }

    public static function getFormattedTime($time)
    {
        $formattedTime = '';
        $timePattern = '/^([\d]{2}):([\d]{2}):[\d]{2}$/';
        $matches = array();
        if (!empty($time) && preg_match($timePattern, $time, $matches)) {
            $hours = $matches[1];
            $minutes = $matches[2];
            $formattedTime = $hours . ':' . $minutes;
        }
        return $formattedTime;
    }
}