<?php

/**
 * Filter to generate the schacPersonalUniqueCode  attribute. Especifica un valor único para la entrada a la que se asocia.. This attribute
 * follows the syntax:
 *
 * urn:schac:personalUniqueCode:es:uhu:<affiliation>:<id>
 * 
 * where affliation es la afilición que tiene el usuario en la universidad (faculty,
 * student, staff) and id es un identificador opaco, único y persistente dentro de la
 * institución.
 * 
 * Examples:
 *
 * personalUniqueCode:es:uhu:student:a3b123c12
 * 
 * References:
 * 
 * http://wiki.rediris.es/gtschema/index.php/Iriseduperson#schacPersonalUniq
 * 
 * By default, this filter will generate the ID based on the attributes uhuUniqueId and 
 * eduPersonPrimaryAffiliation of the current user.
 *  
 * It is possible to generate this attribute from another attribute by specifying these attributes
 * in this configuration.
 *
 * Example - generate from user uhuUniqueId and  eduPersonPrimaryAffiliation:
 * <code>
 * 'authproc' => array(
 *   50 => 'uhufilters:schacPersonalUniqueCode',
 * )
 * </code>
 *
 * Example - generate from eduPersonPrincipalName and affiliation_usr:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'uhufilters:schacPersonalUniqueCode', 
 *          'uid' => 'eduPersonPrincipalName',
 *          'affiliation' =>'affiliation_usr'
 *         ],
 * ],
 * </code>
 *
 * @author Iñaki Fernández de Viana y González, UHU.
 * @package simpleSAMLphp
 */

class sspmod_uhufilters_Auth_Process_schacPersonalUniqueCode extends \SimpleSAML\Auth\ProcessingFilter
{

    private $_PREFIX = "urn:schac:personalUniqueCode:es:uhu";
    
    private $_uid = 'uhuUniqueId';
    private $_affiliation = 'eduPersonPrimaryAffiliation';

    /**
     * Initialize this filter.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);

        assert(is_array($config));

        if (array_key_exists('uid', $config)) {
            $this->_uid = $config['uid'];
            if (!is_string($this->_uid)) {
                throw new \Exception('Invalid attribute uid given to uhufilters:schacPersonalUniqueCode filter.');
            }
        }
             
        if (array_key_exists('affiliation', $config)) {
            $this->$_affiliation = $config['affiliation'];
            if (!is_string($this->$_affiliation)) {
                throw new \Exception('Invalid value of \'affiliation\'-option to uhufilters:schacPersonalUniqueCode filter.');
            }
        }
    }

    /**
     * Apply filter to add the ImmutableID.
     *
     * @param array &$state  The current state.
     */
    public function process(&$state)
    {
        assert(is_array($state));
        assert(array_key_exists("Attributes", $state));

        $attributes =& $state['Attributes'];
        $uid = $this->_uid;
        $aff = $this->_affiliation;

        if (!array_key_exists($uid, $attributes) ) {
            $mesg = "Error while provisioning schacPersonalUniqueCode: $uid required, not presented";
            SimpleSAML\Logger::error($mesg);
             throw new \SimpleSAML\Error\Exception($mesg);
        }

        if (!array_key_exists($aff, $attributes) ) {
            $mesg = "Error while provisioning schacPersonalUniqueCode: $aff required, not presented";
            SimpleSAML\Logger::error($mesg);
             throw new \SimpleSAML\Error\Exception($mesg);
        }

     
        $id = preg_replace("/" . "@uhu.es" . "$/", " ", $attributes[$uid][0]); 
        $eduPersonPrimaryAffiliation = $attributes[$aff][0];

        $values = array ($this->_PREFIX, $eduPersonPrimaryAffiliation, $id); 
        
        $attributes["schacPersonalUniqueCode"] =  array(implode(":", $values));
    }
}