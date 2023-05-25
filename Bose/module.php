<?php

/** @noinspection PhpUnused */

/**
 * @project       Durchsage/Bose
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

include_once __DIR__ . '/helper/BDS_autoload.php';

class BoseDurchsage extends IPSModule
{
    //Helper
    use BDS_config;
    use BDS_deactivation;
    use BDS_playNotification;
    use BDS_triggerCondition;
    use BDS_webHook;
    const BOSE_SOUNDTOUCH_VALUE = 0;
    const BOSE_SWITCHBOARD_VALUE = 1;
    const WEBHOOK_GUID = '{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}';
    const AWS_POLLY_GUID = '{6EFA02E1-360F-4120-B3DE-31EFCDAF0BAF}';
    const BOSE_SOUNDTOUCH_GUID = '{4836EF46-FF79-4D6A-91C9-FE54F1BDF2DB}';
    const BOSE_SWITCHBOARD_GUID = '{3A8BE899-3400-6755-BCAB-375C47D9451E}';

    //Constants
    private const MODULE_NAME = 'Bose Durchsage';
    private const MODULE_PREFIX = 'BDS';
    private const MODULE_VERSION = '1.0-1, 24.05.2023';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ##### Properties

        $this->RegisterPropertyString('Host', (count(Sys_GetNetworkInfo()) > 0) ? Sys_GetNetworkInfo()[0]['IP'] : '');
        $this->RegisterPropertyInteger('AWSPolly', 0);
        $this->RegisterPropertyInteger('OutputType', self::BOSE_SOUNDTOUCH_VALUE);
        $this->RegisterPropertyInteger('OutputDevice', 0);
        $this->RegisterPropertyInteger('Volume', 15);
        $this->RegisterPropertyBoolean('UseAutomaticDeactivation', false);
        $this->RegisterPropertyString('AutomaticDeactivationStartTime', '{"hour":22,"minute":0,"second":0}');
        $this->RegisterPropertyString('AutomaticDeactivationEndTime', '{"hour":6,"minute":0,"second":0}');
        $this->RegisterPropertyString('TriggerList', '[]');

        ##### Variables

        //Active
        $id = @$this->GetIDForIdent('Active');
        $this->RegisterVariableBoolean('Active', 'Aktiv', '~Switch', 10);
        $this->EnableAction('Active');
        if (!$id) {
            $this->SetValue('Active', true);
        }

        //TTS text
        $id = @$this->GetIDForIdent('TTSText');
        $this->RegisterVariableString('TTSText', 'Text', '', 20);
        $this->EnableAction('TTSText');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('TTSText'), 'Edit');
        }

        ##### Timer

        $this->RegisterTimer('StartAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StartAutomaticDeactivation(' . $this->InstanceID . ');');
        $this->RegisterTimer('StopAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StopAutomaticDeactivation(' . $this->InstanceID . ',);');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        $this->UnregisterHook('/hook/BoseDurchsage/' . $this->InstanceID);
    }

    /**
     * @throws Exception
     */
    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        //Never delete this line!
        parent::ApplyChanges();

        //Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        $this->RegisterHook('/hook/BoseDurchsage/' . $this->InstanceID);

        //Delete all references
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Delete all messages
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                if ($message == VM_UPDATE || $message == EM_UPDATE) {
                    $this->UnregisterMessage($senderID, $message);
                }
            }
        }

        if (!$this->ValidateConfiguration()) {
            return;
        }

        //Register references and messages
        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                            $this->RegisterReference($id);
                            $this->RegisterMessage($id, VM_UPDATE);
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($variable['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($variable['SecondaryCondition'], true);
                if (array_key_exists(0, $secondaryConditions)) {
                    if (array_key_exists('rules', $secondaryConditions[0])) {
                        $rules = $secondaryConditions[0]['rules']['variable'];
                        foreach ($rules as $rule) {
                            if (array_key_exists('variableID', $rule)) {
                                $id = $rule['variableID'];
                                if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                                    $this->RegisterReference($id);
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->SetAutomaticDeactivationTimer();
        $this->CheckAutomaticDeactivationTimer();
    }

    /**
     * @throws Exception
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        if (!empty($Data)) {
            foreach ($Data as $key => $value) {
                $this->SendDebug(__FUNCTION__, 'Data[' . $key . '] = ' . json_encode($value), 0);
            }
        }
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

            case VM_UPDATE:

                //$Data[0] = actual value
                //$Data[1] = value changed
                //$Data[2] = last value
                //$Data[3] = timestamp actual value
                //$Data[4] = timestamp value changed
                //$Data[5] = timestamp last value

                if (!$this->CheckMaintenance()) {
                    //Check trigger conditions
                    $valueChanged = 'false';
                    if ($Data[1]) {
                        $valueChanged = 'true';
                    }
                    $scriptText = self::MODULE_PREFIX . '_CheckTriggerConditions(' . $this->InstanceID . ', ' . $SenderID . ', ' . $valueChanged . ');';
                    @IPS_RunScriptText($scriptText);
                }
                break;

        }
    }

    #################### Request Action

    /**
     * @throws Exception
     */
    public function RequestAction($Ident, $Value)
    {
        $this->SetValue($Ident, $Value);
        switch ($Ident) {

            case 'Active':
                $this->SetValue($Ident, $Value);
                break;

            case 'TTSText':
                $this->Play($Value);
                break;
        }
    }

    #################### Private

    /**
     * @throws Exception
     */
    private function KernelReady(): void
    {
        $this->ApplyChanges();
    }

    /**
     * @throws Exception
     */
    private function ValidateConfiguration(): bool
    {
        $result = true;
        $status = 102;
        if (!$this->CheckAWSPolly() || !$this->CheckOutputDevice()) {
            $result = false;
            $status = 104;
        }
        $this->SetStatus($status);
        return $result;
    }

    private function CheckMaintenance(): bool
    {
        $result = false;
        if (!$this->GetValue('Active')) {
            $this->SendDebug(__FUNCTION__, 'Abbruch, die Instanz ist inaktiv!', 0);
            $result = true;
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    private function CheckAWSPolly(): bool
    {
        $id = $this->ReadPropertyInteger('AWSPolly');
        if ($id == 0 || @!IPS_ObjectExists($id)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('Abort, Text-to-Speech (AWS Polly) is invalid!'), 0);
            return false;
        }
        return true;
    }

    /**
     * @throws Exception
     */
    private function CheckOutputDevice(): bool
    {
        $id = $this->ReadPropertyInteger('OutputDevice');
        if ($id == 0 || @!IPS_ObjectExists($id)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('Abort, output device is invalid!'), 0);
            return false;
        }
        return true;
    }
}