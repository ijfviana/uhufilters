<?php

/**
 * Filter that returns the anticode associate with a code
 *
 *
 * Example - generate from mail-attribute:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'immutableid_Auth:ImmutableID' , 
 *          'attributename' => 'eduPersonTargetedID'
 *          'decode' => array('estudiante' => 'student', 'profesor' => 'faculty')
 *          'code' => 'attribute_target'
 *          'default' => 'noname'
 * ],
 * ],
 * </code>
 *
 * @author Iñaki Ferández de Viana González, UHU.
 * @package simpleSAMLphp
 */

class sspmod_uhufilters_Auth_Process_Decode extends \SimpleSAML\Auth\ProcessingFilter
{
    /** 
     * Nombre del campo que contiene el código a decodificar
     */
    private $_attributename;

    /**
     * Asociative array to decode the anticode 
     */
    private $_decode;

    /**
     * Default value if the code do not exist
     *
     * @var [type]
     */
    private $_default;

    /**
     * Nombre del atributo que contiene el código a decodificar
     */
    private $_code;

    /**
     * 
     */

    private $_casesensitive = false;


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
            $this->_attributename = $config['attributename'];
            if (!is_string($this->_attributename)) {
                throw new \Exception('Invalid attributename given to uhufilters:Decode filter.');
            }
        }

        if (array_key_exists('decode', $config)) {
            $this->_decode = $config['decode'];
            if (!is_array($this->_decode)) {
                throw new \Exception('Invalid decode given to uhufilters:Decode filter.');
            }
        }

        if (array_key_exists('code', $config)) {
            $this->_code = $config['code'];
            if (!is_string($this->_code)) {
                throw new \Exception('Invalid code given to uhufilters:Decode filter.');
            }
        }

        if (array_key_exists('default', $config)) {
            $this->_default = $config['default'];
            if (!is_string($this->_default)) {
                throw new \Exception('Invalid default given to uhufilters:Decode filter.');
            }
        }

        if (array_key_exists('casesensitive', $config)) {
            $this->_casesensitive = $config['casesensitive'];
            if (!is_bool($this->_casesensitive)) {
                throw new \Exception('Invalid casesensitive given to uhufilters:Decode filter.');
            }
        }

        if (!$this->_casesensitive)
            $this->_decode = array_change_key_case($this->_decode,CASE_UPPER);
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

        SimpleSAML\Logger::warning("uhufilters:Decode: $this->_code in " . print_r($attributes,true));

        
        if (array_key_exists($this->_code, $attributes)) {
            $o = $attributes[$this->_code][0];
            
            if (!$this->_casesensitive)
                $o = strtoupper($o);

            if (is_string($o) && array_key_exists($o, $this->_decode)) {
                SimpleSAML\Logger::warning("uhufilters:Decode: code value  " . $o . " found in " . print_r($this->_decode,true) . ", decoded to ". $this->_decode[$o]);

                $attributes[$this->_attributename] = array($this->_decode[$o]);
            }
            else
            {
                $attributes[$this->_attributename] = array($this->_default);
                SimpleSAML\Logger::warning("uhufilters:Decode: code value  " . $o . " not found in " . print_r($this->_decode,true) );
            }
        }
        else
            SimpleSAML\Logger::warning("uhufilters:Decode: attribute" . $this->_code . "  not found");
    }
}