<?php

/**
 * Filter to generate .....
 *
 * * Example - generate from user ID:
 * <code>
 *  * 'authproc' => [
 *   50 => 'uhufilters:displayName',
 * ]
 * 
 * Example - generate from mail-attribute:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'uhufilters:displayName' , 
 *      'attributename' => 'eduPersonTargetedID'],
 *      'sn1' => array("apellido1","schacSn1");
 *      'sn2' => array("apellido2","schacSn2");
 *      'sn'  => array("apellidos","surname");
 *      'gn'  => array("nombre","gn")];
 *
 * ]
 * </code>
 *
 * @author Iñaki Fernández de Viana y González, UHU.
 * @package simpleSAMLphp
 */
class sspmod_uhufilters_Auth_Process_displayName extends \SimpleSAML\Auth\ProcessingFilter
{

    private $_sanitize = true;
    private $_sn1= array("apellido1","schacSn1");
    private $_sn2= array("apellido2","schacSn2");
    private $_sn= array("apellidos","surname");
    private $_gn= array("nombre","gn");

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

        if (array_key_exists('sanitize', $config)) {
            $this->_sanitize = $config['sanitize'];
            if (!is_bool($this->_sanitize)) {
                throw new \Exception('Invalid value of \'sanitize\'-option to uhufilters:displayName filter.');
            }
            SimpleSAML\Logger::error($mesg);
        }
                
        if (array_key_exists('gn', $config)) {
            $this->_gn = $config['gn'];
            if (!is_array($this->_gn)) {
                throw new \Exception('Invalid value of \'gn\'-option to uhufilters:displayName filter.');
            }
        }

        if (array_key_exists('sn', $config)) {
            $this->_sn = $config['sn'];
            if (!is_array($this->_sn)) {
                throw new \Exception('Invalid value of \'sn\'-option to uhufilters:displayName filter.');
            }
        }

        if (array_key_exists('sn1', $config)) {
            $this->_sn1 = $config['sn1'];
            if (!is_array($this->_sn1)) {
                throw new \Exception('Invalid value of \'sn1\'-option to uhufilters:displayName filter.');
            }
        }

        if (array_key_exists('sn2', $config)) {
            $this->_sn2 = $config['sn2'];
            if (!is_array($this->_sn2)) {
                throw new \Exception('Invalid value of \'sn2\'-option to uhufilters:displayName filter.');
            }
        }
    }

    /** */
    private function _find_first($here, $options)
    {
        $result = null;

        foreach ($options as $value)
        {
            print_r($value);
            
            if (array_key_exists($value, $here) &&  is_string($here[$value][0])) {                
                $result = $here[$value][0];
                break; 
            }
        }

        return $result;
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

        $nombre = $this->_find_first($attributes, $this->_gn);
   
        if (!is_string($nombre))  {
            $nombre = "--";
            SimpleSAML\Logger::warning("uhufilters:Decode: gn attribute not found ");
        }

        $apellidos = $this->_find_first($attributes, $this->_sn);
    
        if (!is_string($apellidos))
        {
            SimpleSAML\Logger::warning("uhufilters:Decode: sn attribute not found ");

            $ape1 = $this->_find_first($attributes, $this->_sn1);
            
            if (!is_string($ape1))
            {
                SimpleSAML\Logger::warning("uhufilters:Decode: sn1 attribute not found ");
                $ape1 = "--";
            }

            $apellidos = $ape1;

            $ape2 = $this->_find_first($attributes, $this->_sn2);
            if (!is_string($ape2)) {
                SimpleSAML\Logger::warning("uhufilters:Decode: sn2 attribute not found ");
                $ape2 = "--";
            }

            $apellidos = $apellidos . " " . $ape2;
        }
        
        $displayname =  $apellidos . ", " . $nombre;
        
        SimpleSAML\Logger::debug("uhufilters:Decode: " . $displayname);

       
        if ($this->_sanitize){
            $displayname = ucwords(strtolower($displayname));
        }
        $attributes["displayName"] =  array ($displayname); 
    }
}