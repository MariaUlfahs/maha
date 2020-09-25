<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Transaksi extends CI_Controller {
	public function __construct() {
		parent::__construct();
		if ($this->session->userdata('username')==false) {
			redirect('login');
		}
		$this->load->model('model_global');
	}

	public function index() {
		redirect('transaksi/bayar');
	}

	public function bayar() {
		//ambil data siswa
		$this->db->select('tb_siswa.nim,nama');
		$this->db->from('tb_siswa');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim');
		$this->db->order_by('nama','ASC');
		$this->db->order_by('tb_siswa.nim','ASC');
		$query=$this->db->get();
		$data['siswa']=$query->result();

		//ambil data jenis bayar
		$this->db->select('id_jenisbayar,jenis_bayar');
		$this->db->from('tb_jenisbayar');
		$this->db->order_by('jenis_bayar','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['jenis']=$query->result();

		//ambil data jenis bayar
		$this->db->select('debitur');
		$this->db->from('tb_debitur');
		$this->db->order_by('debitur','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['debitur']=$query->result();

		$data['tanggal']=date('d/m/Y');
		$data['title']='Transaksi Pembayaran';
		$data['template']='transaksi/form_bayar';
		$this->load->view('layout/wrapper',$data);
	}

	public function simpan_bayar() {
		$tanggal=$this->input->post('tanggal');
		$nim=$this->input->post('nim');
		$jenis=$this->input->post('jenis');
		$debitur=$this->input->post('debitur');
		$bayar=$this->input->post('bayar');
		$belum=$this->input->post('belum');
		$keterangan=$this->input->post('keterangan');
		$sisa=$belum-$bayar;
		//cari kelas siswa dulu
		$kunci=array('nim'=>$nim);
		$cek=$this->model_global->ambil_data($kunci,'tb_kelassiswa');
		if ($cek) {
			$kelas=$cek->kode_kelas;
		} else {
			echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Siswa belum ditentukan kelasnya!
				</div>';
				die();
		}
		
		//konvert tanggal dulu
		$tgl=str_replace('/','-',$tanggal);
		$tgl=date('Y-m-d',strtotime($tgl));
		
		//cari jenis pembayaran dulu
		$kunci=array('id_jenisbayar'=>$jenis);
		$cek=$this->model_global->ambil_data($kunci,'tb_jenisbayar');
		if ($cek) {
			$isi=array('tanggal'=>$tgl,'nim'=>$nim,'kode_kelas'=>$kelas,'id_jenisbayar'=>$jenis,'bayar'=>$bayar,'kurang'=>$sisa,'debitur'=>$debitur,'keterangan'=>$keterangan);
			$this->model_global->tambah_data('tb_bayar',$isi);
			echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data pembayaran berhasil disimpan.
				</div>';
		} else {
			echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Jenis pembayaran tidak ditemukan!
				</div>';
		}
	}

	public function cari_bayar() {
		$nim=$this->input->post('nim');    //disini adalah patokan untuk mencari 
		$jenis=$this->input->post('jenis');
		//cari kelas siswa dulu
		$kunci=array('nim'=>$nim);
		$cek=$this->model_global->ambil_data($kunci,'tb_kelassiswa');
		if ($cek) {
			$kelas=$cek->kode_kelas;
			//cari bearan biaya dulu
			$kunci=array('kode_kelas'=>$kelas,'id_jenisbayar'=>$jenis);
			$cek=$this->model_global->ambil_data($kunci,'tb_biaya');
			if ($cek) {
				$biaya=$cek->biaya;
				//cari jumlah yang sudah dibayarkan
				$this->db->select('SUM(bayar) AS total');
				$this->db->from('tb_bayar');
				$this->db->where('nim',$nim);
				$this->db->where('id_jenisbayar',$jenis);
				$query=$this->db->get();
				$cek=$query->row();
				$total=$cek->total;
				echo $biaya-$total;
			}
		}
	}
//----------------------------------------------------------------------------------------------------------------
	public function pembayaran() {
		$data['tanggal']=date('d/m/Y');
		$data['title']='Data Transaksi Pembayaran';
		$data['template']='transaksi/view_bayar';
		$this->load->view('layout/wrapper',$data);
	}

	public function databayar() {
		$tanggal=date('Y-m-d');
		$req=$_REQUEST;
		$this->db->from('tb_bayar');
		$this->db->join('tb_siswa','tb_siswa.nim=tb_bayar.nim');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim');
		$this->db->where('tb_bayar.tanggal',$tanggal);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('tb_siswa.nim'=>$cari,'nama'=>$cari,'tb_kelassiswa.kode_kelas'=>$cari,'jenis_bayar'=>$cari,'debitur'=>$cari,'tb_bayar.keterangan'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('tb_bayar.tanggal','tb_siswa.nim','tb_siswa.nisn','tb_siswa.nama','tb_kelassiswa.kode_kelas','jenis_bayar','bayar','debitur','keterangan');
		$this->db->from('tb_bayar');
		$this->db->join('tb_siswa','tb_siswa.nim=tb_bayar.nim');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim');
		$this->db->where('tb_bayar.tanggal',$tanggal);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('tb_siswa.nim'=>$cari,'nama'=>$cari,'tb_kelassiswa.kode_kelas'=>$cari,'jenis_bayar'=>$cari,'debitur'=>$cari,'tb_bayar.keterangan'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		//urutan data
		$this->db->order_by($kolom[$req['order'][0]['column']],$req['order'][0]['dir']);
		$this->db->limit($req['length'],$req['start']);
		$query=$this->db->get();
		$qry=$query->result();

		$isinya=array();
		foreach ($qry as $hasil) {
			$datanya=array();
			$datanya[]=date('d/m/Y',strtotime($hasil->tanggal));
			$datanya[]=$hasil->nim;
			
			$datanya[]=$hasil->nama;
			$datanya[]=$hasil->kode_kelas;
			$datanya[]=$hasil->jenis_bayar;
			$datanya[]=number_format($hasil->bayar,0,',','.');
			$datanya[]=$hasil->debitur;
			$datanya[]=$hasil->keterangan;
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}
}