<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_login extends CI_Model
{
	public function cek_login($tabel, $where)
	{
		return $this->db->get_where($tabel, $where);
	}

	/**
	 * POST data ke URL. Return string respon, atau FALSE kalau gagal.
	 */
	public function post_to_url($url, $data)
	{
		$post = curl_init();
		curl_setopt($post, CURLOPT_URL, $url);
		curl_setopt($post, CURLOPT_POST, TRUE);
		curl_setopt($post, CURLOPT_POSTFIELDS, http_build_query($data)); // otomatis di-encode
		curl_setopt($post, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 5); // jangan hang kalau server tidak ada
		curl_setopt($post, CURLOPT_TIMEOUT, 10);
		$result = curl_exec($post);
		curl_close($post);
		return $result;
	}
}