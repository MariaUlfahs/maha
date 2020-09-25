<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="<?php if ($this->uri->segment(1)=='beranda') echo "active" ?>"><a href="<?php echo base_url('beranda') ?>"><i class="fa fa-dashboard"></i> <span>Beranda</span></a></li>
            <li class="treeview <?php if ($this->uri->segment(1)=='master') echo "active" ?>">
                <a href="#">
                    <i class="fa fa-desktop"></i><span>Master</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url('master/kelas') ?>"><i class="fa fa-circle-o"></i>Kelas</a></li>
                    <li><a href="<?php echo base_url('master/jenis') ?>"><i class="fa fa-circle-o"></i>Jenis Pembayaran</a></li>
                    <li><a href="<?php echo base_url('master/debitur') ?>"><i class="fa fa-circle-o"></i>Debitur</a></li>
                    <li><a href="<?php echo base_url('master/siswa') ?>"><i class="fa fa-circle-o"></i>Mahasiswa</a></li>
                    <li><a href="<?php echo base_url('master/bagikelas') ?>"><i class="fa fa-circle-o"></i>Pembagian Kelas Mahasiswa</a></li>
                </ul>
            </li>
            <li class="treeview <?php if ($this->uri->segment(1)=='transaksi') echo "active" ?>">
                <a href="#">
                    <i class="fa fa-money"></i><span>Transaksi</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url('transaksi/bayar') ?>"><i class="fa fa-circle-o"></i>Pembayaran</a></li>
                    <li><a href="<?php echo base_url('transaksi/pembayaran') ?>"><i class="fa fa-circle-o"></i>Data Pembayaran</a></li>
                </ul>
            </li>
            <li class="treeview <?php if ($this->uri->segment(1)=='laporan') echo "active" ?>">
                <a href="#">
                    <i class="fa fa-book"></i><span>Laporan</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="<?php echo base_url('laporan/pembayaran') ?>"><i class="fa fa-circle-o"></i>Pembayaran</a></li>
                    <li><a href="<?php echo base_url('laporan/pembayaransiswa') ?>"><i class="fa fa-circle-o"></i>Pembayaran Per Mahasiswa</a></li>
                    <li><a href="<?php echo base_url('laporan/tunggakan') ?>"><i class="fa fa-circle-o"></i>Tunggakan Pembayaran</a></li>
                    <li><a href="<?php echo base_url('laporan/rekappendapatan') ?>"><i class="fa fa-circle-o"></i>Rekap Pendapatan</a></li>
                    <li><a href="<?php echo base_url('laporan/rekapdebitur') ?>"><i class="fa fa-circle-o"></i>Rekap Pendapatan Debitur</a></li>
                </ul>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>
