<?php
/* tom Express By Jack */

class ControllerExtensionModuleCedtom extends Controller
{
	private $error = array();
    private $helper;
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

        $this->load->library('cedtom');
        $this->helper = Cedtom::getInstance($this->registry);
    }
	
	public function index()
	{
        $data = array();

		$data = $this->language->load($this->module_path.'/cedtom');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model($this->module_path.'/cedtom');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (VERSION >= '3.0.0.0') {
                if($this->request->post['cedtom_status'])
                    $this->model_setting_setting->editSetting('module_cedtom', ['module_cedtom_status'=>1]);
                else
                    $this->model_setting_setting->editSetting('module_cedtom', ['module_cedtom_status'=>0]);
            }
            $this->model_extension_module_cedtom->saveSettings('cedtom', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success_settings');
        
            $this->response->redirect($this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }

        if(isset($this->session->data['warning'])){
            $data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        }elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success_message'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success_message'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token, 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->module_path.'', $this->session_token_key . '=' . $this->session_token, 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->module_path.'/cedtom', $this->session_token_key . '=' . $this->session_token, 'SSL')
        );

        $data['action'] = $this->url->link($this->module_path.'/cedtom', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL');

        if (isset($this->request->post['cedtom_status'])) {
            $data['cedtom_status'] = $this->request->post['cedtom_status'];
        } else {
            $data['cedtom_status'] = $this->config->get('cedtom_status');
        }

        if (isset($this->request->post['cedtom_api_url'])) {
            $data['cedtom_api_url'] = $this->request->post['cedtom_api_url'];
        } else {
            $data['cedtom_api_url'] = $this->config->get('cedtom_api_url');
        }

        if (isset($this->request->post['cedtom_email'])) {
            $data['cedtom_email'] = $this->request->post['cedtom_email'];
        } else {
            $data['cedtom_email'] = $this->config->get('cedtom_email');
        }

        if (isset($this->request->post['cedtom_password'])) {
            $data['cedtom_password'] = $this->request->post['cedtom_password'];
        } else {
            $data['cedtom_password'] = $this->config->get('cedtom_password');
        }

        if (isset($this->request->post['cedtom_token'])) {
            $data['cedtom_token'] = $this->request->post['cedtom_token'];
        } else {
            $data['cedtom_token'] = $this->config->get('cedtom_token');
        }

        $data['price_choices'] = array(
            '1'=>'Default Price',
            '2'=>'Increase By Fix Amount',
            '3'=>'Decrease By Fix Amount',
            '4'=>'Increase By Fix Percent',
            '5'=>'Decrease By Fix Percent',
        );

        if (isset($this->request->post['cedtom_price_choice'])) {
            $data['cedtom_price_choice'] = $this->request->post['cedtom_price_choice'];
        } else if ($this->config->get('cedtom_price_choice')) {
            $data['cedtom_price_choice'] = $this->config->get('cedtom_price_choice');
        } else {
            $data['cedtom_price_choice'] = '1';
        }

        if(isset($this->request->post['cedtom_variable_price'])){
            $data['cedtom_variable_price'] = $this->request->post['cedtom_variable_price'];
        }elseif($this->config->get('cedtom_variable_price')){
            $data['cedtom_variable_price'] = $this->config->get('cedtom_variable_price');
        }else{
            $data['cedtom_variable_price'] = '';
        }

        if(isset($this->request->post['cedtom_cron_token'])){
            $data['cedtom_cron_token'] = $this->request->post['cedtom_cron_token'];
        }elseif($this->config->get('cedtom_cron_token')){
            $data['cedtom_cron_token'] = $this->config->get('cedtom_cron_token');
        }else{
            $data['cedtom_cron_token'] = '';
        }

        $data['session_token_key'] = $this->session_token_key;
        $data['session_token'] = $this->session_token;

        $data['cedtom_image_cron'] = HTTP_CATALOG.'index.php?route=extension/module/cedtom/product/syncImages&token=';
        $data['cedtom_inventory_cron'] = HTTP_CATALOG.'index.php?route=extension/module/cedtom/product/syncInventory&token=';
        $data['cedtom_image_cron_time'] = '5 minutes';
        $data['cedtom_inventory_cron_time'] = '3 minutes';
        if(!$this->config->get('cedtom_license_validate')){
            $this->response->redirect($this->url->link($this->module_path.'/cedtom/licensePanel', $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }
        $data['cedtom_token'] = $this->config->get('cedtom_token');
        $data['cedtom_token_status'] = $this->config->get('cedtom_token_status');

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if(!$this->config->get('cedtom_license_validate')){
            $this->response->redirect($this->url->link('extension/module/cedtom/licensePanel', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path.'/cedtom', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path.'/cedtom.tpl', $data));
        }
	}

    public function authenticateDetails(){
        $json = array();

        if(isset($this->request->post['email']) && $this->request->post['email']) {
            if(isset($this->request->post['password']) && $this->request->post['password']) {
                $url = $this->request->post['api_url'].'api/twm/auth/authenticate';

                $params = array(
                    'email' =>  $this->request->post['email'],
                    'password' =>  html_entity_decode($this->request->post['password']),
                );

                $method = 'POST';

                $response = $this->getToken($url, $params);
                
                $json = json_decode($response, true);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getToken($api_url, $params){

        $body = json_encode($params);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            "Content-type: application/json",
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

	protected function validate()
    {
        if (!$this->user->hasPermission('modify', $this->module_path.'/cedtom')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function generateCronSecureKey() {
        $cron_secure_key = token(16);

        $json['success'] = true;
        $json['token'] = $cron_secure_key;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	public function install()
    {

        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->module_path.'/cedtom/product');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->module_path.'/cedtom/product');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->module_path.'/cedtom/order');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->module_path.'/cedtom/order');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->module_path.'/cedtom');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->module_path.'/cedtom');

        if (VERSION >= '2.0.0.0' && VERSION < '2.0.1.0') {
            // $this->load->model('tool/event');
            // $this->model_tool_event->addEvent('addToPofile_after_add_product', 'post.admin.product.add', $this->module_path.'/cedtom/addToProfile');
            // $this->model_tool_event->addEvent('addToPofile_after_edit_product', 'post.admin.product.edit', $this->module_path.'/cedtom/addToProfile');
        } elseif (VERSION >= '2.0.1.0' && VERSION <= '2.1.0.2') {
            // $this->load->model('extension/event');
            // $this->model_extension_event->addEvent('addToPofile_after_add_product', 'post.admin.product.add', $this->module_path.'/cedtom/addToProfile');
            // $this->model_extension_event->addEvent('addToPofile_after_edit_product', 'post.admin.product.edit', $this->module_path.'/cedtom/addToProfile');
        } elseif (VERSION >= '2.2.0.0' && VERSION < '3.0.0.0') {
            // $this->load->model('extension/event');
            // $this->model_extension_event->addEvent('cedtom_menu', 'admin/view/common/column_left/before', $this->module_path.'/cedtom/column_left/eventMenu');
            // $this->model_extension_event->addEvent('addToPofile_after_add_product', 'admin/model/catalog/product/addProduct/after', $this->module_path.'/cedtom/addToProfile');
            // $this->model_extension_event->addEvent('addToPofile_after_edit_product', 'admin/model/catalog/product/editProduct/after', $this->module_path.'/cedtom/addToProfile');
        } elseif (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            $this->model_setting_event->addEvent('cedtom_menu', 'admin/view/common/column_left/before', $this->module_path.'/cedtom/column_left/eventMenu');
            $this->model_setting_event->addEvent('cedtom_order_view', 'admin/view/sale/order_info/before', $this->module_path.'/cedtom/order/eventOrderView');
            $this->model_setting_event->addEvent('cedtom_product_edit', 'admin/view/catalog/product_form/before', $this->module_path.'/cedtom/product/eventProductEdit');
        }
        $this->helper->isNewSetup();
    }

    public function uninstall()
    {
       if (VERSION >= '2.0.0.0' && VERSION < '2.0.1.0') {
            // $this->load->model('tool/event');
            // $this->model_tool_event->deleteEvent('addToPofile_after_add_product');
            // $this->model_tool_event->deleteEvent('addToPofile_after_edit_product');
        } elseif (VERSION >= '2.0.1.0' && VERSION <= '2.1.0.2') {
            // $this->load->model('extension/event');
            // $this->model_extension_event->deleteEvent('addToPofile_after_add_product');
            // $this->model_extension_event->deleteEvent('addToPofile_after_edit_product');
        } elseif (VERSION >= '2.2.0.0' && VERSION < '3.0.0.0') {
            // $this->load->model('extension/event');
            // $this->model_extension_event->deleteEvent('cedtom_menu');
            // $this->model_extension_event->deleteEvent('addToPofile_after_add_product');
            // $this->model_extension_event->deleteEvent('addToPofile_after_edit_product');
        } elseif (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEventByCode('cedtom_menu');
            // $this->model_setting_event->deleteEvent('addToPofile_after_add_product');
            // $this->model_setting_event->deleteEvent('addToPofile_after_edit_product');
        }
    }

    public function validateLic(){
        $json = array();
        $this->language->load($this->module_path.'/cedtom');
        if(isset($this->request->post['lic_key']) && $this->request->post['lic_key']){
            $response = json_decode($this->ced_amazon_validate_licensce_callback($this->request->post['lic_key']) , true);
            
            $this->load->model($this->module_path.'/cedtom');
            if($response['response']){
                $this->model_extension_module_cedtom->add('cedtom_license_validate');
                $json = $response['response'];
            }else{
                $this->model_extension_module_cedtom->remove('cedtom_license_validate');
            }
        }

        if($this->config->get('cedtom_license_validate')){
            $json = $this->config->get('cedtom_license_validate');
        }

        if(isset($json) && empty($json)){
            $json = array('message' => $this->language->get('error_message'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function licensePanel(){
        $this->language->load($this->module_path.'/cedtom');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_agree'] = $this->language->get('entry_agree');
        $data['entry_lic_note'] = $this->language->get('entry_lic_note');
        $data['button_validate'] = $this->language->get('button_validate');
        $data['button_save_lic'] = $this->language->get('button_save_lic');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->module_path.'/cedtom', $this->session_token_key . '=' . $this->session_token, true)
        );

        $action = $this->url->link($this->module_path.'/cedtom', $this->session_token_key . '=' . $this->session_token, true);
        $data['action'] = html_entity_decode($action);
        $data['cancel'] = $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&type=module', true);
        $redirect_url = $this->url->link($this->module_path.'/cedtom/validateLic', $this->session_token_key . '=' . $this->session_token, true);
        $data['redirect_url'] = html_entity_decode($redirect_url);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path.'/cedtom/lic_panel', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path.'/cedtom/lic_panel.tpl', $data));
        }
    }

    function ced_amazon_validate_licensce_callback($license_key)
    {
        $return_response = array();
        $license_arg = array();
        $license_arg['domain_name'] = $_SERVER['HTTP_HOST'];
        $license_arg['module_name'] = 'Ced_tom';
        $license_arg['version'] = VERSION;
        $license_arg['php_version'] = phpversion();
        $license_arg['framework'] = 'opencart';
        $license_arg['admin_name'] = $_SERVER['SERVER_NAME'];
        $license_arg['admin_email'] = $_SERVER['SERVER_ADMIN'];
        $license_arg['module_license'] = $license_key;
        $license_arg['edition'] = '';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://cedcommerce.com/licensing/validate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $license_arg);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        $res = curl_exec($ch);
        
        curl_close($ch);
        $response = json_decode($res, true);
        
        $ced_hash = '';
        if(isset($response['hash']) && isset($response['level']))
        {
            $ced_hash = $response['hash'];
            $ced_level = $response['level'];
            {
                $i=1;
                for($i=1;$i<=$ced_level;$i++)
                {
                $ced_hash = base64_decode($ced_hash);
                }
                }
            }
    
            $ced_response = json_decode($ced_hash, true);
    
            if($ced_response['domain'] == $_SERVER['HTTP_HOST'] && $ced_response['license'] == $license_arg['module_license'] && $ced_response['module_name'] == $license_arg['module_name'])
            {
                $return_response['response'] = true;
            }
            else
            {
                $return_response['response'] = true;
            }
            
            return json_encode($return_response);
    }
}