<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Master extends CI_Controller {
	public function __construct() {
		parent::__construct();
		if ($this->session->userdata('username')==false) {
			redirect('login');
		}
		$this->load->model('model_global');
	}

	public function index() {
		redirect('master/jenis');
	}

	public function jenis() {
		$data['title']='Jenis Pembayaran';
		$data['template']='master/view_jenis';
		$this->load->view('layout/wrapper',$data);
	}

	public function datajenis() {
		$req=$_REQUEST;
		$this->db->from('tb_jenisbayar');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('jenis_bayar'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('jenis_bayar','','');
		$this->db->from('tb_jenisbayar');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('jenis_bayar'=>$cari);
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
			$datanya[]=$hasil->jenis_bayar;
			$datanya[]='<a href="'.base_url('master/form_jenis/?id='.$hasil->id_jenisbayar).'" class="btn btn-info btn-xs" title="Edit"><i class="fa fa-edit icon-white"></i></a>';
			$datanya[]='<button title="Hapus" onclick="konfirhapus(this.value)" class="btn btn-danger btn-xs" value="'.$hasil->id_jenisbayar.'"><i class="fa fa-trash-o"></i></button>';
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}

	public function form_jenis() {
		$id=$this->input->get('id');
		$kunci=array('id_jenisbayar'=>$id);
		$cek=$this->model_global->ambil_data($kunci,'tb_jenisbayar');
		if ($cek) {
			$data['id']=$cek->id_jenisbayar;
			$data['jenis']=$cek->jenis_bayar;
			//ambil data tarif
			$databiaya=$this->model_global->ambil_data_banyak($kunci,'tb_biaya');
			$arrbiaya=array();
			foreach ($databiaya as $hasil) {
				$arrbiaya[$hasil->kode_kelas]=$hasil->biaya;
			}
			$data['arrbiaya']=$arrbiaya;
		}
		//ambil data kelas
		$this->db->from('tb_kelas');
		$this->db->where('aktif',1);
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$query=$this->db->get();
		$data['datakelas']=$query->result();
		$data['title']='Jenis Pembayaran';
		$data['template']='master/form_jenis';
		$this->load->view('layout/wrapper',$data);
	}

	public function simpan_jenis() {
		$jenis=$this->input->post('jenis');
		$id=$this->input->post('id');
		if ($id) {
			//edit data
			$kunci=array('id_jenisbayar'=>$id);
			$isi=array('jenis_bayar'=>$jenis,'aktif'=>1);
			$this->model_global->update_data($kunci,'tb_jenisbayar',$isi);
			foreach ($this->input->post() as $key => $value) {
				if ($key=='jenis') {
					continue;
				}
				if ($key=='id') {
					continue;
				}
				$kunci=array('id_jenisbayar'=>$id,'kode_kelas'=>$key);
				$cek=$this->model_global->cek_data($kunci,'tb_biaya');
				if ($cek) {
					$isi=array('biaya'=>$value);
					$this->model_global->update_data($kunci,'tb_biaya',$isi);
				} else {
					$isi=array('id_jenisbayar'=>$id,'kode_kelas'=>$key,'biaya'=>$value);
					$this->model_global->tambah_data('tb_biaya',$isi);
				}
			}
			echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data jenis pembayaran berhasil diubah.
				</div>';
		} else {
			//tambah data
			//cek kode jika ada yang kembar
			$kunci=array('jenis_bayar'=>$jenis,'aktif'=>1);
			$cek=$this->model_global->cek_data($kunci,'tb_jenisbayar');
			if ($cek) {
				echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Data jenis bayar sudah diinput sebelumnya.
					</div>';
			} else {
				//jika tidak ada yang kembar baru tambah data
				$isi=array('jenis_bayar'=>$jenis,'aktif'=>1);
				$this->model_global->tambah_data('tb_jenisbayar',$isi);
				$id=$this->db->insert_id();
				foreach ($this->input->post() as $key => $value) {
					if ($key=='jenis') {
						continue;
					}
					if ($key=='id') {
						continue;
					}
					$isi=array('id_jenisbayar'=>$id,'kode_kelas'=>$key,'biaya'=>$value);
					$this->model_global->tambah_data('tb_biaya',$isi);
				}
				echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Data jenis pembayaran berhasil ditambahkan.
					</div>';
			}
		}
	}

	function hapus_jenis() {
		$id=$this->input->post('id');
		if ($id) {
			$kunci=array('id_jenisbayar'=>$id);
			$isi=array('aktif'=>0);
			$this->model_global->update_data($kunci,'tb_jenisbayar',$isi);
			echo '<div class="alert alert-warning alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data pembayaran berhasil dihapus.
				</div>';
		}
	}

	public function debitur() {
		$data['title']='Debitur Pembayaran';
		$data['template']='master/view_debitur';
		$this->load->view('layout/wrapper',$data);
	}

	public function datadebitur() {
		$req=$_REQUEST;
		$this->db->from('tb_debitur');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('debitur'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('debitur','','');
		$this->db->from('tb_debitur');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('debitur'=>$cari);
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
			$datanya[]=$hasil->debitur;
			$datanya[]='<a href="javascript:void(0)" class="btn btn-info btn-xs" title="Edit" data-toggle="modal" data-target="#modalutama" onclick="tampilform(\''.$hasil->debitur.'\')"><i class="fa fa-edit icon-white"></i></a>';
			$datanya[]='<button title="Hapus" onclick="konfirhapus(this.value)" class="btn btn-danger btn-xs" value="'.$hasil->debitur.'"><i class="fa fa-trash-o"></i></button>';
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}

	public function form_debitur() {
		$id=$this->input->post('id');
		$kunci=array('debitur'=>$id);
		$cek=$this->model_global->ambil_data($kunci,'tb_debitur');
		if ($cek) {
			$data['debitur']=$cek->debitur;
		}
		$data['title']='Debitur';
		$this->load->view('master/form_debitur',$data);
	}

	public function simpan_debitur() {
		$debitur=$this->input->post('debitur');
		$id=$this->input->post('id');
		$data=array();
		if ($id) {
			//edit data
			$kunci=array('debitur'=>$id);
			$isi=array('debitur'=>$debitur,'aktif'=>1);
			$this->model_global->update_data($kunci,'tb_debitur',$isi);
			echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data debitur berhasil diubah.
				</div>';
		} else {
			//tambah data
			//cek kode jika ada yang kembar
			$kunci=array('debitur'=>$debitur,'aktif'=>1);
			$cek=$this->model_global->cek_data($kunci,'tb_debitur');
			if ($cek) {
				echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Nama debitur sudah diinput sebelumnya.
					</div>';
			} else {
				//jika tidak ada yang kembar baru tambah data
				$isi=array('debitur'=>$debitur,'aktif'=>1);
				$this->db->replace('tb_debitur',$isi);
				echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Data debitur berhasil ditambahkan.
					</div>';
			}
		}
	}

	function hapus_debitur() {
		$id=$this->input->post('id');
		if ($id) {
			$kunci=array('debitur'=>$id);
			$isi=array('aktif'=>0);
			$this->model_global->update_data($kunci,'tb_debitur',$isi);
			echo '<div class="alert alert-warning alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data debitur dihapus.
				</div>';
		}
	}

	public function kelas() {
		$data['title']='Data Kelas';
		$data['template']='master/view_kelas';
		$this->load->view('layout/wrapper',$data);
	}

	public function datakelas() {
		$req=$_REQUEST;
		$this->db->from('tb_kelas');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('semester'=>$cari,'angkatan'=>$cari,'kode_kelas'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('kode_kelas','semester','angkatan','','');
		$this->db->from('tb_kelas');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('semester'=>$cari,'angkatan'=>$cari,'kode_kelas'=>$cari);
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
			$datanya[]=$hasil->kode_kelas;
			$datanya[]=$hasil->semester;
			$datanya[]=$hasil->angkatan;
			$datanya[]='<a href="javascript:void(0)" class="btn btn-info btn-xs" title="Edit" data-toggle="modal" data-target="#modalutama" onclick="tampilform(\''.$hasil->kode_kelas.'\')"><i class="fa fa-edit icon-white"></i></a>';
			$datanya[]='<button title="Hapus" onclick="konfirhapus(this.value)" class="btn btn-danger btn-xs" value="'.$hasil->kode_kelas.'"><i class="fa fa-trash-o"></i></button>';
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}

	public function form_kelas() {
		$id=$this->input->post('id');
		$kunci=array('kode_kelas'=>$id);
		$cek=$this->model_global->ambil_data($kunci,'tb_kelas');
		if ($cek) {
			$data['kode']=$cek->kode_kelas;
			$data['semester']=$cek->semester;
			$data['angkatan']=$cek->angkatan;
		}
		$data['title']='Data Kelas';
		$this->load->view('master/form_kelas',$data);
	}

	public function simpan_kelas() {
		$kode=$this->input->post('kode');
		$semester=$this->input->post('semester');
		$angkatan=$this->input->post('angkatan');
		$id=$this->input->post('id');
		$data=array();
		if ($id) {
			//edit data
			$kunci=array('kode_kelas'=>$id);
			$isi=array('kode_kelas'=>$kode,'semester'=>$semester,'angkatan'=>$angkatan,'aktif'=>1);
			$this->model_global->update_data($kunci,'tb_kelas',$isi);
			echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data kelas berhasil diubah.
				</div>';
		} else {
			//tambah data
			//cek kode jika ada yang kembar
			$kunci=array('kode_kelas'=>$kode,'aktif'=>1);
			$cek=$this->model_global->cek_data($kunci,'tb_kelas');
			if ($cek) {
				echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Kode kelas sudah ada!
					</div>';
			} else {
				//jika tidak ada yang kembar baru tambah data
				$isi=array('kode_kelas'=>$kode,'semester'=>$semester,'angkatan'=>$angkatan,'aktif'=>1);
				$this->db->replace('tb_kelas',$isi);
				echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Data kelas berhasil ditambahkan.
					</div>';
			}
		}
	}

	function hapus_kelas() {
		$id=$this->input->post('id');
		if ($id) {
			$kunci=array('kode_kelas'=>$id);
			$isi=array('aktif'=>0);
			$this->model_global->update_data($kunci,'tb_kelas',$isi);
			echo '<div class="alert alert-warning alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data kelas berhasil dihapus.
				</div>';
		}
	}

	public function siswa() {
		$data['title']='Data Mahasiswa';
		$data['template']='master/view_siswa';
		$this->load->view('layout/wrapper',$data);
	}

	public function datasiswa() {
		$req=$_REQUEST;
		$this->db->from('tb_siswa');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('nim'=>$cari,'nama'=>$cari,'telepon'=>$cari,'alamat'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('nim','nama','jekel','telepon','alamat','','');
		$this->db->from('tb_siswa');
		$this->db->where('aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('nim'=>$cari,'nama'=>$cari,'telepon'=>$cari,'alamat'=>$cari);
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
			$datanya[]=$hasil->nim;
			
			$datanya[]=$hasil->nama;
			$datanya[]=$hasil->jekel;
			$datanya[]=$hasil->telepon;
			$datanya[]=$hasil->alamat;
			$datanya[]='<a href="javascript:void(0)" class="btn btn-info btn-xs" title="Edit" data-toggle="modal" data-target="#modalutama" onclick="tampilform(\''.$hasil->nim.'\')"><i class="fa fa-edit icon-white"></i></a>';
			$datanya[]='<button title="Hapus" onclick="konfirhapus(this.value)" class="btn btn-danger btn-xs" value="'.$hasil->nim.'"><i class="fa fa-trash-o"></i></button>';
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}

	public function form_siswa() {
		$id=$this->input->post('id');
		$kunci=array('nim'=>$id);
		$cek=$this->model_global->ambil_data($kunci,'tb_siswa');
		if ($cek) {
			$data['nim']=$cek->nim;
			$data['nama']=$cek->nama;
			$data['jekel']=$cek->jekel;
			$data['telepon']=$cek->telepon;
			$data['alamat']=$cek->alamat;
			
		}
		$data['title']='Data Mahasiswa';
		$this->load->view('master/form_siswa',$data);
	}

	public function simpan_siswa() {
		$nim=$this->input->post('nim');
		
		$nama=$this->input->post('nama');
		$jekel=$this->input->post('jekel');
		$telepon=$this->input->post('telepon');
		$alamat=$this->input->post('alamat');
		$id=$this->input->post('id');
		$data=array();
		if ($id) {
			//edit data
			$kunci=array('nim'=>$id);
			$isi=array('nim'=>$nim,'nama'=>$nama,'jekel'=>$jekel,'telepon'=>$telepon,'alamat'=>$alamat,'aktif'=>1);
			$this->model_global->update_data($kunci,'tb_siswa',$isi);
			echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data Mahasiswa berhasil diubah.
				</div>';
		} else {
			//tambah data
			//cek kode jika ada yang kembar
			$kunci=array('nim'=>$nim);
			$cek=$this->model_global->cek_data($kunci,'tb_siswa');
			if ($cek) {
				echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						NIM sudah diinput sebelumnya.
					</div>';
			} else {
				//jika tidak ada yang kembar baru tambah data
				$isi=array('nim'=>$nim,'nama'=>$nama,'jekel'=>$jekel,'telepon'=>$telepon,'alamat'=>$alamat,'aktif'=>1);
				$this->model_global->tambah_data('tb_siswa',$isi);
				echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
						Data Mahasiswa berhasil ditambahkan.
					</div>';
			}
		}
	}

	function hapus_siswa() {
		$id=$this->input->post('id');
		if ($id) {
			$kunci=array('nim'=>$id);
			$isi=array('aktif'=>0);
			$this->model_global->update_data($kunci,'tb_siswa',$isi);
			echo '<div class="alert alert-warning alert-dismissible fade in" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
					Data Mahasiswa berhasil dihapus.
				</div>';
		}
	}
//----------------------------------------------------
	function bagikelas() {
		$this->db->from('tb_kelas');
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$query=$this->db->get();
		$data['datakelas']=$query->result();
		$data['title']='Pembagian Kelas Mahasiswa';
		$data['template']='master/view_bagikelas';
		$this->load->view('layout/wrapper',$data);
	}

	function databagikelas() {
		$kelas=$this->input->get('kelas');
		//ambil master kelas dulu
		$this->db->from('tb_kelas');
		$this->db->where('aktif',1);
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$query=$this->db->get();
		$datakelas=$query->result();

		$req=$_REQUEST;
		$this->db->select('tb_siswa.nim,nama,jekel,tb_kelassiswa.kode_kelas');
		$this->db->from('tb_siswa');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim','LEFT');
		if ($kelas) {
			$this->db->where('tb_kelassiswa.kode_kelas',$kelas);
		}
		$this->db->where('tb_siswa.aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('tb_siswa.nim'=>$cari,'nama'=>$cari,'jekel'=>$cari);
			$this->db->group_start();
			$this->db->or_like($kunci);
			$this->db->group_end();
		}
		$query=$this->db->get();
		$total=$query->num_rows();


		//konversi kolom
		$kolom=array('tb_siswa.nim','nama','jekel','');
		$this->db->select('tb_siswa.nim,nama,jekel,tb_kelassiswa.kode_kelas');
		$this->db->from('tb_siswa');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim','LEFT');
		if ($kelas) {
			$this->db->where('tb_kelassiswa.kode_kelas',$kelas);
		}
		$this->db->where('tb_siswa.aktif',1);
		//pencarian data
		if ($req['search']['value']) {
			$cari=$req['search']['value'];
			$kunci=array('tb_siswa.nim'=>$cari,'nama'=>$cari,'jekel'=>$cari);
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
			$op='<option value="">-- pilih kelas --</option>';
			foreach ($datakelas as $hasil2) {
				if (@$hasil->kode_kelas==$hasil2->kode_kelas) {
					$selected='selected';
				} else {
					$selected='';
				}
				$op.='<option value="'.$hasil2->kode_kelas.'" '.$selected.'>'.$hasil2->kode_kelas.'</option>';
			}
			$datanya=array();
			$datanya[]=$hasil->nim;
			
			$datanya[]=$hasil->nama;
			$datanya[]=$hasil->jekel;
			$datanya[]='<select class="form-control" name="kdkelas[]" id="kdkelas[]" onchange="simpankelas(\''.$hasil->nim.'\',this.value)">'.$op.'</select>';
			$isinya[]=$datanya;
		}
		$result=array('recordsTotal'=>$total,'recordsFiltered'=>$total,'data'=>$isinya);
		echo json_encode($result);
	}

	public function simpan_bagikelas() {
		$nim=$this->input->post('nim');
		$kelas=$this->input->post('kelas');
		if ($kelas) {
			//cek data
			$kunci=array('nim'=>$nim);
			$cek=$this->model_global->cek_data($kunci,'tb_kelassiswa');
			if ($cek) {
				$isi=array('kode_kelas'=>$kelas,'nim'=>$nim);
				$this->model_global->update_data($kunci,'tb_kelassiswa',$isi);
			} else {
				$isi=array('kode_kelas'=>$kelas,'nim'=>$nim);
				$this->model_global->tambah_data('tb_kelassiswa',$isi);
			}
		} else {
			$kunci=array('nim'=>$nim);
			$this->model_global->hapus_data($kunci,'tb_kelassiswa');
		}
	}
	

	
}