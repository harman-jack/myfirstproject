<?php
/* tom By Jack */
class ControllerExtensionModuleCedtomColumnLeft extends Controller
{
	private $session_token_key = '';
    private $session_token = '';
    private $module_path = '';

    public function __construct($registry)
    {
        // pass `$registry` to parent `__construct`
        parent::__construct($registry);

         if (VERSION >= 2.0 && VERSION <= 2.2) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            $this->extension_path = 'extension/module';
            $this->module_path = 'module';
        } else if (VERSION < 3.0) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            $this->extension_path = 'extension/extension';
            $this->module_path = 'extension/module';
        } else {
            $this->session_token_key = 'user_token';
            $this->session_token = $this->session->data['user_token'];

            $this->extension_path = 'marketplace/extension';
            $this->module_path = 'extension/module';
        }
    }

	public function eventMenu($route, &$data)
	{
		
		$this->load->language('extension/module/cedtom');
				
		// Google Express Menu
		$menu = array();

		if ($this->user->hasPermission('access', 'extension/module/cedtom/product')) {

			$product_menu[] = array(
				'name'	   => $this->language->get('text_product_list'),
				'href'     => $this->url->link('extension/module/cedtom/product', $this->session_token_key .'='. $this->session_token, true),
				'children' => array()		
			);

			$product_menu[] = array(
				'name'	   => $this->language->get('text_product_importer'),
				'href'     => $this->url->link('extension/module/cedtom/product/importer', $this->session_token_key .'='. $this->session_token, true),
				'children' => array()		
			);

			$menu[] = array(
				'name'	   => $this->language->get('text_product'),
				'href'     => '',
				'children' => $product_menu		
			);
		}
		
		if ($this->user->hasPermission('access', 'extension/module/cedtom/order')) {
			$menu[] = array(
				'name'	   => $this->language->get('text_order'),
				'href'     => $this->url->link('extension/module/cedtom/order', $this->session_token_key .'='. $this->session_token, true),
				'children' => array()		
			);
		}

		if ($this->user->hasPermission('access', 'extension/module/cedtom')) {
			$menu[] = array(
				'name'	   => $this->language->get('text_configuration'),
				'href'     => $this->url->link('extension/module/cedtom', $this->session_token_key .'='. $this->session_token, true),
				'children' => array()		
			);
		}


		$data['menus'][] = array(
			'id'       => 'menu-tom',
			'icon'	   => 'fa-text-width',
			'name'	   => $this->language->get('text_cedtom'),
			'href'     => '',
			'children' => $menu
		);

	}
}