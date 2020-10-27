<?php

class ControllerExtensionModuleMarkeaze extends Controller {

	private $error = array();

	public function install() {
		$this->load->model('setting/event');
		$modelEvent = $this->model_setting_event;

		$this->load->model('setting/module');
		$this->model_setting_module->addModule('markeaze', array('name'=>'Markeaze'));

		$modelEvent->addEvent('markeaze', 'catalog/model/checkout/order/addOrder/after' , 'extension/module/markeaze/order_add_2_2');
		$modelEvent->addEvent('markeaze', 'catalog/model/checkout/order/editOrder/after' , 'extension/module/markeaze/order_update_2_2');
		$modelEvent->addEvent('markeaze', 'catalog/model/checkout/order/addOrderHistory/after' , 'extension/module/markeaze/order_update_2_2');

	 	$code = 'markeaze_header';
		$trigger = 'catalog/view/common/header/before';
		$action = 'extension/module/markeaze/head';
	 	$this->model_setting_event->addEvent($code, $trigger, $action);
	}

	public function uninstall() {
		$this->load->model('setting/event');
		$modelEvent = $this->model_setting_event;
		$modelEvent->deleteEvent('markeaze');

		$this->model_setting_event->deleteEventByCode('markeaze_header');
	}

	public function index() {
		$this->load->language('extension/module/markeaze');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('markeaze', $this->request->post);
			$this->response->redirect($this->url->link('extension/module/markeaze', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['entry_app_key'] = $this->language->get('entry_app_key');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/category', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['action'] = $this->url->link('extension/module/markeaze', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');

		if (isset($this->request->post['markeaze_app_key'])) {
			$data['markeaze_app_key'] = $this->request->post['markeaze_app_key'];
		} else {
			$data['markeaze_app_key'] = $this->config->get('markeaze_app_key');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/markeaze', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/markeaze')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}
