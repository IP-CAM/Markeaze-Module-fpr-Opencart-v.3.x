<?php

class ControllerExtensionModuleMarkeaze extends Controller {

	public function index() {
		$this->load->language('extension/module/markeaze');
		$this->load->model('setting/setting');
		$this->load->model('extension/module/markeaze');
		$this->load->model('account/customer');

		if (!($app_key = $this->config->get('markeaze_app_key'))) return false;

		$data['app_key'] = $app_key;

		if ($this->customer->isLogged()) {
			$data['visitor_info'] = $this->model_extension_module_markeaze->getVisitorInfo();
		}

		$data['view_product'] = isset($this->session->mkz_view_product) ? $this->session->mkz_view_product : false;

		return $this->load->view('extension/module/markeaze', $data);
	}

	public function head (&$route, &$args, &$output) {
		$args['analytics'][] = $this->index();
	}

	public function order_add($route, $data, $order_id) {
		if ((integer) $order_id > 0) {
			$this->load->model('extension/module/markeaze');
			$this->model_extension_module_markeaze->orderAdd($order_id);
		}
	}

	public function order_update($route, $data) {
		if (!empty($data[0])) {
			$order_id = $data[0];
			$this->load->model('extension/module/markeaze');
			$this->model_extension_module_markeaze->orderState($order_id);
		}
	}

	public function order_history_add($order_id) {
		if ((integer) $order_id > 0) {
			$this->load->model('extension/module/markeaze');
			$this->model_extension_module_markeaze->orderAdd($order_id);
		}
	}

	public function order_delete($order_id) {
		if ((integer) $order_id > 0) {
			$this->load->model('extension/module/markeaze');
			$this->model_extension_module_markeaze->orderDelete($order_id);
		}
	}

}
