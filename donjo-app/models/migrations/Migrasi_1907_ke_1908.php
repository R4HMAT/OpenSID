<?php
class Migrasi_1907_ke_1908 extends CI_model {

  public function up() {
  	// Tambah kolom pengurus untuk ttd u.b
	 	if (!$this->db->field_exists('pamong_ub', 'tweb_desa_pamong'))
  	{
			$fields = array();
			$fields['pamong_ub'] = array(
					'type' => 'tinyint',
					'constraint' => 1,
				  'null' => FALSE,
				  'defualt' => 0
			);
			$this->dbforge->add_column('tweb_desa_pamong', $fields);
			// Pindahkan setting sebutan_pimpinan_desa ke tweb_desa_pamong, terus hapus
			$this->load->model('pamong_model');
			$sebutan_pimpinan_desa = $this->db->where('key', 'sebutan_pimpinan_desa')->get('setting_aplikasi')->row()->value;
			$ttd = $this->db->limit(1)->where('jabatan', $sebutan_pimpinan_desa)->get('tweb_desa_pamong')->row()->pamong_id;
			$this->pamong_model->ttd($ttd, 1);
			$this->db->where('key', 'sebutan_pimpinan_desa')->delete('setting_aplikasi');
		}
		// Setting nilai default supaya tidak error pada strict mode
		$fields = array();
		$fields['id_kepala'] = array('type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'default' => NULL);
		$fields['lat'] = array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'default' => NULL);
		$fields['lng'] = array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'default' => NULL);
		$fields['zoom'] = array('type' => 'INT', 'null' => TRUE, 'default' => NULL);
		$fields['path'] = array('type' => 'TEXT', 'constraint' => 11, 'null' => TRUE, 'default' => NULL);
		$fields['map_tipe'] = array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE, 'default' => NULL);
	  $this->dbforge->modify_column('tweb_wil_clusterdesa', $fields);
		// Tambah kolom kode untuk setting_aplikasi_options
	 	if (!$this->db->field_exists('kode', 'setting_aplikasi_options'))
	 	{
			$fields = array();
			$fields['kode'] = array('type' => 'TINYINT', 'constraint' => 4, 'null' => TRUE, 'default' => NULL);
		  $this->dbforge->add_column('setting_aplikasi_options', $fields);
	 	}
	  // Perbaiki setting offline_mode
	  $this->db->where('key', 'offline_mode')->update('setting_aplikasi', array('jenis' => 'option-kode'));
	  $setting_id = $this->db->select('id')->where('key', 'offline_mode')->get('setting_aplikasi')->row()->id;
	  $this->db->where('id_setting', $setting_id)->delete('setting_aplikasi_options');
		$this->db->insert_batch(
			'setting_aplikasi_options',
			array(
				array('id_setting'=>$setting_id, 'kode'=>'0', 'value'=>'Web bisa diakses publik'),
				array('id_setting'=>$setting_id, 'kode'=>'1', 'value'=>'Web hanya bisa diakses petugas web'),
				array('id_setting'=>$setting_id, 'kode'=>'2', 'value'=>'Web non-aktif sama sekali'),
			)
		);
  }
}
