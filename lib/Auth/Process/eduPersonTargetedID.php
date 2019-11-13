<?php

/**
 * Filter to generate the eduPersonTargetedID attribute.
 *
 * Es una extensión de core:TargetedID que permite elegir los metadatos a usar para generar
 * el identificador abstracto.
 *
 * Example - generate from user ID:
 * <code>
 * 'authproc' => [
 *   50 => 'core:TargetedID',
 * ]
 * </code>
 *
 * Example - generate from mail-attribute:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'core:TargetedID' , 'attributename' => 'mail', 
 *          'source_entity' =>'https://idpnew.uhu.es/idp ',
 *          'target_entity' => 'urn:target', 
 *   ],
 * ],
 * </code>
 *
 * @package SimpleSAMLphp
 * @author  Iñaki Fernández de Viana y González <i.fviana@dti.uhu.es>
 */

class sspmod_uhufilters_Auth_Process_eduPersonTargetedID extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $_target_entity = 'urn:federation:MicrosoftOnline';

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $_source_entity = 'https://idp.uhu.es/idp';
    
    /**
     * Initialize this filter.
     *
     * @param array $config   Configuration information about this filter.
     * @param mixed $reserved For future use.
     */
    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);

        assert(is_array($config));
        $this->_targetedId = new \SimpleSAML\Module\core\Auth\Process\TargetedID($config, $reserved);

        if (array_key_exists('source_entity', $config)) {
            $this->_source_entity = $config['source_entity'];
            if (!is_string($this->_source_entity)) {
                throw new Exception('Invalid value of \'source_entity \'- option to uhufilters:eduPersonTargetedID filter.');
            }
        }

        if (array_key_exists('target_entity', $config)) {
            $this->_target_entity = $config['target_entity'];
            if (!is_string($this->_target_entity)) {
                throw new Exception('Invalid value of \'target_entity\'-option to uhufilters:eduPersonTargetedID filter.');
            }
        }
    }

    /**
     * Apply filter to add the targeted ID.
     *
     * @param array $state The current state.
     */
    public function process(&$state)
    {
        $newstate = $state;
        $newstate['Source'] = [
            'metadata-set' => 'saml20-idp-hosted',
            'entityid' => $this->_source_entity ,
        ];
        $newstate['Destination'] = [
            'metadata-set' => 'saml20-sp-remote',
            'entityid' => $this->_target_entity,
        ];
        $result = $this->_targetedId->process($newstate);

        SimpleSAML\Logger::debug("uhufilters:eduPersonTargetedID: eduPersonTargetedID " . print_r($newstate['Attributes']['eduPersonTargetedID'],true));

        $state['Attributes']['eduPersonTargetedID'] = $newstate['Attributes']['eduPersonTargetedID'];
    }
}
