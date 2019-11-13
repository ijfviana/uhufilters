<?php

/**
Join array elements with a string
 * Filter to join a array of attributes with a string.
 *
 * By default, this filter will generate the ID based on the UserID of the current user.
 * This is by default generated from the attribute configured in 'userid.attribute' in the
 * metadata. If this attribute isn't present, the userid will be generated from the
 * eduPersonTargetID attribute, if it is present.
 *
 * It is possible to generate this attribute from another attribute by specifying this attribute
 * in this configuration.
 *
 * Example - generate from firstname-attribute and lastname-attribute and join them using " ":
 * <code>
 * 'authproc' => array(
 *   50 => ['class'         => 'uhufilter:Implode' , 
 *          'attributename' => 'eduPersonTargetedID',
 *          'pieces'       => array('firstname','lastname'); 
 * )
 * </code>
 * 
 * Example - generate from user ID:
 * <code>
 * 'authproc' => array(
 *   50 => ['class'         => 'uhufilter:Implode' , 
 *          'attributename' => 'eduPersonTargetedID',
 *          'sanitize'      => true;
 *           'glue'         => " ";
 *           'pieces'       => array('firstname','lastname'); 
 * )
 * </code>
 *
 * @author Olav Morken, UNINETT AS.
 * @package simpleSAMLphp
 */
class sspmod_uhufilters_Auth_Process_Implode extends \SimpleSAML\Auth\ProcessingFilter
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
    private $sanitize = true;

	
	private $glue = " ";
	
	/* The array of strings to implode.*/
	private $pieces= array();

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
                throw new \Exception('Invalid attribute name given to implode:Implode filter.');
            }
        }

        if (array_key_exists('sanitize', $config)) {
            $this->sanitize = $config['sanitize'];
   	        $ok =filter_var($this->sanitize, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($ok == NULL) {
               $this->sanitize = false;
            }
        }
        
        
        if (array_key_exists('glue', $config)) {
            $this->glue = $config['glue'];
            if (!is_string($this->glue)) {
                throw new \Exception('Invalid value of \'glue\'-option to implode:Implode filter.');
            }
        }
        
        
        if (array_key_exists('pieces', $config)) {
            $this->pieces = $config['pieces'];
            if (!is_array($this->pieces)) {
                throw new \Exception('Invalid value of \'pieces\'-option to implode:Implode filter.');
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

		$values = array();
		foreach ($this->pieces as $value) {
		
			 if (array_key_exists($value, $state['Attributes']) && is_string($state['Attributes'][$value][0])) 
			 {
				$val=$state['Attributes'][$value][0];
			
				if ($this->sanitize)				
					$val=ucwords(strtolower($val));
				$values[]=$val;
			}
		}
		

       	$attributes =& $state['Attributes'];
                 
        $attributes[$this->attribute] = array(implode ( $this->glue , $values));
    }
}
