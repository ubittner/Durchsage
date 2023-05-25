<?php

/**
 * @project       Durchsage/Bose/helper
 * @file          BDS_playNotification.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection HttpUrlsUsage */

declare(strict_types=1);

trait BDS_playNotification
{
    /**
     * Plays an audio notification.
     *
     * @param string $Text
     * @return bool
     * false =  an error occurred,
     * true =   successful
     *
     * @throws Exception
     */
    public function Play(string $Text): bool
    {
        if ($this->CheckMaintenance()) {
            return false;
        }
        if (!$this->CheckAWSPolly()) {
            return false;
        }
        if (!$this->CheckOutputDevice()) {
            return false;
        }
        $result = false;
        //Providing audio data to the WebHook
        $this->SetBuffer('AudioData', base64_decode(@TTSAWSPOLLY_GenerateData($this->ReadPropertyInteger('AWSPolly'), $Text)));
        //Play
        $outputDevice = $this->ReadPropertyInteger('OutputDevice');
        switch ($this->ReadPropertyInteger('OutputType')) {
            case self::BOSE_SOUNDTOUCH_VALUE:
                //BST_PlayAudioNotification has no result at the moment, so we set it always to true
                $result = true;
                BST_PlayAudioNotification($outputDevice, 'Audio Notification', sprintf('http://%s:3777/hook/BoseDurchsage/%s/notification.mp3', $this->ReadPropertyString('Host'), $this->InstanceID), $this->ReadPropertyInteger('Volume'));
                break;

            case self::BOSE_SWITCHBOARD_VALUE:
                $result = BOSESB_PlayDeviceAudioNotification($outputDevice, sprintf('http://%s:3777/hook/BoseDurchsage/%s/notification.mp3', $this->ReadPropertyString('Host'), $this->InstanceID), $this->ReadPropertyInteger('Volume'));
                break;
        }
        return $result;
    }
}