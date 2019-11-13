<?php

/**
 * Filter to generate the schacPersonalUniqueID  attribute. Este atributo contiene in Identificador legal único de la persona como, por ejemplo, el DNI. Seguirá
 * el formato:
 * 
 * urn:mace:terena.org:schac:personalUniqueID:es:NIF:31241312L
 * 
 * Donde <tipodocumento> representa el tipo de documento y <numdocumento> es el número de documento único. Por defecto, estos valores se optionen de los atributos
 * tipodocumento y uhuUserDni.
 * 
 * Más información en;
 *     http://wiki.rediris.es/gtschema/index.php/Iriseduperson#schacPersonalUniqueID
 *     http://www.terena.org/activities/tf­emc2/docs/schac/schac­schema­IAD1.4.0.pdf 
 * 
 * 
 * Example - generate from user ID:
 * <code>
 * 'authproc' => array(
 *   50 => 'uhufilters:schacPersonalUniqueID',
 * )
 * </code>
 *
 * Example - generate from mail-attribute:
 * <code>
 * 'authproc' => [
 *   50 => ['class' => 'uhufilters:schacPersonalUniqueID' , 
 *          'typedocument' => 'tipodocumento',
 *          'document' => 'uhuUserDni'
 *  ],
 * ],
 * </code>
 *
 * @author Iñaki Fernández de Viana y Gonzále, UHU.
 * @package simpleSAMLphp
 */
class sspmod_uhufilters_Auth_Process_schacPersonalUniqueID extends \SimpleSAML\Auth\ProcessingFilter
{
    /**
     * 
     */
    private $_PREFIX = "urn:mace:terena.org:schac:personalUniqueID:es";

    /**
     * 
     */
    private $_typedocument = null;

    /**
     * 
     */
    private $_document = true;

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

        if (array_key_exists('typedocument', $config)) {
            $this->_typedocument = $config['typedocument'];
            if (!is_string($this->_typedocument)) {
                throw new \Exception('Invalid value of \'document\'-option to uhufilters:schacPersonalUniqueID filter.');
            }
        }

        if (array_key_exists('document', $config)) {
            $this->_document = $config['document'];
            if (!is_string($this->_document)) {
                throw new \Exception('Invalid value of \'document\'-option to uhufilters:schacPersonalUniqueID filter.');
            }
        }
    }
    
    /**
     * Undocumented function
     *
     * @param  array $state The current state. 
     * @return void
     */
    public function process(&$state)
    {
        assert(is_array($state));
        assert(array_key_exists("Attributes", $state));

        $attributes =& $state['Attributes'];
        
        if (array_key_exists($this->_typedocument,$attributes) 
            && is_string($attributes[$this->_typedocument][0])){
            $typedocument = $attributes[$this->_typedocument][0];
        } else {
            throw new \Exception('Attribute ' . $this->_typedocument . ' not found.');
        }

        if (array_key_exists($this->_document,$attributes) 
            && is_string($attributes[$this->_document][0])){
            $document = $attributes[$this->_document][0];
        } else {
            throw new \Exception('Attribute ' . $this->_document . ' not found.');
        }
        
        $values = array ($this->_PREFIX, $typedocument, $document);
        $attributes["schacPersonalUniqueID"] = array(implode(":", $values));
    }
}
