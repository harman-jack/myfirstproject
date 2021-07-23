<?php

/**
 * 
 */
class ControllerExtensionModuleCedTomOrder extends Controller
{
	private $error;
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
		$this->load->language('extension/module/cedtom/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/cedtom/order');

		$this->getList();
	}

	public function delete()
	{
		$this->load->language('extension/module/cedtom/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/cedtom/order');

		if (isset($this->request->get['order_id']) && is_numeric($this->request->get['order_id'])) {
			$this->request->post['selected'] = array($this->request->get['order_id']);
		}

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $order_id) {
				$this->model_extension_module_cedtom_order->deleteOrder($order_id);
			}

			$this->session->data['success'] = $this->language->get('text_delete_success');

			$this->response->redirect($this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token, true));
		}

		$this->getList();
	}

	protected function getList()
	{
		$data = array();

		$data = $this->load->language('extension/module/cedtom/order');

		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = null;
		}

		if (isset($this->request->get['filter_tom_order_id'])) {
			$filter_tom_order_id = $this->request->get['filter_tom_order_id'];
		} else {
			$filter_tom_order_id = null;
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = null;
		}

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = null;
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$filter_tom_status = $this->request->get['filter_tom_status'];
		} else {
			$filter_tom_status = null;
		}

		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = null;
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_tom_order_id'])) {
			$url .= '&filter_tom_order_id=' . $this->request->get['filter_tom_order_id'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . $url, true)
		);

		$data['orders'] = array();

		$filter_data = array(
			'filter_order_id'      => $filter_order_id,
			'filter_tom_order_id' => $filter_tom_order_id,
			'filter_tom_status'  => $filter_tom_status,
			'filter_customer'	   => $filter_customer,
			'filter_order_status'  => $filter_order_status,
			'filter_total'         => $filter_total,
			'filter_date_added'    => $filter_date_added,
			'filter_date_modified' => $filter_date_modified,
			'sort'                 => $sort,
			'order'                => $order,
			'start'                => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_extension_module_cedtom_order->getTotalOrders($filter_data);

		$results = $this->model_extension_module_cedtom_order->getOrders($filter_data);

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'tom_status'	=> $result['tom_status'] ? $result['tom_status'] : 'Not-Exported',
				'tom_order_id' => $result['tom_order_id'],
				'customer'      => $result['customer'],
				'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'shipping_code' => $result['shipping_code'],
				'export'		=> $this->url->link('extension/module/cedtom/order/exportOrder', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true),
				'view'          => $this->url->link('sale/order/info', $this->session_token_key . '=' . $this->session_token . '&order_id=' . $result['order_id'] . $url, true),
				'edit'          => $this->url->link('sale/order/edit', $this->session_token_key . '=' . $this->session_token . '&order_id=' . $result['order_id'] . $url, true),
				'delete'			=> $this->url->link('extension/module/cedtom/order/delete', $this->session_token_key . '=' . $this->session_token . '&order_id=' . $result['order_id'] . $url, true)
			);
		}

		$data['delete'] = $this->url->link('extension/module/cedtom/order/delete', $this->session_token_key . '=' . $this->session_token, true);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
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

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_tom_order_id'])) {
			$url .= '&filter_tom_order_id=' . $this->request->get['filter_tom_order_id'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=o.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=co.order_status' . $url, true);
		$data['sort_total'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=o.total' . $url, true);
		$data['sort_date_added'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=o.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . '&sort=o.date_modified' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_tom_order_id'])) {
			$url .= '&filter_tom_order_id=' . $this->request->get['filter_tom_order_id'];
		}

		if (isset($this->request->get['filter_tom_status'])) {
			$url .= '&filter_tom_status=' . $this->request->get['filter_tom_status'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}

		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['filter_order_id'] = $filter_order_id;
		$data['filter_tom_order_id'] = $filter_tom_order_id;
		$data['filter_tom_status'] = $filter_tom_status;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status'] = $filter_order_status;
		$data['filter_total'] = $filter_total;
		$data['filter_date_added'] = $filter_date_added;
		$data['filter_date_modified'] = $filter_date_modified;


		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['carriers'] = array(
			'usps',
			'ups',
			'fedex',
			'other',

		);

		$data['order_statuses'] = $this->model_extension_module_cedtom_order->getOrderStatuses();

		$data['session_token_key'] = $this->session_token_key;
		$data['session_token'] = $this->session_token;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (VERSION >= '2.2.0.0') {
			$this->response->setOutput($this->load->view('extension/module/cedtom/order_list', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/module/cedtom/order_list.tpl', $data));
		}
	}

	public function exportOrder()
	{
		$data = array();

		if (VERSION < 3.0) {
			$data = $this->load->language('extension/module/cedtom/order');
		} else {
			$this->load->language('extension/module/cedtom/order');
		}

		if (isset($this->request->get['order_id']) && $this->request->get['order_id']) {
			$this->load->model('extension/module/cedtom/order');
			$order_info = $this->model_extension_module_cedtom_order->getOrder($this->request->get['order_id']);
			
			$data['customer'] = [];

			$data['customer']['first_name'] = $order_info['firstname'] ? $order_info['firstname'] : '';
			$data['customer']['last_name'] = $order_info['lastname'] ? $order_info['lastname'] : '';
			$data['customer']['email'] = $order_info['email'] ? $order_info['email'] : '';
			$data['customer']['street'] = $order_info['shipping_address_2'] ? $order_info['shipping_address_2'] : '';
			$data['customer']['house_no'] = $order_info['shipping_address_1'] ? $order_info['shipping_address_1'] : '';
			$data['customer']['zipcode'] = $order_info['shipping_postcode'] ? $order_info['shipping_postcode'] : '';
			$data['customer']['city'] = $order_info['shipping_city'] ? $order_info['shipping_city'] : '';
			$data['customer']['country'] = $order_info['shipping_country'] ? $order_info['shipping_country'] : '';
			
			$data['order'] = array();

			$order_products = $this->model_extension_module_cedtom_order->getOrderProducts($this->request->get['order_id']);
		
			if (!empty($order_products)) {
				$data['order']['reference'] = $order_info['order_id'];

				foreach ($order_products as $product_info) {
					$sku = $this->model_extension_module_cedtom_order->getProductSku($product_info['product_id']);
					if (!$sku) {
						$data['error'][] = $this->language->get('ProductCode_error');
					}

					$data['order']['orderlines'][] = array(
						'amount' 		=> $product_info['quantity'],
						'product_id'	=> $sku,
						'assembly'	=> false
					);
				}
			} else {
				$data['error'][] = $this->language->get('Product_error');
			}

			if (isset($data['error']) && $data['error']) {
				$this->session->data['warning'] = implode('<br/>', $data['error']);
			} else {

				$url = 'api/twm/orders';

				$method = 'POST';

				$params = $data;

				$response = $this->helper->curlRequest($url, $params, $method);
			
				if (isset($response['data']) && $response['data']) {
					$this->session->data['success'] = $this->language->get('order_success');

					$this->model_extension_module_cedtom_order->updateTomOrder($this->request->get['order_id'], $response['data']['order_id']);
				}
			}
		} else {
			$this->session->data['warning'] = $this->language->get('OrderId_error');
		}

		$this->response->redirect($this->url->link('sale/order', $this->session_token_key . '=' . $this->session_token, true));
	}

	public function eventOrderView($route, &$data)
	{
		if (!empty($data) && isset($data['cancel']) && strrpos($this->request->server['HTTP_REFERER'], 'extension/module/cedtom') !== false) {
			$data['cancel']	= $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token, true);
		}
	}

	public function eventOrderEdit($route, &$data)
	{
		if (!empty($data) && isset($data['cancel']) && strrpos($this->request->server['HTTP_REFERER'], 'extension/module/cedtom') !== false) {
			$data['cancel']	= $this->url->link('extension/module/cedtom/order', $this->session_token_key . '=' . $this->session_token, true);
		}
	}
}
