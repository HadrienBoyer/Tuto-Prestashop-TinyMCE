<?php
/**
  * Demo Tuto TinyMCE pour Prestashop 1.5 & 1.6 - demotutotinymce.php
  * 
  * Auteur: Hadrien Boyer
  * Website: http://hadri.info
  * Date: 28/01/2015
  * Version: 0.1
  */

if (!defined('_PS_VERSION_'))
    exit;

class DemoTutoTinymce extends Module
{
    public function __construct()
    {
        $this->name = 'demotutotinymce';
        $this->tab = 'front_office_features';
        $this->version = '0.1';
        $this->author = 'Hadrien Boyer';
        $this->need_instance = 0;

        parent::__construct();
	
		$this->context->controller->addCSS(($this->_path).'demotutotinymce.css', 'all');

        $this->displayName = $this->l('Demo Tuto TinyMCE');
        $this->description = $this->l('Demo du Tuto pour ajouter des champs TinyMCE dans un module Prestashop.');
    }

  // :: Install : on enregistre le Hook "home" si il n'existe pas (peu probable, mais on ne sait jamais…)
    public function install()
    {
        if (!parent::install() OR !$this->registerHook('home'))
            return false;
        return true;
    }

  // :: Uninstall, no comment ;)
    public function uninstall()
    {
        if (!parent::uninstall())
            return false;
        return true;
    }

  // :: Hook Home : c'est là où sera affiché notre Bloc de texte (en Homepage)
    function hookHome($params)
	  {
    // On assigne la variable "CHAMP_TINYMCE", utilisée dans le fichier TPL demotutotinymce.tpl
      $this->context->smarty->assign(array('CHAMP_TINYMCE' => Configuration::get('CHAMP_TINYMCE')));

    // On indique que le fichier TPL suivant sera le fichier de Template à afficher (il contient notre Bloc de texte)
      return $this->display(__FILE__, 'demotutotinymce.tpl', $this->getCacheId());
    }

  // :: Ici on indique le contenu de la page "Configuration" du plugin, et ses messages (erreur & succès)
    public function getContent()
    {
        $output = '<h2>'.$this->displayName.'</h2>';

      // Si on envoie le formulaire "submitFormChamps"…
        if (Tools::isSubmit('submitFormChamps'))
        {
        // On récupère l'id des languages sur la variable $lang
          foreach (Language::getLanguages(false) as $lang) 
          // On assigne CHAMP_TINYMCE à la value de text_X (X = numéro de votre langue, ici "1")
            Configuration::updateValue('CHAMP_TINYMCE', Tools::getValue('text_'.(int)$lang['id_lang']), true); // IMPORTANT: true = autoriser les balises HTML dans la BDD !

          // Si il y a erreur…
            if (isset($errors) AND sizeof($errors))
                $output .= $this->displayError(implode('<br />', $errors));
          // Sinon, success ! :) On affiche donc le message de confirmation d'enregistrement du texte dans la BDD
            else
                $output .= $this->displayConfirmation($this->l('Modifications enregistrées'));
        }
      // On affiche le formulaire avec le champ TinyMCE via $this->displayForm();
        return $output.$this->displayForm();
    }


    public function displayForm()
    {
        
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

    $fields_form = array(
      'tinymce' => true,
      'legend' => array(
      'title' => $this->l('Champ TinyMCE'),
      ),
      'input' => array(
        'CHAMP_TINYMCE' => array(
          'type' => 'hidden',
          'name' => 'CHAMP_TINYMCE'
        ),
        'content' => array(
          'type' => 'textarea',
          'label' => $this->l('Texte du Champ'),
          'lang' => true,
          'name' => 'text',
          'cols' => 40,
          'rows' => 10,
          'class' => 'rte',
          'autoload_rte' => true,
        ),
      ),
      'submit' => array(
        'title' => $this->l('Save'),
      )
    );

      $helper = new HelperForm();
      $helper->module = $this;
      $helper->name_controller = 'demotutotinymce';
      $helper->identifier = $this->identifier;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      foreach (Language::getLanguages(false) as $lang)
        $helper->languages[] = array(
          'id_lang' => $lang['id_lang'],
          'iso_code' => $lang['iso_code'],
          'name' => $lang['name'],
          'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
        );

      $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
      $helper->default_form_language = $default_lang;
      $helper->allow_employee_form_lang = $default_lang;
      $helper->toolbar_scroll = true;
      $helper->title = $this->displayName;
      $helper->submit_action = 'submitFormChamps';

      $helper->fields_value = $this->getFormValues();

    return $helper->generateForm(array(array('form' => $fields_form)));
    }


    public function getFormValues()
    {
      $fields_value = array();

    // On charge le Texte sauvegardé dans la colonne "value" de la Table "ps_configuration"
      $CHAMP_TINYMCE = Configuration::get('CHAMP_TINYMCE');

      foreach (Language::getLanguages(false) as $lang) // Si besoin d'utiliser la traduction
          $fields_value['text'][(int)$lang['id_lang']] = Tools::getValue('text_'.(int)$lang['id_lang'], '');

    // On load le contenu du textarea ("text_X", text_1 pour FR) qui servira à charger le contenu du champ TINYMCE
      $fields_value['text'][(int)$lang['id_lang']] = $CHAMP_TINYMCE;

    return $fields_value;
    }

}
