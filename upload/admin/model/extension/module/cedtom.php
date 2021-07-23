<?php 
/**
 * 
 */
class ModelExtensionModuleCedtom extends Model
{
	public function saveSettings($code, $data, $store_id = 0){

		foreach ($data as $key => $value) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' ");

			if (!is_array($value)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
			}
		}
	}

	public function add($key){
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->db->escape($key) . "' ");
		if(!$result->num_rows){
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = 'cedcommerce_lic', `key` = '" . $this->db->escape($key) . "', `value` = '1', serialized = '1'");
		}
	}

	public function remove($key){
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = '" . $this->db->escape($key) . "' ");
	}
}