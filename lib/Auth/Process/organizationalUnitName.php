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

class sspmod_uhufilters_Auth_Process_organizationalUnitName extends \SimpleSAML\Auth\ProcessingFilter
{
    private $_decode;

    /** 
     * Nombre del campo que contiene el código de la unidad organizativa
     */
    private $_ouname ="ou";

    /**
     * The attribute we should generate the targeted id from, or NULL if we should use the
     * UserID.
     */
    private $_mappings = array(
        "admin_paspdi" => "TODO",
        "aldoc" => "Alumnos de Doctorado (extinto)",
        "atmos" => "Servicio de pronóstico del tiempo UHU",
        "biblio" => "Biblioteca",
        "c3it" => "Centro de Investigación Internacional en Inteligencia Territorial",
        "ca" => "Colectivos Asociados",
        "caruh" => "Consejo de Alumnos y Representantes de la Universidad de Huelva (CARUH)",
        "chac" => "Curso de Lengua y Cultura China",
        "ciecema" => "Centro Internacional de Estudios y Convenciones Ecológicas y Medioambientales",
        "cop" => "Copisterias",
        "corp" => "Corporaciones",
        "cursos" => "Generico de cursos no reglados",
        "cv" => "Servicio de Enseñanza Virtual",
        "dam" => "Departamento Anton Menger",
        "dbasp" => "Departamento de Biología Ambiental y Salud Pública",
        "dcaf" => "Departamento de Ciencias Agroforestales",
        "dciphi" => "Antiguo Departamento de Derecho (extinto)",
        "ddcc" => "Departamento de Didactica de las Ciencias y Filosofia",
        "ddtss" => "Departamento de Derecho del trabajo y Seguridad Social (extinto)",
        "decd" => "Departamento de Economia Financiera, Contabilidad y Direccion de Operaciones",
        "dedu" => "Departamento de Educación",
        "dege" => "Departamento de Economia General y Estadistica",
        "dehie" => "Departamento de Economia e Historia de las Instituciones Económicas (Extinto)",
        "dem" => "Departamento de Empresas y Marketing",
        "dempc" => "Departamento de Expresion Musical, Plastica, Corporal y sus Didacticas",
        "denf" => "Departamento de Enfermeria",
        "dfa" => "Departamento de Fisica Aplicada",
        "dfaie" => "Departamento de Fisica Aplicacda e Ingenieria Electrica (Extinto)",
        "dfesp" => "Departamento de Filología Española",
        "dfing" => "Departamento de Filología Inglesa",
        "dfint" => "Departamento de filologías Integradas",
        "dgeo" => "Departamento de Geologia",
        "dgf" => "Departamento de Geografía (extinto)",
        "dgyp" => "Departamento de Geodinamica y Paleontologia",
        "dhis1" => "Departamento de Historia I",
        "dhis2" => "Departamento de Historia II",
        "didp" => "Departamento de Ingenieria de Diseño y Proyectos",
        "die" => "Departamento de Ingenieria Electrica",
        "diesia" => "Departamento de Ingeniería Electrónica, Sistemas Informáticos y Automáticai (extinto)",
        "dimme" => "Departamento de Ingenieria Minera, Mecanica y Energetica",
        "diq" => "Departamento de Ingeniería Química",
        "dmat" => "Departamento de Matemáticas",
        "dmce" => "Departamento de Métodos Cuantitativos para la Economía y la Empresa, Estadística e Investigación Operativa",
        "dpces" => "Departamento de Psicologia Clinica, Experimental y Social",
        "dpee" => "Departamento de Psicologia Evolutiva y de la Educación",
        "dppt" => "Departamento de Derecho Penal (extinto)",
        "dpsi" => "Departamento de Psicología",
        "dpub" => "Departamento de Derecho Público",
        "dqcm" => "Departamento de Quimica y Ciencias de los Materiales",
        "dstso" => "Departamento de Sociologia y Trabajo Social",
        "dthm" => "Departamento Theodor Mommsen",
        "dti" => "Departamento de Tecnologías de la Información",
        "eduh" => "Servicio de Educacion a Distancia Universidad de Huelva",
        "enfe" => "Facultad de Enfermeria",
        "eps" => "Escuela Politecnica Superios (extinto)",
        "erel" => "Escuela de Relaciones Laborales (extinto)",
        "etsi" => "Escuela Técnica Superior de Ingeniería",
        "etso" => "Escuela de Trabajo Social (extinto)",
        "euts" => "Escuela Universitaria de Trabajo Social",
        "fcct" => "Facultad de Ciencias del Trabajo",
        "fder" => "Facultad de Derecho",
        "fedu" => "Facultad de Ciencias de la Edución",
        "femp" => "Facultad de Ciencias Empresariales",
        "fexp" => "Facultad de Ciencias Experimentas",
        "fhum" => "Facultad de Humanidades",
        "inv" => "Servicio de Investigacion",
        "lista" => "Listas de correo",
        "ofitec" => "Oficina Tecnica",
        "ole" => "Observatorio Local de Empleo",
        "orientadores" => "Servicio de Orientacion UHU",
        "otri" => "Oficina de Transferencia de Resultados de Investigación",
        "pas" => "Personal de Administracion y Servicios (extinto)",
        "pi" => "Personal Invitado",
        "sacu" => "Servicio de Atencion a la Comunidad Universitaria",
        "sc" => "Servicios Centrales",
        "sdc" => "Sindicatos",
        "sic" => "Servicio de Informática y Comunicaciones",
        "spub" => "Servicio de Pulicaciones",
        "uhu" => "Universidad de Huelva",
    );

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

        if (array_key_exists('ouname', $config)) {
            $this->_ouname = $config['ouname'];
            if (!is_string($this->attribute)) {
                throw new \Exception('Invalid ouname given to uhufilters:organizationalUnitName filter.');
            }
        }

        $config = array(
            'attributename' => 'organizationalUnitName',
            'decode' => $this->_mappings,
            'code' => $this->_ouname,
            'default' => 'none'
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
