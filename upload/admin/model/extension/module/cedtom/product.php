<?php

/**
 * 
 */
class ModelExtensionModuleCedtomProduct extends Model
{
	public function getProducts($data = array())
	{
		$sql = "SELECT p.*,pd.*,fp.status as tom_status FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "cedtom_product fp ON(p.product_id=fp.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_tom_status']) && !is_null($data['filter_tom_status'])) {
			$sql .= " AND fp.status = '" . $data['filter_tom_status'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'fp.status',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductSpecials($product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getTotalProducts($data = array())
	{
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) JOIN " . DB_PREFIX . "cedtom_product fp ON(p.product_id=fp.product_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_tom_status']) && !is_null($data['filter_tom_status'])) {
			$sql .= " AND fp.status = '" . $data['filter_tom_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function setUploadedproduct($products)
	{
		foreach ($products['entries'] as $product) {

			$id = $product['product']['id'];
			$offer_id = $product['product']['offerId'];

			$this->db->query("INSERT INTO `" . DB_PREFIX . "cedtom_uploaded_product` SET tom_item_id = '" . $response['itemId'] . "', product_id = '" . $product_id . "', status = 'Uploaded' ON DUPLICATE KEY UPDATE tom_item_id = '" . $id . "', product_id = '" . $product_id . "' ");

			$this->db->query("UPDATE " . DB_PREFIX . "cedtom_profile_product SET status = 'Uploaded' WHERE product_id = '" . $offer_id . "' ");
		}
	}

	public function isImported($tom_product_id = '', $product_data = array())
	{
		$true_false = false;

		if ($tom_product_id) {
			$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cedtom_product` WHERE tom_product_id = '" . $tom_product_id . "' AND ean = '".$product_data['ean']."'");
			if ($sql->num_rows) {
				$true_false = true;
			} else {
				$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE model = '" . $tom_product_id . "' AND ean = '".$product_data['ean']."'");
				if ($sql->num_rows) {

					$true_false = true;
				} else {
					$this->db->query("INSERT INTO `".DB_PREFIX."cedtom_product` SET tom_product_id = '".$tom_product_id."', ean = '".$product_data['ean']."', product_data = '".$this->db->escape(json_encode($product_data))."', status = 'Imported' ");
				}
			}
		}
		return $true_false;
	}

	public function addTomProducts($data)
	{

		foreach ($data as $key => $value) {
			$model = isset($value['model']) ? $value['model'] : $value['sku'];
			$sku = isset($value['sku']) ? $value['sku'] : '';
			$upc = isset($value['upc']) ? $value['upc'] : '';
			$ean = isset($value['ean']) ? $value['ean'] : '';
			$jan = isset($value['jan']) ? $value['jan'] : '';
			$isbn = isset($value['isbn']) ? $value['isbn'] : '';
			$mpn = isset($value['mpn']) ? $value['mpn'] : '';
			$location = '';
			$subtract = isset($value['subtract']) ? $value['subtract'] : 0;
			$stock_status_id = isset($value['stock_status_id']) ? $value['stock_status_id'] : 7;
			$date_available = isset($value['date_available']) ? $value['date_available'] : '';
			$quantity = isset($value['quantity']) ? $value['quantity'] : 0;

			$price = isset($value['price']) ? $value['price'] : '';
			$weight = isset($value['weight']) ? $value['weight'] : '';
			$weight_class_id = isset($value['weight_class_id']) ? $value['weight_class_id'] : '';
			$length = isset($value['length']) ? $value['length'] : '';
			$width = isset($value['width']) ? $value['width'] : '';
			$height = isset($value['height']) ? $value['height'] : '';
			$length_class_id = isset($value['length_class_id']) ? $value['length_class_id'] : '';
			$minimum = isset($value['minimum']) ? $value['minimum'] : '';
			$status = isset($value['status']) ? $value['status'] : 0;

			$sql = "INSERT INTO `" . DB_PREFIX . "product` SET model = '" . $this->db->escape($model) . "', sku = '" . $this->db->escape($sku) . "', upc = '" . $this->db->escape($upc) . "', ean = '" . $this->db->escape($ean) . "', jan = '" . $this->db->escape($jan) . "', isbn = '" . $this->db->escape($isbn) . "', mpn = '" . $this->db->escape($mpn) . "', location = '" . $this->db->escape($location) . "', quantity = '" . (int)$quantity . "', minimum = '" . (int)$minimum . "', subtract = '" . (int)$subtract . "', stock_status_id = '" . (int)$stock_status_id . "', date_available = '" . $this->db->escape($date_available) . "', shipping = '', price = '" . (float)$price . "', points = '', weight = '" . (float)$weight . "', weight_class_id = '" . (int)$weight_class_id . "', length = '" . (float)$length . "', width = '" . (float)$width . "', height = '" . (float)$height . "', length_class_id = '" . (int)$length_class_id . "', status = '" . (int)$status . "', tax_class_id = '', sort_order = '', date_added = NOW(), date_modified = NOW()";
			
			$this->db->query($sql);

			$product_id = $this->db->getLastId();
		
			if ($product_id) {
				$this->db->query("UPDATE `" . DB_PREFIX . "cedtom_product` SET product_id = '" . (int)$product_id . "' WHERE tom_product_id = '" . $sku . "' ");

				if (isset($value['manufacturer_id'])) {
					
					$this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id = '" . (int)$value['manufacturer_id'] . "' WHERE product_id = '" . (int)$product_id . "' ");

				} elseif (isset($value['manufacturer']) && $value['manufacturer']) {
					$stores['manufacturer_store'] = array();

					if (isset($value['product_store'])) {
						$stores['manufacturer_store'] = $value['product_store'];
					}

					$manufacturer_id = $this->createManufacturer($value['manufacturer'], $stores);

					$this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id = '" . (int)$manufacturer_id . "' WHERE product_id = '" . (int)$product_id . "' ");
				}

				if (isset($value['product_store'])) {
					foreach ($value['product_store'] as $store_id) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = 0 ");
				}

				if (isset($value['product_category'])) {
					foreach ($value['product_category'] as $category_id) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
					}
				} else
				if (isset($value['categories'])) {
					$stores['category_store'] = array();

					if (isset($value['product_store'])) {
						$stores['category_store'] = $value['product_store'];
					}

					$category_id = $this->isCategoryExist($value['categories']);

					if (!$category_id) {
						$category_id = $this->createCategory($value['categories'], $stores);
					}

					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET category_id = '" . (int)$category_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->addImages($value['images'], $product_id, $sku);
				$this->addDescription($value['description'], $product_id);
				$this->createAttributes($value['attributes'], $product_id);
			}
		}
	}

	public function createManufacturer($manufacturer, $data)
	{
		$sql = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $this->db->escape($manufacturer) . "'");
		if ($sql->num_rows) {
			$manufacturer_id = $sql->row['manufacturer_id'];
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($manufacturer) . "', sort_order = '" . (int)0 . "'");

			$manufacturer_id = $this->db->getLastId();

			if (isset($data['manufacturer_store'])) {
				foreach ($data['manufacturer_store'] as $store_id) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
				}
			}
		}
		return $manufacturer_id;
	}

	public function isCategoryExist($categories)
	{
		$category_id = 0;
		foreach ($categories as $key => $value) {
			$sql = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE `name` = '" . $this->db->escape($value['name']) . "'");
			if ($sql->num_rows) {
				$category_id = $sql->row['category_id'];
			}
		}
		return $category_id;
	}

	public function createCategory($categories, $stores)
	{

		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', status = 1, date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "category_path SET `category_id` = '" . $category_id. "', `path_id` = '" . $category_id. "', `level`= 0 "); 

		foreach ($categories as $key => $value) {

			$language_id = $this->getLanguageByName($key);

			if ($language_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['name']) . "', meta_title = '" . $this->db->escape($value['name']) . "', meta_description = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['name']) . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		return $category_id;
	}

	public function addImages($images, $product_id, $tom_product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cedtom_product_image WHERE product_id = '" . (int)$product_id . "'");
		if (!$query->num_rows) {
			foreach ($images as $key => $image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "cedtom_product_image SET product_id = '" . (int)$product_id . "', tom_product_id = '" . $tom_product_id . "', image = '" . $this->db->escape($image) . "', sort_order = '" . $key . "'");
			}
		}
	}

	public function addDescription($descriptions, $product_id)
	{
		foreach ($descriptions as $key => $value) {
			if (isset($value['name']) && $value['name']) {
				$title = $value['name'];
				$description = $value['description'];
				$tag = isset($value['tag']) ? $value['tag'] : '';
				$meta_title = isset($value['meta_title']) ? $value['meta_title'] : $value['name'];
				$meta_description = isset($value['meta_description']) ? $value['meta_description'] : $value['description'];
				$meta_keyword = isset($value['meta_keyword']) ? $value['meta_keyword'] : $value['name'];
			}
		}

		foreach ($descriptions as $key => $value) {

			$language_id = $this->getLanguageByName($key);

			if ($language_id) {
				$title = $value['name'] ? $value['name'] : $title;
				$description = $value['description'] ? $value['description'] : $description;
				$tag = isset($value['tag']) ? $value['tag'] : $tag;
				$meta_title = isset($value['meta_title']) ? $value['meta_title'] : ($value['name'] ? $value['name'] : $meta_title);
				$meta_description = isset($value['meta_description']) ? $value['meta_description'] : ($value['description'] ? $value['description'] : $meta_description);
				$meta_keyword = isset($value['meta_keyword']) ? $value['meta_keyword'] : ($value['name'] ? $value['name'] : $meta_keyword);

				$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($title) . "', description = '" . $this->db->escape($description) . "', tag = '" . $this->db->escape($tag) . "', meta_title = '" . $this->db->escape($meta_title) . "', meta_description = '" . $this->db->escape($meta_description) . "', meta_keyword = '" . $this->db->escape($meta_keyword) . "'");
			}
		}
	}

	public function createAttributes($attributes, $product_id)
	{
		$data = array();
		foreach ($attributes as $key => $values) {
			foreach ($values as $sort_order => $value) {
				if ($value['text']) {
					$data[$value['name']] = $value['text'];
				}
			}
		}

		foreach ($attributes as $key => $values) {

			$language_id = $this->getLanguageByName($key);

			if ($language_id) {
				foreach ($values as $sort_order => $value) {
					$attribute_group_id = $this->addAttributeGroup($value['type'], $language_id);

					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute` a JOIN `" . DB_PREFIX . "attribute_description` ad ON(a.attribute_id=ad.attribute_id) WHERE ad.name = '" . $this->db->escape($value['name']) . "' AND a.attribute_group_id = '" . (int)$attribute_group_id . "' AND ad.language_id = '" . (int)$language_id . "'");

					if ($query->num_rows) {

						$attribute_id = $query->row['attribute_id'];
					} else {
						$sort_order = $sort_order + 1;
						$this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$attribute_group_id . "', sort_order = '" . (int)$sort_order . "'");

						$attribute_id = $this->db->getLastId();

						$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
					}

					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$attribute_id . "' AND language_id = '" . (int)$language_id . "'");

					if (!$query->num_rows) {
						if (!$value['text'] && isset($data[$value['name']])) {
							$value['text'] = $data[$value['name']];
						}
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($value['text']) . "'");
					}
				}
			}
		}
	}

	public function addAttributeGroup($name, $language_id)
	{
		$sql = $this->db->query("SELECT attribute_group_id FROM `" . DB_PREFIX . "attribute_group_description` WHERE name = '" . $this->db->escape($name) . "' AND language_id = '" . (int)$language_id . "'");

		if ($sql->num_rows) {
			$attribute_group_id = $sql->row['attribute_group_id'];
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = '" . (int)1 . "'");

			$attribute_group_id = $this->db->getLastId();

			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($name) . "'");
		}

		return $attribute_group_id;
	}

	public function getLanguageByName($name)
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($name) . "' OR name = '" . $this->db->escape(ucwords($name)) . "'");

		$language_id = 0;

		if ($query->num_rows) {
			$language_id = $query->row['language_id'];
		}
		return $language_id;
	}

	public function removeTomItems($ids)
	{
		foreach ($ids as $product_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "cedtom_product WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "cedtom_product_image WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
			$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");

			$this->cache->delete('product');
		}
	}
}
