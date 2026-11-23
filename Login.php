<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input   $input
 * @property CI_Loader  $load
 * @property M_login    $m_login
 */
class Login extends CI_Controller
{
	/**
	 * TRUE  = mode lokal (XAMPP), tidak memanggil API kampus
	 * FALSE = mode produksi, login lewat API kampus
	 */
	private $mode_lokal = TRUE;

	/** URL API kampus (dipakai kalau $mode_lokal = FALSE) */
	private $api_url = 'http://10.10.3.51/auth/auth_dosen';

	/** Akun dummy untuk mode lokal: username => data */
	private $akun_lokal = array(
		'231432' => array(
			'password' => '123456',
			'ID'       => '231432',
			'NAMA'     => 'MUHAMAD FAKHRUL ARIFIN, S.Kom',
			'IdProdi'  => '202343500924',
		),
		'654654654' => array(
			'password' => '123456',
			'ID'       => '654654654',
			'NAMA'     => 'MUTTAQIN, S.T, M.Cs',
			'IdProdi'  => '1001',
		),
		'0702131911891' => array(
			'password' => '123456',
			'ID'       => '0702131911891',
			'NAMA'     => 'Zuhar Musliyana, S.ST., M.T.',
			'IdProdi'  => '1001',
		),
	);

	function __construct()
	{
		parent::__construct();
		$this->load->model('m_login');
	}

	function index()
	{
		if ($this->session->userdata('id_user')) {
			redirect('home/index');
		}
		$this->load->view('signin');
	}

	function login_akses()
	{
		if ($this->input->method() !== 'post') {
			redirect('login');
		}

		$username = trim($this->input->post('uIdDos'));
		$password = $this->input->post('uPassDos');

		$user = $this->mode_lokal
			? $this->cek_lokal($username, $password)
			: $this->cek_api();

		if ($user === FALSE) {
			$this->gagal("Username / Password salah.");
		}

		$this->session->set_userdata(array(
			'id_user' => $user['ID'],
			'nama'    => $user['NAMA'],
			'IdProdi' => $user['IdProdi'],
		));
		redirect('home/index');
	}

	private function cek_lokal($username, $password)
	{
		if (isset($this->akun_lokal[$username])
			&& $this->akun_lokal[$username]['password'] === $password) {
			return $this->akun_lokal[$username];
		}
		return FALSE;
	}

	private function cek_api()
	{
		$kirim = $this->m_login->post_to_url($this->api_url, $_POST);

		if ($kirim === FALSE) {
			$this->gagal("Server autentikasi tidak dapat dihubungi.");
		}

		$respon = json_decode($kirim, TRUE);

		if (!is_array($respon) || !isset($respon['status']) || $respon['status'] <= 0) {
			return FALSE;
		}

		return array(
			'ID'      => isset($respon['ID']) ? $respon['ID'] : '',
			'NAMA'    => isset($respon['NAMA']) ? $respon['NAMA'] : '',
			'IdProdi' => isset($respon['IdProdi']) ? $respon['IdProdi'] : '',
		);
	}

	private function gagal($pesan)
	{
		$this->session->set_flashdata("msg", "<br/><div class='alert alert-warning' role='alert'>
			<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
			" . html_escape($pesan) . "
			</div>");
		redirect('login');
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect(base_url());
	}
}