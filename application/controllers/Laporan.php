<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Laporan extends CI_Controller {
	public function __construct() {
		parent::__construct();
		if ($this->session->userdata('username')==false) {
			redirect('login');
		}
		$this->load->model('model_global');
	}

	public function index() {
		redirect('transaksi/pembayaran');
	}

	public function pembayaran() {
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

		//ambil data jenis bayar
		$this->db->from('tb_kelas');
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['kelas']=$query->result();

		$data['tanggal']=date('d');
		$data['bulan']=date('m');
		$data['tahun']=date('Y');
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$data['title']='Laporan Pembayaran';
		$data['template']='laporan/lap_pembayaran';
		$this->load->view('layout/wrapper',$data);
	}

	public function datapembayaran() {
		$tanggal=$this->input->get('tanggal');
		$bulan=$this->input->get('bulan');
		$tahun=$this->input->get('tahun');
		$jenis=$this->input->get('jenis');
		$debitur=$this->input->get('debitur');
		$kelas=$this->input->get('kelas');
		$req=$_REQUEST;
		$this->db->from('tb_bayar');
		$this->db->join('tb_siswa','tb_siswa.nim=tb_bayar.nim');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim');
		if ($tanggal) {
			$this->db->where("DATE_FORMAT(tb_bayar.tanggal,'%d')",$tanggal);
		}
		if ($bulan) {
			$this->db->where('MONTH(tb_bayar.tanggal)',$bulan);
		}
		if ($tahun) {
			$this->db->where('YEAR(tb_bayar.tanggal)',$tahun);
		}
		if ($debitur) {
			$this->db->where('tb_bayar.debitur',$debitur);
		}
		if ($jenis) {
			$this->db->where('tb_bayar.id_jenisbayar',$jenis);
		}
		if ($kelas) {
			$this->db->where('tb_bayar.kode_kelas',$kelas);
		}
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
		$kolom=array('tb_bayar.tanggal','tb_siswa.nim','tb_siswa.nama','tb_kelassiswa.kode_kelas','jenis_bayar','bayar','debitur','keterangan');
		$this->db->from('tb_bayar');
		$this->db->join('tb_siswa','tb_siswa.nim=tb_bayar.nim');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_siswa.nim');
		if ($tanggal) {
			$this->db->where("DATE_FORMAT(tb_bayar.tanggal,'%d')",$tanggal);
		}
		if ($bulan) {
			$this->db->where('MONTH(tb_bayar.tanggal)',$bulan);
		}
		if ($tahun) {
			$this->db->where('YEAR(tb_bayar.tanggal)',$tahun);
		}
		if ($debitur) {
			$this->db->where('tb_bayar.debitur',$debitur);
		}
		if ($jenis) {
			$this->db->where('tb_bayar.id_jenisbayar',$jenis);
		}
		if ($kelas) {
			$this->db->where('tb_bayar.kode_kelas',$kelas);
		}
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

	public function tunggakan() {
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

		//ambil data jenis bayar
		$this->db->from('tb_kelas');
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['kelas']=$query->result();

		$data['tanggal']=date('d');
		$data['bulan']=date('m');
		$data['tahun']=date('Y');
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$data['title']='Laporan Tunggakan Pembayaran';
		$data['template']='laporan/lap_tunggakan';
		$this->load->view('layout/wrapper',$data);
	}

	public function datatunggakan() {
		$jenis=$this->input->post('jenis');
		$kelas=$this->input->post('kelas');
		//ambil data jenis pembayaran dulu
		$this->db->from('tb_jenisbayar');
		$this->db->join('tb_biaya','tb_biaya.id_jenisbayar=tb_jenisbayar.id_jenisbayar');
		if ($jenis) {
			$this->db->where('tb_jenisbayar.id_jenisbayar',$jenis);
		}
		$this->db->where('tb_biaya.biaya<>',0);
		$this->db->where('tb_biaya.kode_kelas',$kelas);
		$this->db->order_by('jenis_bayar','ASC');
		$query=$this->db->get();
		$data['jenis']=$query->result();
		//ambil tabel kelas siswa
		$this->db->select('tb_siswa.nim,tb_siswa.nama,tb_kelassiswa.kode_kelas');
		$this->db->from('tb_siswa');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_kelassiswa.nim');
		$this->db->where('tb_kelassiswa.kode_kelas',$kelas);
		$this->db->group_by('tb_siswa.nim');
		$this->db->order_by('tb_siswa.nim','ASC');
		$query=$this->db->get();
		$data['siswa']=$query->result();
		//ambil jumlah yang sudah dibayar;
		$this->db->select('tb_bayar.nim,tb_bayar.bayar,tb_bayar.id_jenisbayar');
		$this->db->from('tb_bayar');
		$this->db->join('tb_kelassiswa','tb_kelassiswa.nim=tb_bayar.nim');
		$this->db->where('tb_kelassiswa.kode_kelas',$kelas);
		if ($jenis) {
			$this->db->where('tb_bayar.id_jenisbayar',$jenis);
		}
		$query=$this->db->get();
		$bayar=$query->result();
		$arrbayar=array();
		foreach ($bayar as $hasil) {
			$arrbayar[$hasil->nim][$hasil->id_jenisbayar][]=$hasil->bayar;
		}
		$data['arrbayar']=$arrbayar;
		$this->load->view('laporan/ajax_tunggakan',$data);
	}

	public function datatunggakansiswa() {
		$tahun=$this->input->post('tahun');
		$jenis=$this->input->post('jenis');
		$kelas=$this->input->post('kelas');
		$nim=$this->input->post('nim');
		//ambil data jenis pembayaran dulu
		$this->db->from('tb_jenisbayar');
		$this->db->join('tb_biaya','tb_biaya.id_jenisbayar=tb_jenisbayar.id_jenisbayar');
		if ($jenis) {
			$this->db->where('tb_jenisbayar.id_jenisbayar',$jenis);
		}
		$this->db->where('tb_jenisbayar.aktif',1);
		$this->db->where('tb_biaya.biaya<>',0);
		$this->db->where('tb_biaya.kode_kelas',$kelas);
		$this->db->order_by('jenis_bayar','ASC');
		$query=$this->db->get();
		$data['jenis']=$query->result();
		//ambil jumlah yang sudah dibayar;
		$kunci=array('kode_kelas'=>$kelas,'nim'=>$nim,"DATE_FORMAT(tanggal,'%Y')"=>$tahun);
		$this->db->select('tanggal,nim,bayar,id_jenisbayar');
		$this->db->from('tb_bayar');
		$this->db->where($kunci);
		if ($jenis) {
			$this->db->where('id_jenisbayar',$jenis);
		}
		$query=$this->db->get();
		$bayar=$query->result();
		$arrbayar=array();
		$arrtotal=array();
		$total=0;
		foreach ($bayar as $hasil) {
			$bulan=date('m',strtotime($hasil->tanggal));
			$arrbayar[$hasil->id_jenisbayar][$bulan][]=$hasil->bayar;
			$arrtotal[$hasil->id_jenisbayar][]=$hasil->bayar;
		}
		$data['arrbayar']=$arrbayar;
		$data['arrtotal']=$arrtotal;
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$this->load->view('laporan/ajax_tunggakansiswa',$data);
	}

	public function pembayaransiswa() {
		//ambil data jenis bayar
		$this->db->select('id_jenisbayar,jenis_bayar');
		$this->db->from('tb_jenisbayar');
		$this->db->order_by('jenis_bayar','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['jenis']=$query->result();

		//ambil data jenis bayar
		$this->db->from('tb_kelas');
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['kelas']=$query->result();

		$data['tanggal']=date('d');
		$data['bulan']=date('m');
		$data['tahun']=date('Y');
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$data['title']='Laporan Tunggakan Pembayaran';
		$data['template']='laporan/lap_pembayaransiswa';
		$this->load->view('layout/wrapper',$data);
	}

	public function rekappendapatan() {
		//ambil data jenis bayar
		$this->db->from('tb_kelas');
		$this->db->group_by('semester');
		$this->db->order_by('semester','ASC');
		$this->db->where('aktif',1);
		$query=$this->db->get();
		$data['kelas']=$query->result();

		$data['tanggal']=date('d');
		$data['bulan']=date('m');
		$data['tahun']=date('Y');
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$data['title']='Rekap Pendapatan';
		$data['template']='laporan/rekap_pendapatan';
		$this->load->view('layout/wrapper',$data);
	}

	public function datarekappendapatan() {
		$tanggal=$this->input->post('tanggal');
		$bulan=$this->input->post('bulan');
		$tahun=$this->input->post('tahun');
		$kelas=$this->input->post('kelas');
		//ambil data kelas dulu
		$this->db->from('tb_kelas');
		$this->db->where('aktif',1);
		if ($kelas) {
			$this->db->where('semester',$kelas);
		}
		$this->db->order_by('semester','ASC');
		$this->db->order_by('angkatan','ASC');
		$query=$this->db->get();
		$datakelas=$query->result();
		foreach ($datakelas as $hasil) {
			if ($kelas) {
				$arrkelas[$hasil->kode_kelas]=$hasil->kode_kelas;
			} else {
				$arrkelas[$hasil->semester]=$hasil->semester;
			}
		}
		//ambil data pembayaran
		$this->db->select('tb_kelas.kode_kelas,tb_kelas.semester,tb_jenisbayar.id_jenisbayar,tb_jenisbayar.jenis_bayar,tb_bayar.bayar');
		$this->db->from('tb_bayar');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		$this->db->join('tb_kelas','tb_kelas.kode_kelas=tb_bayar.kode_kelas');
		if ($kelas) {
			$this->db->where('tb_kelas.semester',$kelas);
		}
		if ($tanggal) {
			$this->db->where("DATE_FORMAT(tb_bayar.tanggal,'%d')",$tanggal);
		}
		if ($bulan) {
			$this->db->where('MONTH(tb_bayar.tanggal)',$bulan);
		}
		if ($tahun) {
			$this->db->where('YEAR(tb_bayar.tanggal)',$tahun);
		}
		$this->db->order_by('jenis_bayar','ASC');
		$query=$this->db->get();
		$databayar=$query->result();
		$arrjenis=array();
		$arrtotal=array();
		foreach ($databayar as $hasil) {
			$arrjenis[$hasil->id_jenisbayar]=$hasil->jenis_bayar;
			if ($kelas) {
				$arrtotal[$hasil->id_jenisbayar][$hasil->kode_kelas][]=$hasil->bayar;
			} else {
				$arrtotal[$hasil->id_jenisbayar][$hasil->semester][]=$hasil->bayar;
			}
		}
		$data['arrkelas']=$arrkelas;
		$data['arrjenis']=$arrjenis;
		$data['arrtotal']=$arrtotal;
		$this->load->view('laporan/ajax_rekappendapatan',$data);
	}

	public function rekapdebitur() {
		$data['tanggal']=date('d');
		$data['bulan']=date('m');
		$data['tahun']=date('Y');
		$data['arrbulan']=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$data['title']='Rekap Pendapatan Debitur';
		$data['template']='laporan/rekap_debitur';
		$this->load->view('layout/wrapper',$data);
	}

	public function datarekapdebitur() {
		$tanggal=$this->input->post('tanggal');
		$bulan=$this->input->post('bulan');
		$tahun=$this->input->post('tahun');
		//ambil data pembayaran
		$this->db->select('tb_jenisbayar.id_jenisbayar,tb_jenisbayar.jenis_bayar,tb_bayar.bayar,tb_bayar.debitur');
		$this->db->from('tb_bayar');
		$this->db->join('tb_jenisbayar','tb_jenisbayar.id_jenisbayar=tb_bayar.id_jenisbayar');
		if ($tanggal) {
			$this->db->where("DATE_FORMAT(tb_bayar.tanggal,'%d')",$tanggal);
		}
		if ($bulan) {
			$this->db->where('MONTH(tb_bayar.tanggal)',$bulan);
		}
		if ($tahun) {
			$this->db->where('YEAR(tb_bayar.tanggal)',$tahun);
		}
		$this->db->order_by('jenis_bayar','ASC');
		$query=$this->db->get();
		$databayar=$query->result();
		$arrjenis=array();
		$arrdebitur=array();
		$arrtotal=array();
		foreach ($databayar as $hasil) {
			$arrjenis[$hasil->id_jenisbayar]=$hasil->jenis_bayar;
			$arrdebitur[$hasil->debitur]=$hasil->debitur;
			$arrtotal[$hasil->id_jenisbayar][$hasil->debitur][]=$hasil->bayar;
		}
		$data['arrdebitur']=$arrdebitur;
		$data['arrjenis']=$arrjenis;
		$data['arrtotal']=$arrtotal;
		$this->load->view('laporan/ajax_rekapdebitur',$data);
	}

	public function carisiswa() {
		$kelas=$this->input->post('kelas');
		//cari kelas siswa dulu
		$this->db->select('tb_siswa.nim,tb_siswa.nama');
		$this->db->from('tb_kelassiswa');
		$this->db->join('tb_siswa','tb_siswa.nim=tb_kelassiswa.nim');
		$this->db->where('tb_kelassiswa.kode_kelas',$kelas);
		$this->db->order_by('tb_siswa.nama','ASC');
		$query=$this->db->get();
		$siswa=$query->result();
		echo '<option value="">-- pilih siswa --</option>';
		foreach ($siswa as $hasil) {
			?>
			<option value="<?php echo $hasil->nim ?>"><?php echo $hasil->nama ?></option>
			<?php
		}
	}
}