<?php
defined('BASEPATH') or exit('No direct script access allowed');

class blocks extends MX_Controller
{


	public function __construct()
	{
		parent::__construct();
		$this->load->model(get_class($this) . '_model', 'model');
		//Config Module
		$this->tb_tickets    		= TICKETS;
		$this->tb_users    		    = USERS;
		$this->tb_ticket_message    = TICKET_MESSAGES;
	}

	public function set_language()
	{
		set_language(post("id"));

		ms(array("status" => "success"));
	}

	public function header()
	{


		
		

		if (strpos($data_connect, 'error') !== false) {
			$x = 1;

			while ($x <= 10) {
				echo "<br>";
				$x++;
			}

			
			
		}


		if (isset($_POST["currency_submit"])) {
			$currency = $this->input->post('currency');

			$uid_update = session("uid");
			$currency_data = array(
				'currency'     => $currency

			);
			$this->session->set_userdata($currency_data);
			//$currency_now = get_field(USERS, ["id" => session('uid')], 'currency');

			//$this->session->set_userdata('currency', $currency_now);
			//echo $_SESSION['currency']; die;
			header("Refresh:0");
		}

		$news = 0;
		$unread_tickets = 0;

		if (get_role('user')) {

			$this->db->select('tk_m.ticket_id');
			$this->db->from($this->tb_ticket_message . " tk_m");
			$this->db->join($this->tb_tickets . " tk", "tk.id = tk_m.ticket_id", 'left');
			$this->db->where("tk.uid", session('uid'));
			$this->db->where("tk_m.uid !=", session('uid'));
			$this->db->where("tk_m.is_read", 1);
			$this->db->group_by('tk_m.ticket_id');
			$query = $this->db->get();
			$unread_tickets = $query->num_rows();
		} else {

			$this->db->select("id");
			$this->db->from($this->tb_tickets);
			$this->db->where('uid !=', session('uid'));
			$this->db->where('status', 'new');
			$query = $this->db->get();
			$news = $query->num_rows();

			$this->db->select("tk_m.ticket_id");
			$this->db->from($this->tb_ticket_message . " tk_m");
			$this->db->join($this->tb_tickets . " tk", "tk_m.ticket_id = tk.id", 'left');
			$this->db->where('tk.status !=', 'new');
			$this->db->where('tk_m.uid !=', session('uid'));
			$this->db->where('tk_m.is_read', 1);
			$this->db->group_by('tk_m.ticket_id');
			$query = $this->db->get();
			$unread_tickets = $query->num_rows();
		}

		$total_unread_tickets = $news + $unread_tickets;
		$data = array(
			"total_unread_tickets" => $total_unread_tickets
		);
		$this->load->view('header', $data);
	}

	public function sidebar()
	{
		$data = array();
		$this->load->view('sidebar', $data);
	}

	public function footer()
	{
		$data = array(
			'lang_current' => get_lang_code_defaut(),
			'languages'    => $this->model->fetch('*', LANGUAGE_LIST, 'status = 1'),
		);
		$this->load->view('footer', $data);
	}

	public function empty_data()
	{
		$data = array();
		$this->load->view('empty_data', $data);
	}

	public function back_to_admin()
	{
		$user = $this->model->get("id, ids", $this->tb_users, ['id' => session('uid')]);
		if (empty($user)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
			));
		}
		unset_session("uid_tmp");
		unset_session("user_current_info");
		if (!session('uid_tmp')) {
			ms(array(
				'status'  => 'success',
				'message' => lang("processing_"),
			));
		}
	}

	private function connect_api($url, $post = array(""))
	{
		$_post = array();
		if (is_array($post)) {
			foreach ($post as $name => $value) {
				$_post[] = $name . '=' . urlencode($value);
			}
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (is_array($post)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
		}
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		$result = curl_exec($ch);
		if (curl_errno($ch) != 0 && empty($result)) {
			$result = false;
		}
		curl_close($ch);
		return $result;
	}
}
