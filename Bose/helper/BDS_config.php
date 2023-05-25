<?php

/**
 * @project       Durchsage/Bose/helper
 * @file          BDS_config.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

trait BDS_config
{
    /**
     * Modifies a configuration button.
     *
     * @param string $Field
     * @param string $Caption
     * @param int $ObjectID
     * @return void
     */
    public function ModifyButton(string $Field, string $Caption, int $ObjectID): void
    {
        $state = false;
        if ($ObjectID > 1 && @IPS_ObjectExists($ObjectID)) { //0 = main category, 1 = none
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', $Caption);
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $ObjectID);
    }

    /**
     * Updates the output type.
     *
     * @param int $OutputType
     * 0 =  Bose SoundTouch,
     * 1 =  Bose Switchboard (Home/Smart Speaker)
     *
     * @return void
     */
    public function UpdateOutputType(int $OutputType): void
    {
        switch ($OutputType) {
            case self::BOSE_SOUNDTOUCH_VALUE:
                $this->UpdateFormField('OutputDevice', 'moduleID', self::BOSE_SOUNDTOUCH_GUID);
                $this->UpdateFormField('OutputDevice', 'value', 0);
                break;

            case self::BOSE_SWITCHBOARD_VALUE:
                $this->UpdateFormField('OutputDevice', 'moduleID', self::BOSE_SWITCHBOARD_GUID);
                $this->UpdateFormField('OutputDevice', 'value', 0);
                break;

        }
    }

    /**
     * Modifies a trigger list configuration button
     *
     * @param string $Field
     * @param string $Condition
     * @return void
     */
    public function ModifyTriggerListButton(string $Field, string $Condition): void
    {
        $id = 0;
        $state = false;
        //Get variable id
        $primaryCondition = json_decode($Condition, true);
        if (array_key_exists(0, $primaryCondition)) {
            if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                    $state = true;
                }
            }
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $id . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $id);
    }

    /**
     * Gets the configuration form.
     *
     * @return false|string
     * @throws Exception
     */
    public function GetConfigurationForm()
    {
        $form = [];

        ########## Elements

        //Logo
        $form['elements'][] = [
            'type'  => 'Image',
            'image' => 'data:image/png;base64,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'
        ];

        //Module name
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => self::MODULE_NAME
        ];

        //Version
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => 'Version: ' . self::MODULE_VERSION
        ];

        //Spacer
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => ' '
        ];

        //Host
        $form['elements'][] = [
            'type'    => 'ValidationTextBox',
            'name'    => 'Host',
            'caption' => 'Symcon IP-Adresse '
        ];

        //AWS Polly
        $id = $this->ReadPropertyInteger('AWSPolly');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'  => 'RowLayout',
            'items' => [
                [
                    'type'     => 'SelectModule',
                    'name'     => 'AWSPolly',
                    'caption'  => 'Text-to-Speech (AWS Polly)',
                    'moduleID' => self::AWS_POLLY_GUID,
                    'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "AWSPollyConfigurationButton", "ID " . $AWSPolly . " Instanzkonfiguration", $AWSPolly);'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'ID ' . $id . ' Instanzkonfiguration',
                    'name'     => 'AWSPollyConfigurationButton',
                    'visible'  => $enableButton,
                    'objectID' => $id
                ]
            ]
        ];

        //Output type
        $options = [];
        $options[] = [
            'value'   => self::BOSE_SOUNDTOUCH_VALUE,
            'caption' => 'SoundTouch'
        ];
        $options[] = [
            'value'   => self::BOSE_SWITCHBOARD_VALUE,
            'caption' => 'Home/Smart Speaker'
        ];
        $form['elements'][] = [
            'type'     => 'Select',
            'name'     => 'OutputType',
            'caption'  => 'Ausgabetyp',
            'options'  => $options,
            'onChange' => self::MODULE_PREFIX . '_UpdateOutputType($id, $OutputType);'
        ];

        //Output device
        $id = $this->ReadPropertyInteger('OutputDevice');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'  => 'RowLayout',
            'items' => [
                [
                    'type'     => 'SelectModule',
                    'name'     => 'OutputDevice',
                    'caption'  => 'Ausgabegerät',
                    'moduleID' => $this->ReadPropertyInteger('OutputType') === self::BOSE_SOUNDTOUCH_VALUE ? self::BOSE_SOUNDTOUCH_GUID : self::BOSE_SWITCHBOARD_GUID,
                    'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "OutputDeviceConfigurationButton", "ID " . $OutputDevice . " Instanzkonfiguration", $OutputDevice);'
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'caption'  => 'ID ' . $id . ' Instanzkonfiguration',
                    'name'     => 'OutputDeviceConfigurationButton',
                    'visible'  => $enableButton,
                    'objectID' => $id
                ]
            ]
        ];

        //Volume
        $form['elements'][] = [
            'type'    => 'NumberSpinner',
            'name'    => 'Volume',
            'caption' => 'Lautstärke',
            'minimum' => 0,
            'maximum' => 100
        ];

        //Spacer
        $form['elements'][] = [
            'type'    => 'Label',
            'caption' => ' '
        ];

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Deaktivierung',
            'items'   => [
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseAutomaticDeactivation',
                    'caption' => 'Automatische Deaktivierung'
                ],
                [
                    'type'    => 'SelectTime',
                    'name'    => 'AutomaticDeactivationStartTime',
                    'caption' => 'Startzeit'
                ],
                [
                    'type'    => 'SelectTime',
                    'name'    => 'AutomaticDeactivationEndTime',
                    'caption' => 'Endzeit'
                ]
            ]
        ];

        //Trigger list
        $triggerListValues = [];
        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $variable) {
            $triggerID = 0;
            $conditions = true;
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $triggerID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($triggerID <= 1 || !@IPS_ObjectExists($triggerID)) { //0 = main category, 1 = none
                            $conditions = false;
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
                                if ($id <= 1 || !@IPS_ObjectExists($id)) { //0 = main category, 1 = none
                                    $conditions = false;
                                }
                            }
                        }
                    }
                }
            }
            $rowColor = '#FFC0C0'; //red
            if ($conditions) {
                $rowColor = '#C0FFC0'; //light green
                if (!$variable['Use']) {
                    $rowColor = '#DFDFDF'; //grey
                }
            }
            $triggerListValues[] = ['ID' => $triggerID, 'rowColor' => $rowColor];
        }

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Auslöser',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'TriggerList',
                    'rowCount' => 15,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Variable ID',
                            'name'    => 'ID',
                            'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "TriggerListConfigurationButton", $TriggerList["PrimaryCondition"]);',
                            'width'   => '120px',
                            'add'     => ''
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "TriggerListConfigurationButton", $TriggerList["PrimaryCondition"]);',
                            'width'   => '200px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerPrimaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Bedingung:',
                            'name'    => 'LabelPrimaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'Label',
                                'italic' => true,
                                'bold'   => true
                            ]
                        ],
                        [
                            'caption' => 'Mehrfachauslösung',
                            'name'    => 'UseMultipleAlerts',
                            'width'   => '200px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'PrimaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectCondition'
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerSecondaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Weitere Bedingung(en):',
                            'name'    => 'LabelSecondaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'Label',
                                'italic' => true,
                                'bold'   => true
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SecondaryCondition',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type'  => 'SelectCondition',
                                'multi' => true
                            ]
                        ],
                        [
                            'caption' => ' ',
                            'name'    => 'SpacerNotification',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'Label'
                            ]
                        ],
                        [
                            'caption' => 'Benachrichtigung:',
                            'name'    => 'LabelNotification',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'Label',
                                'italic' => true,
                                'bold'   => true
                            ]
                        ],
                        [
                            'caption' => 'Sprachnachricht',
                            'name'    => 'VoiceMessage',
                            'width'   => '300px',
                            'add'     => '',
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Lautstärke',
                            'name'    => 'Volume',
                            'width'   => '120px',
                            'add'     => 15,
                            'edit'    => [
                                'type'    => 'NumberSpinner',
                                'minimum' => 0,
                                'maximum' => 100
                            ]
                        ]
                    ],
                    'values' => $triggerListValues,
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'TriggerListConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        ########## Actions

        $form['actions'][] = [
            'type' => 'TestCenter',
        ];

        $form['actions'][] = [
            'type'    => 'Label',
            'caption' => ' ',
        ];

        //Registered messages
        $registeredMessages = [];
        $messages = $this->GetMessageList();
        foreach ($messages as $id => $messageID) {
            $name = 'Objekt #' . $id . ' existiert nicht';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($id)) {
                $name = IPS_GetName($id);
                $rowColor = '#C0FFC0'; //light green
            }
            switch ($messageID) {
                case [10001]:
                    $messageDescription = 'IPS_KERNELSTARTED';
                    break;

                case [10603]:
                    $messageDescription = 'VM_UPDATE';
                    break;

                default:
                    $messageDescription = 'keine Bezeichnung';
            }
            $registeredMessages[] = [
                'ObjectID'           => $id,
                'Name'               => $name,
                'MessageID'          => $messageID,
                'MessageDescription' => $messageDescription,
                'rowColor'           => $rowColor];
        }

        $form['actions'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Registrierte Nachrichten',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredMessages',
                    'rowCount' => 10,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " aufrufen", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " aufrufen", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Nachrichten ID',
                            'name'    => 'MessageID',
                            'width'   => '150px'
                        ],
                        [
                            'caption' => 'Nachrichten Bezeichnung',
                            'name'    => 'MessageDescription',
                            'width'   => '250px'
                        ]
                    ],
                    'values' => $registeredMessages
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredMessagesConfigurationButton',
                    'caption'  => 'Aufrufen',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        foreach ($references as $reference) {
            $name = 'Objekt #' . $reference . ' existiert nicht';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($reference)) {
                $name = IPS_GetName($reference);
                $rowColor = '#C0FFC0'; //light green
            }
            $registeredReferences[] = [
                'ObjectID' => $reference,
                'Name'     => $name,
                'rowColor' => $rowColor];
        }

        $form['actions'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Registrierte Referenzen',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredReferences',
                    'rowCount' => 10,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " aufrufen", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " aufrufen", $RegisteredReferences["ObjectID"]);'
                        ]
                    ],
                    'values' => $registeredReferences
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredReferencesConfigurationButton',
                    'caption'  => 'Aufrufen',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        ########## Status

        $form['status'][] = [
            'code'    => 101,
            'icon'    => 'active',
            'caption' => self::MODULE_NAME . ' wird erstellt',
        ];
        $form['status'][] = [
            'code'    => 102,
            'icon'    => 'active',
            'caption' => self::MODULE_NAME . ' ist aktiv',
        ];
        $form['status'][] = [
            'code'    => 103,
            'icon'    => 'active',
            'caption' => self::MODULE_NAME . ' wird gelöscht',
        ];
        $form['status'][] = [
            'code'    => 104,
            'icon'    => 'inactive',
            'caption' => self::MODULE_NAME . ' ist inaktiv',
        ];
        $form['status'][] = [
            'code'    => 200,
            'icon'    => 'inactive',
            'caption' => 'Es ist Fehler aufgetreten, weitere Informationen unter Meldungen, im Log oder Debug!',
        ];

        return json_encode($form);
    }
}