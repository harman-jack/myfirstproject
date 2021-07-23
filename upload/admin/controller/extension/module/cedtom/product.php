<?php

/**
 * 
 */
class ControllerExtensionModuleCedtomProduct extends Controller
{
	private $error = array();
	private $helper;
	private $session_token_key = '';
	private $session_token = '';
	private $module_path = '';
	private $product_data;

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

		$this->product_data = array();

		$this->load->library('cedtom');
		$this->helper = Cedtom::getInstance($this->registry);
	}

	public function index()
	{
		if (isset($this->session->data['tom_product_edit'])) {
			unset($this->session->data['tom_product_edit']);
		}

		$this->load->language('extension/module/cedtom/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/cedtom/product');

		$this->getList();
	}

	protected function getList()
	{
		$data = array();

		$data = $this->load->language('extension/module/cedtom/product');

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = null;
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$filter_tom_status = $this->request->get['filter_tom_status'];
		} else {
			$filter_tom_status = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . $url, true)
		);

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  	=> $filter_name,
			'filter_model'	  	=> $filter_model,
			'filter_price'	  	=> $filter_price,
			'filter_quantity' 	=> $filter_quantity,
			'filter_status'   	=> $filter_status,
			'filter_tom_status' => $filter_tom_status,
			'sort'            	=> $sort,
			'order'           	=> $order,
			'start'           	=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           	=> $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');
		$this->load->model('extension/module/cedtom/product');

		$product_total = $this->model_extension_module_cedtom_product->getTotalProducts($filter_data);

		$results = $this->model_extension_module_cedtom_product->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$product_specials = $this->model_extension_module_cedtom_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}

			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'tom_status' => $result['tom_status'],
				'edit'       => $this->url->link('catalog/product/edit', $this->session_token_key . '=' . $this->session_token . '&product_id=' . $result['product_id'] . $url, true),
			);
		}

		$data['session_token_key'] = $this->session_token_key;
		$data['session_token'] = $this->session_token;

		if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];

			unset($this->session->data['warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=p.model' . $url, true);
		$data['sort_price'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=p.price' . $url, true);
		$data['sort_quantity'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=p.status' . $url, true);
		$data['filter_tom_status'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=fp.status' . $url, true);
		$data['sort_order'] = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$data['statuses'] = array('Imported', 'Updated', 'Removed');

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;
		$data['filter_tom_status'] = $filter_tom_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (VERSION >= '2.2.0.0') {
			$this->response->setOutput($this->load->view('extension/module/cedtom/product_list', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/module/cedtom/product_list.tpl', $data));
		}
	}

	public function importer()
	{
		$data = array();

		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}

		$this->load->model('setting/store');

		$data['segments'] = $this->getSegments();

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/stock_status');

		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->load->model('localisation/length_class');

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$data['session_token_key'] = $this->session_token_key;
		$data['session_token'] = $this->session_token;

		if (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];

			unset($this->session->data['warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('extension/module/cedtom/product/importProduct', $this->session_token_key . '=' . $this->session_token, true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (VERSION >= '2.2.0.0') {
			$this->response->setOutput($this->load->view('extension/module/cedtom/product_importer', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/module/cedtom/product_importer.tpl', $data));
		}
	}

	protected function getSegments() {

		$data = [];

		$url = 'api/twm/segments';

        $method = 'GET';

        $response = $this->helper->curlRequest($url, '', $method);

        if(isset($response['data'])) {
        	return $response['data'];
        }	

        return array();
    }

	public function importProductBySegment()
	{
		$this->load->model('extension/module/cedtom/product');

		$data = array();
		$this->product_data = array();

		$json = array();

		if(isset($this->request->post['segments']) && $this->request->post['segments']) {

			$url = 'api/twm/segment/'.$this->request->post['segments'].'&page='.$this->request->get['page'];

			$method = "GET";
			
			$response = $this->helper->curlRequest($url, [], $method);

			if (isset($response['data']) && $response['data']) {
				
				foreach ($response['data'] as $product) {
					 
					$is_imported = $this->model_extension_module_cedtom_product->isImported($product['id'], $product);
					
					if (!$is_imported) {
						$this->prepareProductData($product, $this->request->post);
					}
				}
				
				if (!empty($this->product_data)) {
					$this->model_extension_module_cedtom_product->addTomProducts($this->product_data);
				}
			}

			if(isset($response['meta']['current_page'])){
				$json['current_page'] = $response['meta']['current_page'];
			}else{
				$json['current_page'] = 0;
			}
			if(isset($response['meta']['last_page'])){
				$json['last_page'] = $response['meta']['last_page'];
			}else{
				$json['last_page'] = 0;
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function importProduct()
	{
		$this->load->model('extension/module/cedtom/product');

		$data = array();
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}

		if(isset($this->request->post['tom_id']) && $this->request->post['tom_id']) {

			$url = 'api/twm/products/'.$this->request->post['tom_id'];

			$method = "GET";
			
			$response = $this->helper->curlRequest($url, [], $method);

			if (isset($response['data']) && $response['data']) {
					 
				$is_imported = $this->model_extension_module_cedtom_product->isImported($response['data']['id'], $response['data']);
				
				if (!$is_imported) {
					$this->prepareProductData($response['data'], $this->request->post);
				}
				
				if (!empty($this->product_data)) {
					$this->model_extension_module_cedtom_product->addTomProducts($this->product_data);
				}

				$this->session->data['success'] = "Product Imported Successfully.";
			}

		}else{
			$this->session->data['warning'] = "TWN item id missing!";
		}

		$this->importer();
	}

	public function downloadImages()
	{
		$data = array();
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}

		$json = array();

		if (isset($this->request->post['selected']) && !empty($this->request->post['selected'])) {

			foreach ($this->request->post['selected'] as $product_id) {
				$this->syncImages($product_id);
			}

			$this->session->data['success'] = '';

			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		} else {
			$this->session->data['warning'] = $this->language->get('upload_error');
			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function syncImages($product_id)
	{
		$data = array();
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}

		$data = $this->language->get('error');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cedtom_product_image` WHERE product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$temp = array();
			$ids = array();

			foreach ($query->rows as $row) {
				$ids[] = $row['id'];
				$image_url = $row['image'];
				$tom_product_id = str_replace('', '_', $row['tom_product_id']);

				$exploded_data = explode('.', $image_url);
				$image_extension = end($exploded_data);

				$save_to = $this->prepareurl($tom_product_id, $image_extension, $row['product_id'], $row['sort_order']);
				$this->helper->grab_image($image_url, DIR_IMAGE . $save_to);

				if ($row['sort_order']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET product_id = '" . (int)$row['product_id'] . "', image = '" . $this->db->escape($save_to) . "', sort_order = '" . (int)$row['sort_order'] . "'");
				} else {
					$this->db->query("UPDATE `" . DB_PREFIX . "product` SET image = '" . $this->db->escape($save_to) . "' WHERE product_id = '" . (int)$row['product_id'] . "'");
				}

				$temp[] = $row['product_id'] . '_image_' . $row['sort_order'];
			}

			$this->db->query("DELETE FROM `" . DB_PREFIX . "cedtom_product_image` WHERE `id` IN (" . implode(',', $ids) . ")");
			$data = implode('<br/>', $temp) . '<br/>' . $this->language->get('images_success');
		} else {
			$data = $this->language->get('not_found');
		}
	}

	protected function prepareurl($tom_product_id, $image_extension, $product_id, $sort_order)
	{

		$local_image_path = 'catalog/products/' . $tom_product_id . '_' . $product_id . '_' . $sort_order . '.' . $image_extension;

		if (!is_dir(DIR_IMAGE . 'catalog/products')) {
			@mkdir(DIR_IMAGE . 'catalog/products', 0777, true);
		}

		$fp = fopen(DIR_IMAGE . $local_image_path, "wb");
		fclose($fp);

		return $local_image_path;
	}

	public function updateInventory()
	{
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}
		$json = array();
		$this->session->data['warning'] = '';
		if (isset($this->request->post['selected']) && !empty($this->request->post['selected'])) {

			$status = $this->syncInventory($this->request->post['selected']);

			$this->session->data['success'] = $status;

			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		} else {
			$this->session->data['warning'] = $this->language->get('update_error');
			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function syncInventory($product_ids)
	{
		$data = array();
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}

		$data = '';
		$temp = array();

		$initial_qty = isset($this->request->post['requested_amount']) ? $this->request->post['requested_amount'] : 100;

		foreach ($product_ids as $product_id) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cedtom_product` WHERE product_id = '" . $product_id . "'")->row;

			if (isset($query['product_id']) && $query['product_id']) {

				$url = 'api/twm/stock/' . $query['tom_product_id'];

				$method = "GET";

				$response = $this->helper->curlRequest($url, array(), $method);

				if (isset($response['product_id'])) {
					
					$this->db->query("UPDATE `" . DB_PREFIX . "cedtom_product` SET quantity_update = 1 WHERE product_id = '" . (int)$product_id . "'");
					$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = '" . $response['stock'] . "' WHERE product_id = '" . (int)$product_id . "'");
					$temp[] = $product_id;
				}
			} else {
				$data .= $this->language->get('Product_Not_Found') . $product_id . '<br>';
			}
		}
		if (!empty($temp)) {
			$data .= implode('<br/>', $temp) . '<br/>' . $this->language->get('sync_success');
		}

		return $data;
	}

	public function removeProduct()
	{
		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/product');
		} else {
			$this->load->language('extension/module/cedtom/product');
		}
		$json = array();
		if (isset($this->request->post['selected']) && !empty($this->request->post['selected'])) {

			$this->load->model('extension/module/cedtom/product');
			$this->model_extension_module_cedtom_product->removeTomItems($this->request->post['selected']);

			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		} else {
			$this->session->data['warning'] = $this->language->get('delete_error');
			$json = array('success' => true, 'reload' => $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, 'SSL'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function prepareProductData($product = array(), $post_data)
	{

		$this->product_data[$product['id']] = array();

		$this->product_data[$product['id']]['sku'] = $product['id'];

		$this->product_data[$product['id']]['ean'] = $product['ean'];
		
		$this->product_data[$product['id']]['model'] = $product['product_family'] ? $product['product_family'] : $product['id'];
		
		$this->product_data[$product['id']]['price'] = $product['consumer_price'];

		$this->product_data[$product['id']]['quantity'] = $product['stock'];

		$this->product_data[$product['id']]['height'] = '';

		$this->product_data[$product['id']]['length_class_id'] = isset($post_data['tax_class_id']) ? $post_data['tax_class_id'] : 0;

		/*Store image url as it is and process them by using cron*/
		if (isset($product['media']) && !empty($product['media'])) {
			foreach ($product['media'] as $image) {
				if($image['type'] == 'image'){
					$this->product_data[$product['id']]['images'][] = $image['url'];
				}
			}
		}

		// Set Product Name
		if(isset($product['name_en']) && $product['name_en']) {
			$this->product_data[$product['id']]['description']['en-gb']['name'] = $product['name_en'];
		}

		if(isset($product['name_nl']) && $product['name_nl']) {
			$this->product_data[$product['id']]['description']['nl']['name'] = $product['name_nl'];
		}

		if(isset($product['name_de']) && $product['name_de']) {
			$this->product_data[$product['id']]['description']['de']['name'] = $product['name_de'];
		}

		// Set Product Description
		if(isset($product['name_en']) && $product['name_en']) {
			$this->product_data[$product['id']]['description']['en-gb']['description'] = $product['description_en'];
		}

		if(isset($product['name_nl']) && $product['name_nl']) {
			$this->product_data[$product['id']]['description']['nl']['description'] = $product['description_nl'];
		}

		if(isset($product['name_de']) && $product['name_de']) {
			$this->product_data[$product['id']]['description']['de']['description'] = $product['description_de'];
		}

		// Set Product Categories
		foreach($product['categories'] as $category) {
			if(isset($category['name_en']) && $category['name_en']) {
				$this->product_data[$product['id']]['categories']['en-gb']['name'] = $category['name_en'];
			}

			if(isset($category['name_nl']) && $category['name_nl']) {
				$this->product_data[$product['id']]['categories']['nl']['name'] = $category['name_nl'];
			}

			if(isset($category['name_de']) && $category['name_de']) {
				$this->product_data[$product['id']]['categories']['de']['name'] = $category['name_de'];
			}
		}

		// Set Product Attributes
		foreach($product['attributes'] as $attribute) {
			if(isset($category['name_en']) && $attribute['name_en']) {
				$this->product_data[$product['id']]['attributes']['en-gb'][] = array(
																					'name'		=>	$attribute['name_en'],
																					'text'		=>	$attribute['value_en'],
																					'type'		=> 'specifications',
																				);
			}

			if(isset($category['name_nl']) && $attribute['name_nl']) {
				$this->product_data[$product['id']]['attributes']['nl'][] = array(
																				'name'		=>	$attribute['name_nl'],
																				'text'		=>	$attribute['value_nl'],
																				'type'		=> 'specifications',
																			);
			}

			if(isset($category['name_de']) && $attribute['name_de']) {
				$this->product_data[$product['id']]['attributes']['de'][] = array(
																				'name'		=>	$attribute['name_de'],
																				'text'		=>	$attribute['value_de'],
																				'type'		=> 'specifications',
																			);
			}
		}
		
		if (isset($post_data['manufacturer_id']) && $post_data['manufacturer_id']) {
			$this->product_data[$product['id']]['manufacturer_id']	= $post_data['manufacturer_id'];
		} else
		if (isset($product['brand']) && $product['brand']) {
			$this->product_data[$product['id']]['manufacturer'] = $product['brand'];
		}

		if (isset($post_data['product_store'])) {
			$this->product_data[$product['id']]['product_store'] = $post_data['product_store'];
		}

		if (isset($post_data['product_category'])) {
			$this->product_data[$product['id']]['product_category'] = $post_data['product_category'];
		}

		if (isset($post_data['stock_status_id'])) {
			$this->product_data[$product['id']]['stock_status_id'] = $post_data['stock_status_id'];
		}

		if (isset($post_data['subtract'])) {
			$this->product_data[$product['id']]['subtract'] = $post_data['subtract'];
		}

		if (isset($post_data['minimum']) && $post_data['minimum']) {
			$this->product_data[$product['id']]['minimum'] = $post_data['minimum'];
		}

		if (isset($post_data['status'])) {
			$this->product_data[$product['id']]['status'] = $post_data['status'];
		}

		if (isset($post_data['date_available']) && $post_data['date_available']) {
			$this->product_data[$product['id']]['date_available'] = $post_data['date_available'];
		} else {
			$this->product_data[$product['id']]['date_available'] = date("Y-m-d");
		}

		if (isset($post_data['length_class_id'])) {
			$this->product_data[$product['id']]['length_class_id'] = $post_data['length_class_id'];
		}

		if (isset($post_data['weight_class_id'])) {
			$this->product_data[$product['id']]['weight_class_id'] = $post_data['weight_class_id'];
		}
	}

	public function eventProductEdit($route, &$data)
	{
		if (!empty($data) && isset($data['cancel']) && !empty($this->request->server['HTTP_REFERER']) && strrpos($this->request->server['HTTP_REFERER'], 'extension/module/cedtom') !== false) {

			$data['cancel']	= $this->url->link('extension/module/cedtom/product', $this->session_token_key . '=' . $this->session_token, true);
		}
	}
}
