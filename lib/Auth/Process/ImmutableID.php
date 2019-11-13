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
 *   50 => ['class' => 'immutableid_Auth:ImmutableID' , 'attributename' => 'eduPersonTargetedID'],
 * ],
 * </code>
 *
 * @author Olav Morken, UNINETT AS.
 * @package simpleSAMLphp
 */
      
class sspmod_uhufilters_Auth_Process_ImmutableID extends \SimpleSAML\Auth\ProcessingFilter
{

    /**
     * The attribute we should generate the targeted id from, or NULL if we should use the
     * UserID.
     */
    private $attribute = null;


    /**
     * Whether the attribute should be generated as a NameID value, or as a simple string.
     *
     * @var boolean
     */
    private $generateNameId = false;


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

        if (array_key_exists('attributename', $config)) {
            $this->attribute = $config['attributename'];
            if (!is_string($this->attribute)) {
                throw new \Exception('Invalid attribute name given to immutableid:ImmutableID filter.');
            }
        }

        if (array_key_exists('nameId', $config)) {
            $this->generateNameId = $config['nameId'];
            if (!is_bool($this->generateNameId)) {
                throw new \Exception('Invalid value of \'nameId\'-option to immutableid:ImmutableID filter.');
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

        if ($this->attribute === null) {
            if (!array_key_exists('UserID', $state)) {
                throw new \Exception('immutableid:ImmutableID: Missing UserID for this user. Please' .
                    ' check the \'userid.attribute\' option in the metadata against the' .
                    ' attributes provided by the authentication source.');
            }

            $userID = $state['UserID'];
        } else {
            if (!array_key_exists($this->attribute, $state['Attributes'])) {
                throw new \Exception('immutableid:ImmutableID: Missing attribute \'' . $this->attribute .
                '\', which is needed to generate the immutable ID.' . print_r($state['Attributes'], true));
            }

            $userID = $state['Attributes'][$this->attribute][0];
        }


        $chunks = str_split(md5($userID), 4);
        $uid    = vsprintf("%s%s-%s-%s-%s-%s%s%s", $chunks);

        if ($this->generateNameId) {
            /* Convert the targeted ID to a SAML 2.0 name identifier element. */
            $nameId = [
                'Format' => \SAML2\Constants::NAMEID_PERSISTENT,
                'Value' => $uid,
            ];

            if (isset($state['Source']['entityid'])) {
                $nameId['NameQualifier'] = $state['Source']['entityid'];
            }
            if (isset($state['Destination']['entityid'])) {
                $nameId['SPNameQualifier'] = $state['Destination']['entityid'];
            }

            $doc = new \DOMDocument();
            $root = $doc->createElement('root');
            $doc->appendChild($root);

            \SAML2\Utils::addNameId($root, $nameId);
            $uid = $doc->saveXML($root->firstChild);
        }
        $attributes =& $state['Attributes'];
                 
        $attributes['ImmutableID'] = [$uid];
    }
}
