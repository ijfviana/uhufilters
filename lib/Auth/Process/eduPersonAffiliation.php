<?php
/**
 * Filter to generate the AzudeAD ImmutableID  attribute.
 *
 * By default, this filter will generate the ID based on the UserID of the current user.
 * This is by default generated from the attribute configured in 'userid.attribute' in the
 * metadata. If this attribute isn't present, the userid will be generated from the
 * eduPersonTargetID attribute, if it is present.
 *
 * It is possible to generate this attribute from another attribute by specifying this attribute
 * in this configuration.
 *
 * Example - generate from user ID:
 * <code>
 * 'authproc' => array(
 *   50 => 'immutableid_Auth:ImmutableID',
 * )
 * </code>
 *
 * Example - generate from mail-attribute:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'uhufilters:eduPersonAffiliation' , 'attributename' => 'eduPersonTargetedID'],
 * ],
 * </code>
 *
 * @author Olav Morken, UNINETT AS.
 * @package simpleSAMLphp
 */
class sspmod_uhufilters_Auth_Process_eduPersonAffiliation extends \SimpleSAML\Auth\ProcessingFilter
{
    private $_decode;

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

        $config = array(
            'attributename' => 'eduPersonAffiliation',
            'decode' => array(
                "PAS" => "staff",
                "PDI" => "faculty",
                "ALUMNO" => "student"
            ),
            'code' => 'uhuScope',
            'default' => 'walk-in'
        );
        

        $this->_decode = new sspmod_uhufilters_Auth_Process_Decode($config,null);
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
       
        $this->_decode->process($state);                 
    }
}